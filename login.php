<?php
require_once __DIR__.'/config.php';
// Al abrir el inicio de sesión por GET, destruir cualquier sesión previa.
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $p = session_get_cookie_params();
        setcookie(session_name(), '', time()-42000, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
    }
    session_destroy(); // session destroy solicitado
    session_start();
    session_regenerate_id(true);
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $correo = strtolower(trim((string)($_POST['correo']??'')));
    $clave = (string)($_POST['clave']??'');
    $stmt=db()->prepare('SELECT id,nombre,password_hash FROM usuarios WHERE correo=?');
    $stmt->execute([$correo]); $u=$stmt->fetch();
    if ($u && password_verify($clave,$u['password_hash'])) {
        session_regenerate_id(true);
        $_SESSION['usuario_id']=(int)$u['id']; $_SESSION['nombre']=$u['nombre'];
        go('panel.php');
    }
    flash('Correo o contraseña incorrectos.');go('login.php');
}
layout_start('Iniciar sesión'); ?>
<div class="card auth"><p class="eyebrow">BIENVENIDO</p><h1>Inicia sesión</h1><p>Transforma tu formación docente en estrategias para el aula.</p>
<form method="post"><?php csrf_input(); ?><label>Correo electrónico<input type="email" name="correo" autocomplete="email" required></label><label>Contraseña<input type="password" name="clave" autocomplete="current-password" required></label><button>Entrar al panel</button></form><p>¿Aún no tienes cuenta? <a href="registro.php">Regístrate</a></p></div>
<?php layout_end();
