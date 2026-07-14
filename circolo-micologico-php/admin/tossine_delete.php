<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/functions.php';
require_admin();

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if ($id) {
    $pdo = db();
    $stmt = $pdo->prepare('SELECT pdf_path FROM tossine WHERE id = ?');
    $stmt->execute([$id]);
    $row = $stmt->fetch();
    if ($row && $row['pdf_path']) {
        @unlink(UPLOAD_TOSSINE_DIR . '/' . $row['pdf_path']);
    }
    $pdo->prepare('DELETE FROM tossine WHERE id = ?')->execute([$id]);
}
header('Location: /admin/dashboard.php');
exit;
