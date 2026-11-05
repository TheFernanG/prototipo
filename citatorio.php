<?php
declare(strict_types=1);
require __DIR__.'/app.php';
require __DIR__.'/pdf_citatorio.php';

try {
    if (isset($_SESSION['uid']) && time()-($_SESSION['last']??0)>1800) destroySession();
    $u=user();
    if (!$u) {
        http_response_code(401);
        exit('Inicia sesión para descargar el citatorio.');
    }
    $id=filter_var($_GET['id']??null,FILTER_VALIDATE_INT);
    if (!$id || $id<1) {
        http_response_code(404);
        exit('Citatorio no disponible.');
    }
    // A message remains private to its sender and recipient.
    $message=one("SELECT m.*,a.nombre remitente,b.nombre destinatario,b.rol destinatario_rol
        FROM mensajes m JOIN usuarios a ON a.id=m.remitente_id
        JOIN usuarios b ON b.id=m.destinatario_id
        WHERE m.id=? AND (m.remitente_id=? OR m.destinatario_id=?)
            AND b.rol IN ('alumno','padre')",[$id,$u['id'],$u['id']]);
    if (!$message) {
        http_response_code(404);
        exit('Citatorio no disponible.');
    }
    $pdf=citatorioPdf($message);
    $_SESSION['last']=time();
    header('Content-Type: application/pdf');
    header('Content-Disposition: attachment; filename="Citatorio_'.$id.'.pdf"');
    header('Content-Length: '.strlen($pdf));
    echo $pdf;
} catch (Throwable $e) {
    error_log($e->getMessage());
    http_response_code(503);
    header('Content-Type: text/plain; charset=utf-8');
    echo 'No se pudo generar el citatorio. Intenta nuevamente.';
}
