<?php
declare(strict_types=1);
const DB_HOST = 'localhost';
const DB_NAME = 'edumentor_php';
const DB_USER = 'root';
const DB_PASS = '';
// Opcional: coloca tu clave de Gemini aquí, nunca la subas a un repositorio público.
const GEMINI_API_KEY = '';
const GEMINI_MODEL = 'gemini-2.5-flash';
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start(['cookie_httponly' => true, 'cookie_samesite' => 'Lax', 'use_strict_mode' => true]);
}
function db(): PDO {
    static $pdo = null;
    if ($pdo === null) {
        $pdo = new PDO('mysql:host='.DB_HOST.';dbname='.DB_NAME.';charset=utf8mb4', DB_USER, DB_PASS,
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
             PDO::ATTR_EMULATE_PREPARES => false]);
    }
    return $pdo;
}
function h($s): string { return htmlspecialchars((string)$s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); }
function go(string $path): never { header('Location: '.$path); exit; }
function flash(string $message, string $kind='error'): void { $_SESSION['flash'][]=['message'=>$message,'kind'=>$kind]; }
function require_login(): void { if (empty($_SESSION['usuario_id'])) go('login.php'); }
function csrf_token(): string { if (empty($_SESSION['csrf'])) $_SESSION['csrf']=bin2hex(random_bytes(32)); return $_SESSION['csrf']; }
function csrf_input(): void { echo '<input type="hidden" name="csrf" value="'.h(csrf_token()).'">'; }
function verify_csrf(): void { if (!hash_equals(csrf_token(), (string)($_POST['csrf']??''))) { http_response_code(403); exit('Solicitud no válida. Recarga la página.'); } }
function layout_start(string $title): void {
    echo '<!doctype html><html lang="es"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>'.h($title).' · EduMentor IA</title><link rel="stylesheet" href="assets/estilos.css"></head><body>';
    echo '<header><a class="logo" href="index.php">✦ EduMentor <em>IA</em></a><nav>';
    if (!empty($_SESSION['usuario_id'])) {
        echo '<span>Hola, '.h($_SESSION['nombre']).'</span><a href="panel.php">Panel</a><a href="generar.php">Generar</a><a href="evidencia.php">Evidencias</a><form action="salir.php" method="post">';csrf_input();echo '<button class="textbutton">Salir</button></form>';
    } else echo '<a href="login.php">Entrar</a><a href="registro.php">Registrarme</a>';
    echo '</nav></header><main>';
    foreach ($_SESSION['flash']??[] as $f) echo '<div class="alert '.h($f['kind']).'" role="status">'.h($f['message']).'</div>';
    unset($_SESSION['flash']);
}
function layout_end(): void { echo '</main><footer>EduMentor IA · Prototipo de HackaEdu · Revisa siempre las sugerencias de la IA antes de usarlas en clase.</footer></body></html>'; }
