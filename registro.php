<?php
require_once __DIR__.'/config.php';
if ($_SERVER['REQUEST_METHOD']==='POST') {
 verify_csrf();$nombre=trim((string)($_POST['nombre']??''));$correo=strtolower(trim((string)($_POST['correo']??'')));$clave=(string)($_POST['clave']??'');
 if ($nombre===''||mb_strlen($nombre)>100||!filter_var($correo,FILTER_VALIDATE_EMAIL)||strlen($clave)<8) flash('Verifica tus datos. La contraseña debe tener al menos 8 caracteres.');
 else {try {$stmt=db()->prepare('INSERT INTO usuarios(nombre,correo,password_hash) VALUES(?,?,?)');$stmt->execute([$nombre,$correo,password_hash($clave,PASSWORD_DEFAULT)]);flash('Cuenta creada. Ya puedes iniciar sesión.','ok');go('login.php');}
 catch(PDOException $e) {if ($e->getCode()==='23000') flash('Ese correo ya está registrado.');else throw $e;}}
 go('registro.php');
}
layout_start('Registro'); ?>
<div class="card auth"><p class="eyebrow">EMPEZAR</p><h1>Crear cuenta docente</h1><form method="post"><?php csrf_input(); ?><label>Nombre<input name="nombre" maxlength="100" required></label><label>Correo electrónico<input type="email" name="correo" autocomplete="email" required></label><label>Contraseña (mínimo 8 caracteres)<input type="password" name="clave" minlength="8" autocomplete="new-password" required></label><button>Crear cuenta</button></form></div>
<?php layout_end();
