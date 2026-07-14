<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/functions.php';
require_admin();

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if ($id) {
    db()->prepare('DELETE FROM confronto WHERE id = ?')->execute([$id]);
}
header('Location: /admin/dashboard.php');
exit;
