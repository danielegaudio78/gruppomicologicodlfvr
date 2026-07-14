<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/functions.php';
require_admin();

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if ($id) {
    $pdo = db();
    $stmt = $pdo->prepare('SELECT foto_path FROM ricetta WHERE id = ?');
    $stmt->execute([$id]);
    $row = $stmt->fetch();
    if ($row && $row['foto_path']) {
        @unlink(UPLOAD_RICETTE_DIR . '/' . $row['foto_path']);
    }
    $pdo->prepare('DELETE FROM ricetta WHERE id = ?')->execute([$id]);
}
header('Location: /admin/dashboard.php');
exit;
