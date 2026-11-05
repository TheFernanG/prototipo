<?php
if (!isset($u['id'])) { http_response_code(404); exit; }
$where=[
    'entrada'=>'m.destinatario_id=? AND m.leido=0',
    'leidos'=>'m.destinatario_id=? AND m.leido=1',
    'enviados'=>'m.remitente_id=?',
][$folder];
$msgs=rows("SELECT m.*,a.nombre remitente,b.nombre destinatario,b.rol destinatario_rol
    FROM mensajes m JOIN usuarios a ON a.id=m.remitente_id
    JOIN usuarios b ON b.id=m.destinatario_id
    WHERE $where AND NOT EXISTS (SELECT 1 FROM mensajes_eliminados e
        WHERE e.mensaje_id=m.id AND e.usuario_id=?)
    ORDER BY (m.id=?) DESC,m.creado DESC,m.id DESC LIMIT 100",
    [$u['id'],$u['id'],$openedId]);
?>
<?php if (!$msgs): ?><p class="empty">Esta carpeta está vacía.</p><?php endif; ?>
<?php foreach ($msgs as $m): ?>
    <?php if ($folder==='entrada'): ?>
        <article class="message message-preview">
            <span><strong><?=h($m['asunto'])?></strong><br><small>De <?=h($m['remitente'])?></small></span>
            <form method="post">
                <?=csrf()?>
                <input type="hidden" name="action" value="read">
                <input type="hidden" name="id" value="<?=h($m['id'])?>">
                <button type="submit" class="secondary small" aria-label="Abrir mensaje: <?=h($m['asunto'])?>">Abrir</button>
            </form>
    <?php else: ?>
        <details class="message" id="mensaje-<?=h($m['id'])?>" data-message-id="<?=h($m['id'])?>" <?=$openedId===(int)$m['id']?'open':''?>>
            <summary><strong><?=h($m['asunto'])?></strong><small><?=h($m['remitente'])?> → <?=h($m['destinatario'])?></small></summary>
            <p class="pre"><?=h($m['cuerpo'])?></p><small><?=h($m['creado'])?></small>
            <?php if (in_array($m['destinatario_rol'],['alumno','padre'],true)): ?>
                <p><a class="button secondary small" href="citatorio.php?id=<?=h($m['id'])?>">Descargar citatorio PDF</a></p>
            <?php endif; ?>
    <?php endif; ?>
            <form method="post" data-delete-message>
                <?=csrf()?>
                <input type="hidden" name="action" value="message_delete">
                <input type="hidden" name="id" value="<?=h($m['id'])?>">
                <input type="hidden" name="carpeta" value="<?=h($folder)?>">
                <button class="danger small" aria-label="Eliminar mensaje: <?=h($m['asunto'])?>">Eliminar mensaje</button>
            </form>
    <?php if ($folder==='entrada'): ?></article><?php else: ?></details><?php endif; ?>
<?php endforeach; ?>
