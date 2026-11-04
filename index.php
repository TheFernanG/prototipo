<?php
require __DIR__.'/app.php';
    
$error='';
    $fatal=false;
    
$p=(string)($_GET['p']??'inicio');
    
$pages=['inicio'=>'Mi panel','mensajes'=>'Mensajes','avisos'=>'Avisos escolares','academico'=>'Seguimiento académico','reportes'=>'Comportamiento','calendario'=>'Calendario','usuarios'=>'Usuarios','grupos'=>'Grupos y familias','perfil'=>'Mi cuenta','login'=>'Ingresar'];
    
if(!isset($pages[$p])) $p='inicio';
    
try {
    if(isset($_SESSION['uid']) && time()-($_SESSION['last']??0)>1800) { destroySession();
    redirectTo('login');
    }
    if (($_GET['live']??'')==='messages' && $_SERVER['REQUEST_METHOD']==='GET') {
        header('Content-Type: application/json; charset=utf-8');
    
        $u=user();
    
        if (!$u || (string)$u['id']!==($_GET['account']??'')) { http_response_code(401);
    exit;
    }
        $unread=unreadMessages((int)$u['id']);
    
        $html=null;
    
        if ($p==='mensajes') {
            ob_start();
    
            require __DIR__.'/bandeja.php';
    
            $html=ob_get_clean();
    
        }
        echo json_encode(['unread'=>$unread,'html'=>$html], JSON_INVALID_UTF8_SUBSTITUTE);
    
        exit;
    
    }
    db();
    
    if(user()) $_SESSION['last']=time();
    
    if($_SERVER['REQUEST_METHOD']==='POST') handlePost();
    
    if(!user()) $p='login';
    
    elseif($p==='login') redirectTo();
    
    if(in_array($p,['usuarios','grupos'],true) && !role('administrador')) { http_response_code(403);
    $p='inicio';
    $error='Esta sección es exclusiva de administración.';
    }
} catch(PDOException $e) {
    error_log($e->getMessage());
    
    if(in_array((string)$e->getCode(),['23000'],true)) $error='El registro ya existe o está relacionado con otros datos. Revisa tu selección.';
    
    else { $error='No se pudo completar la operación. Revisa la conexión en config.php y que hayas importado base_de_datos.sql.';
    $fatal=true;
    http_response_code(503);
    }
} catch(RuntimeException $e) { $error=$e->getMessage();
    if(!user()) $p='login';
    }
$u=$fatal?null:user(); ?><!doctype html>

<html lang="es">
    <head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title><?=h($pages[$p])?> · Conecta Escuela</title><link rel="stylesheet" href="assets/style.css">
    </head><body<?php if($u): ?> data-account="<?=h($u['id'])?>" data-page="<?=h($p)?>"<?php endif; ?>>
<?php if(!$u): ?>

    <main class="login-layout">
        <section class="login-story"><a class="brand" href="index.php"><img class="brand-symbol" src="assets/edu.png" width="220" height="220" alt=""> Conecta Escuela</a>
            <div><span class="eyebrow">COMUNIDAD ESCOLAR</span>
                <h1>Más cerca.<br>Mejor conectados.
                </h1>
                <p>Un espacio para acompañar el aprendizaje, compartir avances y fortalecer la comunicación entre la escuela y las familias.
                </p>
                <div class="story-tags"><span>Comunicación</span><span>Aprendizaje</span><span>Comunidad</span>
                </div>
            </div><small>Docentes · Alumnos · Familias</small>
        </section>
        <section class="login-card"><span class="eyebrow">BIENVENIDO A TU ESCUELA</span>
            <h2>Inicia sesión
            </h2>
            <p class="muted">Ingresa con la cuenta proporcionada por administración.
            </p>
<?php if($error): ?>
            <div class="alert error"><?=h($error)?>
            </div><?php endif; ?>
