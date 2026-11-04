<?php
require_once __DIR__.'/config.php';require_login();
$s=db()->prepare('SELECT * FROM recursos WHERE usuario_id=? ORDER BY id DESC LIMIT 8');$s->execute([$_SESSION['usuario_id']]);$recursos=$s->fetchAll();
$s=db()->prepare('SELECT * FROM evidencias WHERE usuario_id=? ORDER BY id DESC LIMIT 8');$s->execute([$_SESSION['usuario_id']]);$evidencias=$s->fetchAll();
layout_start('Panel'); ?>
<section class="hero"><p class="eyebrow">CENTRO DE INNOVACIÓN DOCENTE</p><h1>De la capacitación al impacto real</h1><p>Diseña actividades con ayuda de IA y documenta qué cambia en el aula.</p><div class="actions"><a class="button" href="generar.php">✦ Crear recurso</a><a class="button secondary" href="evidencia.php">+ Registrar evidencia</a></div></section>
<div class="cols"><section class="card"><h2>Mis recursos</h2><?php foreach($recursos as $r): ?><a class="item" href="recurso.php?id=<?= (int)$r['id'] ?>"><strong><?=h($r['tema'])?></strong><small><?=h($r['tipo'])?> · <?=h($r['materia'])?> · <?=h($r['fecha'])?></small></a><?php endforeach; if (!$recursos): ?><p>Todavía no has generado recursos.</p><?php endif; ?></section>
<section class="card"><h2>Cambios observados</h2><?php foreach($evidencias as $e): ?><div class="item"><strong><?=h($e['tema'])?></strong><small><?=h($e['estrategia'])?></small><p>Antes: <?=h($e['resultado_antes'])?>% · Después: <?=h($e['resultado_despues'])?>% · Cambio: <?=number_format((float)$e['resultado_despues']-(float)$e['resultado_antes'],1)?> puntos</p></div><?php endforeach; if (!$evidencias): ?><p>Registra resultados para comenzar a comparar.</p><?php endif; ?><p class="note">Una diferencia antes/después no prueba por sí sola que la estrategia causó la mejora.</p></section></div>
<?php layout_end();
