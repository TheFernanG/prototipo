<?php
declare(strict_types=1);
$config = require __DIR__.'/config.php';
date_default_timezone_set($config['timezone']);
ini_set('session.use_strict_mode','1');
ini_set('session.use_only_cookies','1');
session_name('comunidad_session');
session_set_cookie_params(['lifetime'=>0,'path'=>'/','secure'=>!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS']!=='off','httponly'=>true,'samesite'=>'Lax']);
session_start();
header('Content-Type: text/html; charset=utf-8');
header('Cache-Control: no-store');
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header("Content-Security-Policy: default-src 'self'; style-src 'self'; img-src 'self' data:; script-src 'none'; base-uri 'self'; form-action 'self'; frame-ancestors 'none'");
function h($v): string { return htmlspecialchars((string)$v, ENT_QUOTES|ENT_SUBSTITUTE,'UTF-8'); }
function redirectTo(string $page='inicio'): void { header('Location: index.php?p='.rawurlencode($page)); exit; }
function db(): PDO {
    static $pdo;
    global $config;
    if (!$pdo) {
        $pdo = new PDO("mysql:host={$config['host']};port={$config['port']};dbname={$config['database']};charset=utf8mb4",$config['username'],$config['password'],[PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION,PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_ASSOC,PDO::ATTR_EMULATE_PREPARES=>false]);
        $pdo->exec("SET time_zone=".$pdo->quote(date('P')));
    }
    return $pdo;
}
function q(string $sql,array $args=[]): PDOStatement { $s=db()->prepare($sql); $s->execute($args); return $s; }
function rows(string $sql,array $args=[]): array { return q($sql,$args)->fetchAll(); }
function one(string $sql,array $args=[]): ?array { return q($sql,$args)->fetch() ?: null; }
function user(): ?array { static $u=false; if ($u===false) $u=isset($_SESSION['uid']) ? one('SELECT * FROM usuarios WHERE id=? AND activo=1',[$_SESSION['uid']]) : null; return $u; }
function role(string ...$roles): bool { return user() && in_array(user()['rol'],$roles,true); }
function requireRole(string ...$roles): void { if (!role(...$roles)) throw new RuntimeException('No tienes permiso para realizar esta acción.'); }
function csrf(): string { return '<input type="hidden" name="csrf" value="'.h($_SESSION['csrf'] ?? ($_SESSION['csrf']=bin2hex(random_bytes(32)))).'">'; }
function checkCsrf(): void { if (!isset($_POST['csrf'],$_SESSION['csrf']) || !is_string($_POST['csrf']) || !hash_equals($_SESSION['csrf'],$_POST['csrf'])) throw new RuntimeException('La sesión del formulario venció. Recarga la página.'); }
function val(string $key,int $max=200,bool $required=true): string { $v=trim((string)($_POST[$key]??'')); if (($required && $v==='') || strlen($v)>$max) throw new RuntimeException('Revisa el campo: '.$key.'.'); return $v; }
function num(string $key): int { $v=filter_var($_POST[$key]??null,FILTER_VALIDATE_INT); if ($v===false || $v===null || $v<1) throw new RuntimeException('Selecciona una opción válida: '.$key.'.'); return $v; }
function dateValue(string $key): string { $v=val($key,10); $d=DateTime::createFromFormat('!Y-m-d',$v); if (!$d || $d->format('Y-m-d')!==$v) throw new RuntimeException('Fecha inválida.'); return $v; }
function flash(string $msg,string $type='ok'): void { $_SESSION['flash']=[$type,$msg]; }
function groups(): array {
    $u=user();
    if (role('administrador')) return rows('SELECT * FROM grupos ORDER BY nombre');
    if (role('docente')) return rows('SELECT DISTINCT g.* FROM grupos g JOIN asignaciones a ON a.grupo_id=g.id WHERE a.docente_id=? ORDER BY g.nombre',[$u['id']]);
    if (role('alumno')) return rows('SELECT g.* FROM grupos g JOIN inscripciones i ON i.grupo_id=g.id WHERE i.alumno_id=? ORDER BY g.nombre',[$u['id']]);
    return rows('SELECT DISTINCT g.* FROM grupos g JOIN inscripciones i ON i.grupo_id=g.id JOIN vinculos v ON v.alumno_id=i.alumno_id WHERE v.padre_id=? ORDER BY g.nombre',[$u['id']]);
}
function groupAllowed(int $id): bool { return in_array($id,array_map('intval',array_column(groups(),'id')),true); }
function students(): array {
    $u=user();
    if (role('administrador')) return rows("SELECT id,nombre FROM usuarios WHERE rol='alumno' AND activo=1 ORDER BY nombre");
    if (role('docente')) return rows('SELECT DISTINCT u.id,u.nombre FROM usuarios u JOIN inscripciones i ON i.alumno_id=u.id JOIN asignaciones a ON a.grupo_id=i.grupo_id WHERE a.docente_id=? AND u.activo=1 ORDER BY u.nombre',[$u['id']]);
    if (role('alumno')) return [['id'=>$u['id'],'nombre'=>$u['nombre']]];
    return rows('SELECT u.id,u.nombre FROM usuarios u JOIN vinculos v ON v.alumno_id=u.id WHERE v.padre_id=? AND u.activo=1 ORDER BY u.nombre',[$u['id']]);
}
function studentAllowed(int $id): bool { return in_array($id,array_map('intval',array_column(students(),'id')),true); }
function assignmentAllowed(int $id,int $student): bool {
    $a=one('SELECT a.* FROM asignaciones a JOIN inscripciones i ON i.grupo_id=a.grupo_id WHERE a.id=? AND i.alumno_id=?',[$id,$student]);
    return $a && (role('administrador') || (role('docente') && (int)$a['docente_id']===(int)user()['id']));
}
function recipients(): array {
    $u=user(); $all=rows('SELECT id,nombre,rol FROM usuarios WHERE activo=1 AND id<>? ORDER BY nombre',[$u['id']]);
    if (role('administrador')) return $all;
    $out=[]; $myGroups=array_column(groups(),'id');
    foreach ($all as $r) {
        if ($r['rol']==='administrador') { $out[]=$r; continue; }
        if (role('docente') && !in_array($r['rol'],['padre','alumno'],true)) continue;
        if (!role('docente') && $r['rol']!=='docente') continue;
        if ($r['rol']==='docente') $their=rows('SELECT grupo_id FROM asignaciones WHERE docente_id=?',[$r['id']]);
        elseif ($r['rol']==='alumno') $their=rows('SELECT grupo_id FROM inscripciones WHERE alumno_id=?',[$r['id']]);
        else $their=rows('SELECT i.grupo_id FROM inscripciones i JOIN vinculos v ON v.alumno_id=i.alumno_id WHERE v.padre_id=?',[$r['id']]);
        if (array_intersect($myGroups,array_column($their,'grupo_id'))) $out[]=$r;
    }
    return $out;
}
function options(array $list,string $label='nombre'): string { $s='<option value="">Seleccionar…</option>'; foreach($list as $r) $s.='<option value="'.h($r['id']).'">'.h($r[$label]).'</option>'; return $s; }
function groupOptions(bool $global=false): string { return ($global?'<option value="0">Toda la escuela</option>':'').options(groups()); }
function studentSql(string $col): array { $ids=array_map('intval',array_column(students(),'id')); return $ids ? [$col.' IN ('.implode(',',array_fill(0,count($ids),'?')).')',$ids] : ['1=0',[]]; }
function audienceSql(string $alias='r'): array { $ids=array_map('intval',array_column(groups(),'id')); if (role('administrador')) return ['1=1',[]]; return ["($alias.grupo_id IS NULL".($ids?' OR '.$alias.'.grupo_id IN ('.implode(',',array_fill(0,count($ids),'?')).')':'').')',$ids]; }
function validateUser(int $id,string $rol): void { if (!one('SELECT id FROM usuarios WHERE id=? AND rol=? AND activo=1',[$id,$rol])) throw new RuntimeException('Usuario inválido para este rol.'); }
function deleteForm(string $table,int $id): string { return '<form method="post" class="inline">'.csrf().'<input type="hidden" name="action" value="delete"><input type="hidden" name="table" value="'.h($table).'"><input type="hidden" name="id" value="'.$id.'"><button class="danger small">Eliminar</button></form>'; }
function handlePost(): void {
    checkCsrf(); $action=val('action',40);
    if ($action==='login') {
        $email=val('email',190); $password=val('password',200);
        $key=hash('sha256',strtolower($email).'|'.($_SERVER['REMOTE_ADDR']??'local'));
        $attempt=one('SELECT * FROM intentos_login WHERE clave=?',[$key]);
        if ($attempt && strtotime($attempt['bloqueado_hasta']??'1970-01-01')>time()) throw new RuntimeException('Demasiados intentos. Espera 15 minutos.');
        $u=one('SELECT * FROM usuarios WHERE email=? AND activo=1',[$email]);
        // Hash ficticio para reducir diferencias de tiempo entre cuentas existentes e inexistentes.
        $valid=password_verify($password,$u['password']??'$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2uheWG/igi.');
        if (!$u || !$valid) {
            q('INSERT INTO intentos_login(clave,intentos,ultimo_intento) VALUES(?,1,NOW()) ON DUPLICATE KEY UPDATE intentos=IF(ultimo_intento<DATE_SUB(NOW(),INTERVAL 15 MINUTE),1,intentos+1),ultimo_intento=NOW()',[$key]);
            q('UPDATE intentos_login SET bloqueado_hasta=DATE_ADD(NOW(),INTERVAL 15 MINUTE) WHERE clave=? AND intentos>=5',[$key]);
            throw new RuntimeException('Correo o contraseña incorrectos.');
        }
        q('DELETE FROM intentos_login WHERE clave=?',[$key]);
        session_regenerate_id(true); $_SESSION=['uid'=>$u['id'],'last'=>time(),'csrf'=>bin2hex(random_bytes(32))]; redirectTo();
    }
    if (!user()) redirectTo('login');
    if ($action==='logout') { $_SESSION=[]; session_destroy(); setcookie(session_name(),'',time()-3600,'/'); redirectTo('login'); }
    if ($action==='password') {
        if (!password_verify(val('actual',200),user()['password'])) throw new RuntimeException('La contraseña actual no coincide.');
        $p=val('nueva',200); if (strlen($p)<10) throw new RuntimeException('Usa al menos 10 caracteres.');
        q('UPDATE usuarios SET password=? WHERE id=?',[password_hash($p,PASSWORD_DEFAULT),user()['id']]); session_regenerate_id(true); flash('Contraseña actualizada.'); redirectTo('perfil');
    }
    if ($action==='user_create') {
        requireRole('administrador'); $rol=val('rol',30); if (!in_array($rol,['administrador','docente','alumno','padre'],true)) throw new RuntimeException('Rol inválido.');
        $email=val('email',190); if (!filter_var($email,FILTER_VALIDATE_EMAIL)) throw new RuntimeException('Correo inválido.');
        $pass=val('password',200); if(strlen($pass)<10) throw new RuntimeException('La contraseña requiere al menos 10 caracteres.');
        q('INSERT INTO usuarios(nombre,email,password,rol) VALUES(?,?,?,?)',[val('nombre',120),$email,password_hash($pass,PASSWORD_DEFAULT),$rol]); flash('Usuario creado.'); redirectTo('usuarios');
    }
    if ($action==='toggle_user') {
        requireRole('administrador'); $id=num('id'); if($id==user()['id']) throw new RuntimeException('No puedes desactivar tu propia cuenta.');
        q('UPDATE usuarios SET activo=1-activo WHERE id=?',[$id]); flash('Estado actualizado.'); redirectTo('usuarios');
    }
    if ($action==='group_create') { requireRole('administrador'); q('INSERT INTO grupos(nombre,ciclo) VALUES(?,?)',[val('nombre',80),val('ciclo',30)]); flash('Grupo creado.'); redirectTo('grupos'); }
    if ($action==='subject_create') { requireRole('administrador'); q('INSERT INTO materias(nombre) VALUES(?)',[val('nombre',100)]); flash('Materia creada.'); redirectTo('grupos'); }
    if ($action==='assignment') {
        requireRole('administrador'); $t=num('docente_id'); validateUser($t,'docente');
        q('INSERT INTO asignaciones(grupo_id,materia_id,docente_id) VALUES(?,?,?)',[num('grupo_id'),num('materia_id'),$t]); flash('Docente asignado.'); redirectTo('grupos');
    }
    if ($action==='enroll') { requireRole('administrador'); $id=num('alumno_id'); validateUser($id,'alumno'); q('INSERT INTO inscripciones(grupo_id,alumno_id) VALUES(?,?)',[num('grupo_id'),$id]); flash('Alumno inscrito.'); redirectTo('grupos'); }
    if ($action==='link') { requireRole('administrador'); $p=num('padre_id'); $a=num('alumno_id'); validateUser($p,'padre'); validateUser($a,'alumno'); q('INSERT INTO vinculos(padre_id,alumno_id) VALUES(?,?)',[$p,$a]); flash('Familia vinculada.'); redirectTo('grupos'); }
    if ($action==='message') {
        $id=num('destinatario_id'); if(!in_array($id,array_map('intval',array_column(recipients(),'id')),true)) throw new RuntimeException('Destinatario fuera de tus grupos.');
        q('INSERT INTO mensajes(remitente_id,destinatario_id,asunto,cuerpo) VALUES(?,?,?,?)',[user()['id'],$id,val('asunto',150),val('cuerpo',5000)]); flash('Mensaje enviado.'); redirectTo('mensajes');
    }
    if ($action==='read') { q('UPDATE mensajes SET leido=1 WHERE id=? AND destinatario_id=?',[num('id'),user()['id']]); redirectTo('mensajes'); }
    if ($action==='announcement' || $action==='event') {
        requireRole('administrador','docente'); $g=filter_var($_POST['grupo_id']??null,FILTER_VALIDATE_INT); if($g===false || $g===null || $g<0 || ($g===0 && !role('administrador')) || ($g>0 && !groupAllowed($g))) throw new RuntimeException('Grupo no permitido.');
        if($action==='announcement') { q('INSERT INTO avisos(autor_id,grupo_id,titulo,cuerpo) VALUES(?,?,?,?)',[user()['id'],$g?:null,val('titulo',150),val('cuerpo',5000)]); flash('Aviso publicado.'); redirectTo('avisos'); }
        q('INSERT INTO eventos(autor_id,grupo_id,titulo,descripcion,fecha,lugar) VALUES(?,?,?,?,?,?)',[user()['id'],$g?:null,val('titulo',150),val('descripcion',3000),dateValue('fecha'),val('lugar',150,false)]); flash('Actividad agregada.'); redirectTo('calendario');
    }
    if ($action==='grade') {
        requireRole('administrador','docente'); $s=num('alumno_id'); $a=num('asignacion_id'); if(!assignmentAllowed($a,$s)) throw new RuntimeException('El alumno no pertenece a esa clase.');
        $n=filter_var($_POST['calificacion']??null,FILTER_VALIDATE_FLOAT); if($n===false || $n<0 || $n>10) throw new RuntimeException('La calificación debe estar entre 0 y 10.');
        q('INSERT INTO calificaciones(alumno_id,asignacion_id,autor_id,periodo,calificacion,observaciones) VALUES(?,?,?,?,?,?)',[$s,$a,user()['id'],val('periodo',60),$n,val('observaciones',2000,false)]); flash('Calificación guardada.'); redirectTo('academico');
    }
    if ($action==='behavior') {
        requireRole('administrador','docente'); $s=num('alumno_id'); if(!studentAllowed($s)) throw new RuntimeException('Alumno no permitido.'); $tipo=val('tipo',20); if(!in_array($tipo,['Positivo','Observación','Incidencia'],true)) throw new RuntimeException('Tipo inválido.');
        q('INSERT INTO reportes(alumno_id,autor_id,tipo,detalle,fecha) VALUES(?,?,?,?,?)',[$s,user()['id'],$tipo,val('detalle',3000),dateValue('fecha')]); flash('Reporte guardado.'); redirectTo('reportes');
    }
    if ($action==='delete') {
        $table=val('table',30); $id=num('id');
        $admin=['asignaciones'=>'grupos','inscripciones'=>'grupos','vinculos'=>'grupos']; $owned=['avisos'=>'avisos','eventos'=>'calendario','calificaciones'=>'academico','reportes'=>'reportes'];
        if(isset($admin[$table])) { requireRole('administrador'); q("DELETE FROM $table WHERE id=?",[$id]); flash('Registro eliminado.'); redirectTo($admin[$table]); }
        if(isset($owned[$table])) { requireRole('administrador','docente'); $r=one("SELECT autor_id FROM $table WHERE id=?",[$id]); if(!$r || (!role('administrador') && $r['autor_id']!=user()['id'])) throw new RuntimeException('No puedes eliminar este registro.'); q("DELETE FROM $table WHERE id=?",[$id]); flash('Registro eliminado.'); redirectTo($owned[$table]); }
        throw new RuntimeException('Operación no válida.');
    }
    throw new RuntimeException('Acción desconocida.');
}