<?php if(isset($_SESSION['flash'])): ?>
            <div class="alert"><?=h($_SESSION['flash'][1])?>
            </div><?php unset($_SESSION['flash']);
    endif; ?>

            <form method="post"><?=csrf()?><input type="hidden" name="action" value="login"><label>Correo electrónico<input type="email" name="email" required maxlength="190" autocomplete="username" placeholder="tu.correo@escuela.mx"></label><label>Contraseña<input type="password" name="password" required maxlength="200" autocomplete="current-password"></label><button>Entrar a mi espacio →</button>
            </form>
            <p class="help"><a href="recuperar.php">¿Olvidaste tu contraseña?</a></p>
            <p class="help">Solicita una cuenta al administrador de tu escuela.</p>
        </section>
    </main>
<?php else: ?>

    <aside class="sidebar"><a href="index.php" class="brand"><img class="brand-symbol" src="assets/edu.png" width="112" height="112" alt=""> Conecta<span class="brand-sub">ESCUELA</span></a>
        <div class="workspace-label">MI ESPACIO ESCOLAR
        </div>
        <nav>
<?php $unread=unreadMessages((int)$u['id']);
    $icons=['inicio'=>'◈','mensajes'=>'✉','avisos'=>'◉','academico'=>'▤','reportes'=>'◇','calendario'=>'▦','usuarios'=>'◎','grupos'=>'▥','perfil'=>'⚙'];
    foreach($icons as $key=>$icon): if(in_array($key,['usuarios','grupos'],true) && !role('administrador')) continue; ?><a class="<?=$p===$key?'active':''?>" href="index.php?p=<?=$key?>"><span><?=h($icon)?></span><?=h($pages[$key])?><?php if($key==='mensajes'): ?><span id="message-dot" class="message-dot" role="img" aria-label="Mensajes sin leer" <?=$unread?'':'hidden'?>>●</span><?php endif; ?></a><?php endforeach; ?>
        </nav>
        <div class="sidebar-bottom">
            <div class="avatar"><?=h(strtoupper(substr($u['nombre'],0,1)))?>
            </div>
            <div><strong><?=h($u['nombre'])?></strong><small><?=h(ucfirst($u['rol']))?></small>
            </div>
        </div>
        <form method="post" class="logout"><?=csrf()?><input type="hidden" name="action" value="logout"><button class="ghost">Cerrar sesión</button>
        </form>
    </aside>

    <main class="main">
        <header class="topbar"><span>Tu comunidad, conectada.</span><span><?=h(date('d/m/Y'))?> <span class="pill"><?=h(ucfirst($u['rol']))?></span></span>
        </header>
        <div class="content">
            <div class="page-heading">
                <div><span class="eyebrow">CONECTA ESCUELA</span>
                    <h1><?=h($pages[$p])?>
                    </h1>
                </div><a class="button secondary" href="index.php?p=perfil">Mi cuenta</a>
            </div>
<?php if($error): ?>
            <div class="alert error" role="alert"><?=h($error)?>
            </div><?php endif; ?>
<?php if(isset($_SESSION['flash'])): ?>
            <div class="alert <?=h($_SESSION['flash'][0])?>" role="status"><?php if($_SESSION['flash'][0]==='sent'): ?><span class="message-dot" aria-hidden="true">●</span> <?php endif; ?><?=h($_SESSION['flash'][1])?>
            </div><?php unset($_SESSION['flash']);
    endif; ?>
<?php if($p==='inicio'):
[$aud,$args]=audienceSql();
    $news=rows("SELECT r.*,u.nombre autor,g.nombre grupo FROM avisos r JOIN usuarios u ON u.id=r.autor_id LEFT JOIN grupos g ON g.id=r.grupo_id WHERE $aud ORDER BY r.creado DESC LIMIT 4",$args);
    
