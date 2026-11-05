<?php
if (!isset($u['id'])) { http_response_code(404); exit; }
$where=[
    'entrada'=>'m.destinatario_id=? AND m.leido=0',
    'leidos'=>'m.destinatario_id=? AND m.leido=1',
    'enviados'=>'m.remitente_id=?',
][$folder];
$msgs=rows("SELECT m.*,a.nombre remitente,b.nombre destinatario
    FROM mensajes m JOIN usuarios a ON a.id=m.remitente_id
    JOIN usuarios b ON b.id=m.destinatario_id
    WHERE $where ORDER BY (m.id=?) DESC,m.creado DESC,m.id DESC LIMIT 100",
    [$u['id'],$openedId]);
?>
<?php if (!$msgs): ?><p class="empty">Esta carpeta está vacía.</p><?php endif; ?>
<?php foreach ($msgs as $m): ?>
    <?php if ($folder==='entrada'): ?>
        <form class="message message-preview" method="post">
            <?=csrf()?>
            <input type="hidden" name="action" value="read">
            <input type="hidden" name="id" value="<?=h($m['id'])?>">
            <span><strong><?=h($m['asunto'])?></strong><br><small>De <?=h($m['remitente'])?></small></span>
            <button type="submit" class="secondary small" aria-label="Abrir mensaje: <?=h($m['asunto'])?>">Abrir</button>
        </form>
    <?php else: ?>
        <details class="message" id="mensaje-<?=h($m['id'])?>" data-message-id="<?=h($m['id'])?>" <?=$openedId===(int)$m['id']?'open':''?>>
            <summary><strong><?=h($m['asunto'])?></strong><small><?=h($m['remitente'])?> → <?=h($m['destinatario'])?></small></summary>
            <p class="pre"><?=h($m['cuerpo'])?></p><small><?=h($m['creado'])?></small>
        </details>
    <?php endif; ?>
<?php endforeach; ?>
