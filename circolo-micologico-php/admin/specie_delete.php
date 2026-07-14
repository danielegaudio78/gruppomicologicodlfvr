<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/functions.php';
require_admin();

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$id) { header('Location: /admin/dashboard.php'); exit; }

$pdo = db();

$stmt = $pdo->prepare('SELECT * FROM specie WHERE id = ?');
$stmt->execute([$id]);
$specie = $stmt->fetch();

if ($specie) {
    $stmt = $pdo->prepare('SELECT * FROM foto WHERE specie_id = ?');
    $stmt->execute([$id]);
    foreach ($stmt->fetchAll() as $ph) {
        @unlink(UPLOAD_FOTO_DIR . '/' . $ph['path']);
    }
    if ($specie['pdf_path']) {
        @unlink(UPLOAD_PDF_DIR . '/' . $specie['pdf_path']);
    }
    // Le foto collegate vengono rimosse automaticamente (ON DELETE CASCADE).
    $pdo->prepare('DELETE FROM specie WHERE id = ?')->execute([$id]);
}

header('Location: /admin/dashboard.php');
exit;