$events=rows("SELECT r.*,g.nombre grupo FROM eventos r LEFT JOIN grupos g ON g.id=r.grupo_id WHERE $aud AND r.fecha>=CURDATE() ORDER BY r.fecha LIMIT 4",$args); ?>
            <section class="welcome">
                <div><span class="eyebrow">CADA DÍA ES UNA OPORTUNIDAD</span>
                    <h2>Hola, <?=h(explode(' ',$u['nombre'])[0])?>.
                    </h2>
                    <p>Acompaña los avances y mantente al día con tu comunidad escolar.
                    </p><a class="button" href="index.php?p=mensajes">Ir a mensajes →</a>
                </div>
                <div class="welcome-art" aria-hidden="true"><span>✦</span><strong>C</strong><small>Aprendemos juntos</small>
                </div>
            </section>

            <div class="stats"><a href="index.php?p=mensajes"><span>Mensajes sin leer</span><strong id="unread-count"><?=h($unread)?></strong><small>Tu bandeja de entrada</small></a><a href="index.php?p=<?=role('administrador')?'grupos':'academico'?>"><span>Mis grupos</span><strong><?=count(groups())?></strong><small>Espacios de aprendizaje</small></a><a href="index.php?p=academico"><span><?=role('padre')?'Mis hijos':'Alumnos vinculados'?></span><strong><?=count(students())?></strong><small>Seguimiento académico</small></a>
            </div>

            <div class="columns">
                <section class="card">
                    <div class="section-title">
                        <h2>Últimos avisos
                        </h2><a href="index.php?p=avisos">Ver todos →</a>
                    </div><?php if(!$news): ?>
                    <p class="empty">Aún no hay avisos para mostrar.
                    </p><?php endif;
    foreach($news as $n): ?><article class="feed"><span class="pill"><?=h($n['grupo']??'Toda la escuela')?></span>
                    <h3><?=h($n['titulo'])?>
                    </h3>
                    <p class="clamp"><?=h($n['cuerpo'])?>
                    </p><small><?=h($n['autor'])?> · <?=h(date('d/m/Y',strtotime($n['creado'])))?></small></article><?php endforeach; ?>
                </section>
                <section class="card">
                    <div class="section-title">
                        <h2>Próximas actividades
                        </h2><a href="index.php?p=calendario">Ver todas →</a>
                    </div><?php if(!$events): ?>
                    <p class="empty">No hay actividades próximas.
                    </p><?php endif;
    foreach($events as $e): ?><article class="event-mini">
                    <div class="date-tile"><?=h(date('d',strtotime($e['fecha'])))?><small><?=h(date('m/Y',strtotime($e['fecha'])))?></small>
                    </div>
                    <div>
                        <h3><?=h($e['titulo'])?>
                        </h3><small><?=h($e['grupo']??'Toda la escuela')?> · <?=h($e['lugar'])?></small>
                    </div></article><?php endforeach; ?>
                    <div class="note"><strong>Una comunidad que acompaña</strong>
                        <p>Consulta los avances y conversa con tus docentes desde un mismo lugar.
                        </p>
                    </div>
                </section>
            </div>
<?php elseif($p==='mensajes'):
$rec=recipients(); ?>
            <div class="columns">
                <section class="card">
                    <h2>Escribir mensaje
                    </h2>
                    <p class="muted">Los destinatarios corresponden a tus grupos y a administración.
                    </p>
                    <form method="post"><?=csrf()?><input type="hidden" name="action" value="message"><label>Para<select name="destinatario_id" required><?=options(array_map(function($r){$r['nombre'].=' · '.ucfirst($r['rol']); return $r;},$rec))?></select></label><label>Asunto<input name="asunto" maxlength="150" required></label><label>Mensaje<textarea name="cuerpo" maxlength="5000" rows="6" required></textarea></label><button>Enviar mensaje</button>
                    </form><?php if(!$rec): ?>
                    <p class="help">No hay destinatarios disponibles. Solicita la asignación de tu grupo.
                    </p><?php endif; ?>
                </section>
                <section class="card">
                    <h2>Bandeja de mensajes
                    </h2>
                    <p class="muted">Últimos 100 mensajes recibidos y enviados.
                    </p>
                    <div id="message-list"><?php require __DIR__.'/bandeja.php'; ?>
                    </div>
                </section>
            </div>
