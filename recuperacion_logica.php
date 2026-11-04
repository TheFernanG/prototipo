<?php
declare(strict_types=1);

function preguntasRecuperacion(): array {
    return [
        'escuela' => '¿Cómo se llamaba tu primera escuela?',
        'mascota' => '¿Cómo se llamaba tu primera mascota?',
        'lugar' => '¿Cuál era tu lugar favorito de la infancia?',
    ];
}

function respuestaRecuperacion(): string {
    $respuesta = val('respuesta', 72);
    return function_exists('mb_strtolower') ? mb_strtolower($respuesta, 'UTF-8') : strtolower($respuesta);
}

function limitarRecuperacion(string $email): void {
    // Límite por cuenta y por dirección, independiente del inicio de sesión.
    $claves = ['cuenta:' . strtolower($email), 'ip:' . ($_SERVER['REMOTE_ADDR'] ?? 'local')];
    foreach ($claves as $i => $clave) {
        $key = hash('sha256', 'recuperacion:' . $clave);
        q('INSERT INTO intentos_login(clave,intentos,ultimo_intento) VALUES(?,1,NOW())
            ON DUPLICATE KEY UPDATE
            intentos=IF(ultimo_intento<=DATE_SUB(NOW(),INTERVAL 15 MINUTE),1,intentos+1),
            ultimo_intento=IF(ultimo_intento<=DATE_SUB(NOW(),INTERVAL 15 MINUTE),NOW(),ultimo_intento)', [$key]);
        if ((int)one('SELECT intentos FROM intentos_login WHERE clave=?', [$key])['intentos'] > ($i === 0 ? 5 : 20)) {
            throw new RuntimeException('Demasiados intentos. Intenta de nuevo en 15 minutos.');
        }
    }
}

function guardarPreguntaRecuperacion(array $usuario): void {
    if (!password_verify(val('actual', 200), $usuario['password'])) {
        throw new RuntimeException('La contraseña actual no coincide.');
    }
    $pregunta = val('pregunta', 20);
    if (!isset(preguntasRecuperacion()[$pregunta])) {
        throw new RuntimeException('Selecciona una pregunta válida.');
    }
    $respuesta = password_hash(respuestaRecuperacion(), PASSWORD_DEFAULT);
    q('INSERT INTO recuperacion_cuenta(usuario_id,pregunta,respuesta) VALUES(?,?,?)
        ON DUPLICATE KEY UPDATE pregunta=VALUES(pregunta),respuesta=VALUES(respuesta),
        token_hash=NULL,vence=NULL,password_version=NULL', [$usuario['id'], $pregunta, $respuesta]);
}

function emitirCodigoRecuperacion(): string {
    $email = val('email', 190);
    $pregunta = val('pregunta', 20);
    $respuesta = respuestaRecuperacion();
    limitarRecuperacion($email);
    $cuenta = one('SELECT u.id,u.password,r.pregunta,r.respuesta FROM usuarios u
        JOIN recuperacion_cuenta r ON r.usuario_id=u.id WHERE u.email=? AND u.activo=1', [$email]);
    $valida = password_verify($respuesta, $cuenta['respuesta'] ?? '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2uheWG/igi.');
    if (!$cuenta || !$valida || $cuenta['pregunta'] !== $pregunta) {
        throw new RuntimeException('No se pudo verificar la cuenta, la pregunta y la respuesta.');
    }

    $codigo = bin2hex(random_bytes(24));
    $actualizado = q('UPDATE recuperacion_cuenta r JOIN usuarios u ON u.id=r.usuario_id
        SET r.token_hash=?,r.vence=DATE_ADD(NOW(),INTERVAL 10 MINUTE),r.password_version=u.password
        WHERE r.usuario_id=? AND BINARY r.respuesta=? AND r.pregunta=? AND BINARY u.password=? AND u.activo=1',
        [hash('sha256', $codigo), $cuenta['id'], $cuenta['respuesta'], $pregunta, $cuenta['password']]);
    if (!$actualizado->rowCount()) {
        throw new RuntimeException('Los datos de recuperación cambiaron. Vuelve a intentar.');
    }
    return $codigo;
}

function restablecerConCodigo(): void {
    $codigo = strtolower(val('codigo', 48));
    $password = val('nueva', 72);
    if (strlen($password) < 10 || $password !== val('confirmar', 72)) {
        throw new RuntimeException('La contraseña debe tener al menos 10 caracteres y coincidir con la confirmación.');
    }
    if (!preg_match('/^[a-f0-9]{48}$/D', $codigo)) {
        throw new RuntimeException('El código es inválido o venció.');
    }
    $hash = hash('sha256', $codigo);
    if (!one('SELECT usuario_id FROM recuperacion_cuenta WHERE token_hash=? AND vence>NOW()', [$hash])) {
        throw new RuntimeException('El código es inválido o venció.');
    }
    $cambio = q('UPDATE usuarios u JOIN recuperacion_cuenta r ON r.usuario_id=u.id
        SET u.password=?,r.token_hash=NULL,r.vence=NULL,r.password_version=NULL
        WHERE r.token_hash=? AND r.vence>NOW() AND u.activo=1 AND BINARY u.password=r.password_version',
        [password_hash($password, PASSWORD_DEFAULT), $hash]);
    if (!$cambio->rowCount()) {
        throw new RuntimeException('El código es inválido o venció.');
    }
}
