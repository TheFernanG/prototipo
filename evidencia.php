<?php
require_once __DIR__.'/config.php';require_login();
if ($_SERVER['REQUEST_METHOD']==='POST') {
 verify_csrf();$estrategia=trim((string)($_POST['estrategia']??''));$tema=trim((string)($_POST['tema']??''));$observaciones=trim((string)($_POST['observaciones']??''));$antes=filter_var($_POST['antes']??null,FILTER_VALIDATE_FLOAT);$despues=filter_var($_POST['despues']??null,FILTER_VALIDATE_FLOAT);
 if ($estrategia===''||$tema===''||mb_strlen($estrategia)>150||mb_strlen($tema)>150||mb_strlen($observaciones)>2000||$antes===false||$despues===false||$antes<0||$antes>100||$despues<0||$despues>100) flash('Completa todos los campos y usa porcentajes entre 0 y 100.');
 else {$s=db()->prepare('INSERT INTO evidencias(usuario_id,estrategia,tema,resultado_antes,resultado_despues,observaciones) VALUES(?,?,?,?,?,?)');$s->execute([$_SESSION['usuario_id'],$estrategia,$tema,$antes,$despues,$observaciones]);flash('Evidencia guardada. El cambio observado no demuestra por sí solo causalidad.','ok');go('panel.php');}
 go('evidencia.php');
}
layout_start('Registrar evidencia'); ?>
<div class="card formcard"><p class="eyebrow">MEDIR PARA MEJORAR</p><h1>Registrar evidencia de aula</h1><p>Registra porcentajes de logro sobre el mismo objetivo de aprendizaje y un grupo comparable.</p><form method="post"><?php csrf_input(); ?><label>Estrategia aplicada<input name="estrategia" maxlength="150" placeholder="Ej. Aprendizaje colaborativo" required></label><label>Tema<input name="tema" maxlength="150" required></label><div class="cols"><label>Porcentaje antes (0–100)<input name="antes" type="number" min="0" max="100" step="0.1" required></label><label>Porcentaje después (0–100)<input name="despues" type="number" min="0" max="100" step="0.1" required></label></div><label>Observaciones<textarea name="observaciones" rows="5" maxlength="2000" placeholder="Qué se hizo, cuándo y qué factores pudieron influir"></textarea></label><button>Guardar evidencia</button></form><p class="note">Evita introducir nombres de menores u otra información personal en este prototipo.</p></div>
<?php layout_end();