<?php elseif($p==='avisos' || $p==='calendario'): $isEvent=$p==='calendario';
    [$aud,$args]=audienceSql();
    $table=$isEvent?'eventos':'avisos';
    $records=rows("SELECT r.*,u.nombre autor,g.nombre grupo FROM $table r JOIN usuarios u ON u.id=r.autor_id LEFT JOIN grupos g ON g.id=r.grupo_id WHERE $aud ORDER BY ".($isEvent?'r.fecha DESC':'r.creado DESC').',r.id DESC LIMIT 100',$args); ?>
<?php if(role('administrador','docente')): ?>
            <details class="card composer"><summary>＋ <?=$isEvent?'Agregar actividad':'Publicar aviso'?></summary>
                <form method="post" class="form-grid"><?=csrf()?><input type="hidden" name="action" value="<?=$isEvent?'event':'announcement'?>"><label>Título<input name="titulo" required maxlength="150"></label><label>Dirigido a<select name="grupo_id" required><?=groupOptions(role('administrador'))?></select></label><?php if($isEvent): ?><label>Fecha<input type="date" name="fecha" required></label><label>Lugar<input name="lugar" maxlength="150"></label><?php endif; ?><label class="full"><?=$isEvent?'Descripción':'Contenido'?><textarea name="<?=$isEvent?'descripcion':'cuerpo'?>" required maxlength="<?=$isEvent?'3000':'5000'?>" rows="4"></textarea></label>
                    <div><button><?=$isEvent?'Guardar actividad':'Publicar aviso'?></button>
                    </div>
                </form>
            </details><?php endif; ?>
            <p class="muted">Se muestran los últimos 100 registros. Las actividades están ordenadas de la fecha más reciente a la más antigua.
            </p>
            <div class="record-grid"><?php if(!$records): ?>
                <section class="card empty">Aún no hay <?=$isEvent?'actividades':'avisos'?>.
                </section><?php endif;
    foreach($records as $r): ?><article class="card">
                <div class="section-title"><span class="pill"><?=h($r['grupo']??'Toda la escuela')?></span><?php if(role('administrador') || (role('docente') && $r['autor_id']==$u['id'])) echo deleteForm($table,(int)$r['id']); ?>
                </div>
                <h2><?=h($r['titulo'])?>
                </h2><?php if($isEvent): ?>
                <p class="event-date"><?=h(date('d/m/Y',strtotime($r['fecha'])))?> · <?=h($r['lugar'])?>
                </p><?php endif; ?>
                <p class="pre"><?=h($r[$isEvent?'descripcion':'cuerpo'])?>
                </p><small><?=h($r['autor'])?> · <?=h(date('d/m/Y',strtotime($r['creado'])))?></small></article><?php endforeach; ?>
            </div>
<?php elseif($p==='academico'): [$where,$args]=studentSql('c.alumno_id');
    $grades=rows("SELECT c.*,u.nombre alumno,m.nombre materia,g.nombre grupo,t.nombre docente FROM calificaciones c JOIN usuarios u ON u.id=c.alumno_id JOIN asignaciones a ON a.id=c.asignacion_id JOIN materias m ON m.id=a.materia_id JOIN grupos g ON g.id=a.grupo_id JOIN usuarios t ON t.id=c.autor_id WHERE $where".(role('docente')?' AND a.docente_id='.(int)$u['id']:'').' ORDER BY c.creado DESC,c.id DESC LIMIT 200',$args);
    
