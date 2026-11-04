<?php require_once __DIR__.'/config.php'; go(!empty($_SESSION['usuario_id'])?'panel.php':'login.php');
