<?php
// Retire links saved before password recovery was removed.
header('Location: index.php?p=login', true, 303);
exit;