$assign=role('administrador')?rows("SELECT a.id,CONCAT(g.nombre,' · ',m.nombre,' · ',u.nombre) nombre FROM asignaciones a JOIN grupos g ON g.id=a.grupo_id JOIN materias m ON m.id=a.materia_id JOIN usuarios u ON u.id=a.docente_id"):rows("SELECT a.id,CONCAT(g.nombre,' · ',m.nombre) nombre FROM asignaciones a JOIN grupos g ON g.id=a.grupo_id JOIN materias m ON m.id=a.materia_id WHERE a.docente_id=?",[$u['id']]); ?>
<?php if(role('administrador','docente')): ?>
            <details class="card composer"><summary>＋ Registrar calificación</summary>
                <p class="muted">Selecciona un alumno inscrito en el grupo de la materia. Escala de 0 a 10.
                </p>
                <form method="post" class="form-grid"><?=csrf()?><input type="hidden" name="action" value="grade"><label>Alumno<select name="alumno_id" required><?=options(students())?></select></label><label>Grupo y materia<select name="asignacion_id" required><?=options($assign)?></select></label><label>Periodo<input name="periodo" placeholder="Primer trimestre" maxlength="60" required></label><label>Calificación<input type="number" name="calificacion" min="0" max="10" step="0.01" required></label><label class="full">Observaciones<textarea name="observaciones" maxlength="2000"></textarea></label>
                    <div><button>Guardar calificación</button>
                    </div>
                </form>
            </details><?php endif; ?>

            <section class="card">
                <h2>Historial académico
                </h2>
                <p class="muted">Últimas 200 calificaciones. Para corregir un registro, su autor o administración puede eliminarlo y capturarlo nuevamente.
                </p>
                <div class="table-wrap">
                    <table>
                        <thead>
                            <tr><th>Alumno / grupo</th><th>Materia / periodo</th><th>Calificación</th><th>Observaciones</th><th>Registró</th><th></th>
                            </tr>
                        </thead>
                        <tbody><?php if(!$grades): ?>
                            <tr><td colspan="6" class="empty">Sin calificaciones registradas.</td>
                            </tr><?php endif;
    foreach($grades as $r): ?>
                            <tr><td><strong><?=h($r['alumno'])?></strong><small><?=h($r['grupo'])?></small></td><td><?=h($r['materia'])?><small><?=h($r['periodo'])?></small></td><td><span class="score"><?=h($r['calificacion'])?></span></td><td class="pre"><?=h($r['observaciones'])?></td><td><?=h($r['docente'])?><small><?=h(date('d/m/Y',strtotime($r['creado'])))?></small></td><td><?php if(role('administrador') || (role('docente') && $r['autor_id']==$u['id'])) echo deleteForm('calificaciones',(int)$r['id']); ?></td>
                            </tr><?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </section>
<?php elseif($p==='reportes'): [$where,$args]=studentSql('r.alumno_id');
    $reports=rows("SELECT r.*,u.nombre alumno,a.nombre autor FROM reportes r JOIN usuarios u ON u.id=r.alumno_id JOIN usuarios a ON a.id=r.autor_id WHERE $where ORDER BY r.fecha DESC,r.id DESC LIMIT 200",$args); ?>
<?php if(role('administrador','docente')): ?>
            <details class="card composer"><summary>＋ Registrar comportamiento</summary>
                <form method="post" class="form-grid"><?=csrf()?><input type="hidden" name="action" value="behavior"><label>Alumno<select name="alumno_id" required><?=options(students())?></select></label><label>Tipo<select name="tipo"><option>Positivo</option><option>Observación</option><option>Incidencia</option></select></label><label>Fecha<input type="date" name="fecha" required value="<?=date('Y-m-d')?>"></label><label class="full">Descripción<textarea name="detalle" required maxlength="3000" rows="4"></textarea></label>
                    <div><button>Guardar reporte</button>
                    </div>
                </form>
            </details><?php endif; ?>
            <div class="record-grid"><?php if(!$reports): ?>
                <section class="card empty">Sin reportes registrados.
                </section><?php endif;
    foreach($reports as $r): ?><article class="card">
                <div class="section-title"><span class="pill"><?=h($r['tipo'])?></span><small><?=h(date('d/m/Y',strtotime($r['fecha'])))?></small>
                </div>
                <h2><?=h($r['alumno'])?>
                </h2>
                <p class="pre"><?=h($r['detalle'])?>
                </p>
                <div class="section-title"><small>Registró: <?=h($r['autor'])?></small><?php if(role('administrador') || (role('docente') && $r['autor_id']==$u['id'])) echo deleteForm('reportes',(int)$r['id']); ?>
                </div></article><?php endforeach; ?>
            </div>
