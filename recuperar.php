<?php
require __DIR__.'/app.php';
header('Referrer-Policy: no-referrer');

$error = '';
$codigo = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        checkCsrf();
        $action = $_POST['action'] ?? '';
        if ($action === 'restablecer') {
            restablecerConCodigo();
            unset($_SESSION['pregunta_pendiente']);
            flash('Contraseña restablecida. Ya puedes iniciar sesión.');
            redirectTo('login');
        }
        if ($action === 'buscar_pregunta') {
            unset($_SESSION['pregunta_pendiente']);
            iniciarPreguntaRecuperacion();
        } elseif ($action === 'verificar') {
            $codigo = emitirCodigoRecuperacion();
            unset($_SESSION['pregunta_pendiente']);
        }
    } catch (PDOException $e) {
        error_log($e->getMessage());
        $error = 'No se pudo completar la recuperación. Revisa la configuración de la base de datos.';
    } catch (RuntimeException $e) {
        $error = $e->getMessage();
    }
}
if (isset($_GET['reiniciar']) || ($_SESSION['pregunta_pendiente']['vence'] ?? 0) <= time()) {
    unset($_SESSION['pregunta_pendiente']);
}
$pendiente = $_SESSION['pregunta_pendiente'] ?? null;
?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Recuperar contraseña · Conecta Escuela</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
<main class="login-card">
    <h1>Recuperar contraseña</h1>
    <p><a href="index.php?p=login">Volver al inicio de sesión</a></p>
    <?php if ($error): ?><p class="alert error" role="alert"><?=h($error)?></p><?php endif; ?>

    <?php if ($codigo): ?>
        <section class="card">
            <h2>Respuesta verificada</h2>
            <canvas id="qr-recuperacion" width="246" height="246" data-code="<?=h($codigo)?>" aria-label="QR con tu código de recuperación"></canvas>
            <p>El código es de un solo uso y vence en 10 minutos. Si lo escaneas con otro dispositivo, copia el código que aparezca y pégalo en el formulario de abajo. Mantenlo en privado.</p>
            <label>Código generado <input readonly value="<?=h($codigo)?>"></label>
        </section>
        <script src="assets/qr-recuperacion.js" defer></script>
    <?php elseif ($pendiente): ?>
        <form method="post" class="card">
            <h2>Responde tu pregunta de seguridad</h2>
            <p>Cuenta: <strong><?=h($pendiente['email'])?></strong></p>
            <?=csrf()?>
            <input type="hidden" name="action" value="verificar">
            <label><?=h(preguntasRecuperacion()[$pendiente['pregunta']])?>
                <input type="password" name="respuesta" maxlength="72" required>
            </label>
            <button>Verificar y generar QR</button>
            <p><a href="recuperar.php?reiniciar=1">Usar otro correo</a></p>
        </form>
    <?php else: ?>
        <form method="post" class="card">
            <h2>Encuentra tu pregunta de seguridad</h2>
            <?=csrf()?>
            <input type="hidden" name="action" value="buscar_pregunta">
            <label>Correo <input name="email" type="email" maxlength="190" required></label>
            <button>Mostrar mi pregunta</button>
        </form>
        <p class="help">Si todavía no configuraste una pregunta, solicita ayuda a la administración de tu escuela.</p>
    <?php endif; ?>

    <form method="post" class="card">
        <h2>Elige tu nueva contraseña</h2>
        <?=csrf()?>
        <input type="hidden" name="action" value="restablecer">
        <label>Código de recuperación <input name="codigo" value="<?=h($codigo)?>" minlength="48" maxlength="48" required></label>
        <label>Nueva contraseña <input type="password" name="nueva" minlength="10" maxlength="72" autocomplete="new-password" required></label>
        <label>Confirmar contraseña <input type="password" name="confirmar" minlength="10" maxlength="72" autocomplete="new-password" required></label>
        <button>Guardar nueva contraseña</button>
    </form>
</main>
</body>
</html>
