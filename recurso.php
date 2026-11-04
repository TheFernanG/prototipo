<?php
require_once __DIR__.'/config.php';require_login();$id=filter_input(INPUT_GET,'id',FILTER_VALIDATE_INT);
if (!$id){http_response_code(404);exit('Recurso no encontrado');}
$s=db()->prepare('SELECT * FROM recursos WHERE id=? AND usuario_id=?');$s->execute([$id,$_SESSION['usuario_id']]);$r=$s->fetch();if (!$r){http_response_code(404);exit('Recurso no encontrado');}
layout_start('Recurso guardado'); ?>
<div class="card"><p class="eyebrow">RECURSO GUARDADO</p><h1><?=h($r['tema'])?></h1><p><?=h($r['materia'])?> · <?=h($r['nivel'])?> · <?=h($r['tipo'])?></p><div class="output"><?=h($r['resultado'])?></div><div class="actions"><button type="button" onclick="window.print()">Imprimir / Guardar como PDF</button><a class="button secondary" href="panel.php">Volver al panel</a></div></div>
<?php layout_end();