<?php elseif($p==='usuarios'): $users=rows('SELECT id,nombre,email,rol,activo FROM usuarios ORDER BY rol,nombre'); ?>

            <details class="card composer"><summary>＋ Crear usuario</summary>
                <form method="post" class="form-grid"><?=csrf()?><input type="hidden" name="action" value="user_create"><label>Nombre completo<input name="nombre" maxlength="120" required></label><label>Correo<input name="email" type="email" maxlength="190" required></label><label>Rol<select name="rol"><option value="docente">Docente</option><option value="alumno">Alumno</option><option value="padre">Padre o tutor</option><option value="administrador">Administrador</option></select></label><label>Contraseña inicial<input name="password" type="password" minlength="10" maxlength="200" required autocomplete="new-password"></label>
                    <div><button>Crear usuario</button>
                    </div>
                </form>
            </details>
            <section class="card">
                <h2>Directorio escolar
                </h2>
                <div class="table-wrap">
                    <table>
                        <thead>
                            <tr><th>Nombre</th><th>Correo</th><th>Rol</th><th>Estado</th><th>Acción</th>
                            </tr>
                        </thead>
                        <tbody><?php foreach($users as $r): ?>
                            <tr><td><?=h($r['nombre'])?></td><td><?=h($r['email'])?></td><td><?=h(ucfirst($r['rol']))?></td><td><span class="pill"><?=$r['activo']?'Activo':'Inactivo'?></span></td><td><?php if($r['id']!=$u['id']): ?>
                                <form method="post"><?=csrf()?><input type="hidden" name="action" value="toggle_user"><input type="hidden" name="id" value="<?=h($r['id'])?>"><button class="secondary small"><?=$r['activo']?'Desactivar':'Activar'?></button>
                                </form><?php endif; ?></td>
                            </tr><?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </section>
<?php elseif($p==='grupos'): $teachers=rows("SELECT id,nombre FROM usuarios WHERE rol='docente' AND activo=1 ORDER BY nombre");
    $parents=rows("SELECT id,nombre FROM usuarios WHERE rol='padre' AND activo=1 ORDER BY nombre");
    $subjects=rows('SELECT * FROM materias ORDER BY nombre'); ?>

            <div class="record-grid">
                <section class="card">
                    <h2>Crear grupo
                    </h2>
                    <form method="post"><?=csrf()?><input type="hidden" name="action" value="group_create"><label>Nombre<input name="nombre" maxlength="80" required placeholder="1° A"></label><label>Ciclo escolar<input name="ciclo" maxlength="30" required placeholder="2026–2027"></label><button>Crear grupo</button>
                    </form>
                </section>
                <section class="card">
                    <h2>Crear materia
                    </h2>
                    <form method="post"><?=csrf()?><input type="hidden" name="action" value="subject_create"><label>Nombre<input name="nombre" required maxlength="100"></label><button>Crear materia</button>
                    </form>
                    <p class="help"><?=h(implode(' · ',array_column($subjects,'nombre')))?>
                    </p>
                </section>
                <section class="card">
                    <h2>Asignar docente
                    </h2>
                    <form method="post"><?=csrf()?><input type="hidden" name="action" value="assignment"><label>Grupo<select name="grupo_id" required><?=options(groups())?></select></label><label>Materia<select name="materia_id" required><?=options($subjects)?></select></label><label>Docente<select name="docente_id" required><?=options($teachers)?></select></label><button>Asignar docente</button>
                    </form>
                </section>
                <section class="card">
                    <h2>Inscribir alumno
                    </h2>
                    <form method="post"><?=csrf()?><input type="hidden" name="action" value="enroll"><label>Grupo<select name="grupo_id" required><?=options(groups())?></select></label><label>Alumno<select name="alumno_id" required><?=options(students())?></select></label><button>Inscribir alumno</button>
                    </form>
                </section>
                <section class="card">
                    <h2>Vincular familia
                    </h2>
                    <form method="post"><?=csrf()?><input type="hidden" name="action" value="link"><label>Padre o tutor<select name="padre_id" required><?=options($parents)?></select></label><label>Hijo / alumno<select name="alumno_id" required><?=options(students())?></select></label><button>Vincular familia</button>
                    </form>
                </section>
                <section class="card">
                    <h2>Grupos disponibles
                    </h2><?php foreach(groups() as $g): ?>
                    <div class="feed"><strong><?=h($g['nombre'])?></strong><small>Ciclo <?=h($g['ciclo'])?></small>
                    </div><?php endforeach; ?>
                </section>
            </div>
