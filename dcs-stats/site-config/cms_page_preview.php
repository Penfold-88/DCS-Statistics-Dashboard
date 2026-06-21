<?php

$id = preg_replace('/[^a-f0-9]/', '', (string)($_GET['id'] ?? ''));
header('Location: ../page_preview.php?id=' . rawurlencode($id), true, 302);
exit;