<?php $tables=[['asignaciones','Clases asignadas','SELECT a.id,CONCAT(g.nombre," · ",m.nombre," · ",u.nombre) nombre FROM asignaciones a JOIN grupos g ON g.id=a.grupo_id JOIN materias m ON m.id=a.materia_id JOIN usuarios u ON u.id=a.docente_id'],['inscripciones','Inscripciones','SELECT i.id,CONCAT(g.nombre," · ",u.nombre) nombre FROM inscripciones i JOIN grupos g ON g.id=i.grupo_id JOIN usuarios u ON u.id=i.alumno_id'],['vinculos','Familias vinculadas','SELECT v.id,CONCAT(p.nombre," → ",a.nombre) nombre FROM vinculos v JOIN usuarios p ON p.id=v.padre_id JOIN usuarios a ON a.id=v.alumno_id']];
    foreach($tables as [$table,$title,$sql]): ?>
            <section class="card spaced">
                <h2><?=h($title)?>
                </h2><?php foreach(rows($sql) as $r): ?>
                <div class="section-title feed"><span><?=h($r['nombre'])?></span><?=deleteForm($table,(int)$r['id'])?>
                </div><?php endforeach; ?>
            </section><?php endforeach; ?>
<?php elseif($p==='perfil'): ?>
            <div class="columns">
                <section class="card">
                    <div class="avatar large"><?=h(strtoupper(substr($u['nombre'],0,1)))?>
                    </div>
                    <h2><?=h($u['nombre'])?>
                    </h2>
                    <p><?=h($u['email'])?>
                    </p><span class="pill"><?=h(ucfirst($u['rol']))?></span>
                    <h3>Mis grupos
                    </h3><?php foreach(groups() as $g): ?>
                    <p><?=h($g['nombre'])?> · <?=h($g['ciclo'])?>
                    </p><?php endforeach; ?>
                </section>
                <section class="card">
                    <h2>Cambiar contraseña
                    </h2>
                    <form method="post"><?=csrf()?><input type="hidden" name="action" value="password"><label>Contraseña actual<input type="password" name="actual" maxlength="200" required autocomplete="current-password"></label><label>Nueva contraseña<input type="password" name="nueva" minlength="10" maxlength="200" required autocomplete="new-password"></label>
                        <p class="help">Usa al menos 10 caracteres. Combina letras, números y símbolos.
                        </p><button>Actualizar contraseña</button>
                    </form>
                </section>
                <section class="card">
                    <h2>Pregunta de seguridad</h2>
                    <p class="help">Configúrala para recuperar tu contraseña. Elige una respuesta que otras personas desconozcan.</p>
                    <form method="post">
                        <?=csrf()?>
                        <input type="hidden" name="action" value="pregunta_recuperacion">
                        <label>Pregunta
                            <select name="pregunta" required>
                                <?php foreach (preguntasRecuperacion() as $id => $texto): ?>
                                    <option value="<?=h($id)?>"><?=h($texto)?></option>
                                <?php endforeach; ?>
                            </select>
                        </label>
                        <label>Respuesta <input type="password" name="respuesta" maxlength="72" required></label>
                        <label>Contraseña actual <input type="password" name="actual" maxlength="200" autocomplete="current-password" required></label>
                        <button>Guardar pregunta</button>
                    </form>
                </section>
            </div><?php endif; ?>

            <footer>Conecta Escuela · Comunicación que acompaña el aprendizaje.
            </footer>
        </div>
    </main><script src="assets/live.js" defer></script><?php endif; ?>
</body>
</html>
