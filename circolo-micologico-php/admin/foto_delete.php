<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/functions.php';
require_admin();

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
$specieId = filter_input(INPUT_GET, 'specie_id', FILTER_VALIDATE_INT);
if (!$id) { header('Location: /admin/dashboard.php'); exit; }

$pdo = db();
$stmt = $pdo->prepare('SELECT * FROM foto WHERE id = ?');
$stmt->execute([$id]);
$ph = $stmt->fetch();

if ($ph) {
    @unlink(UPLOAD_FOTO_DIR . '/' . $ph['path']);
    $pdo->prepare('DELETE FROM foto WHERE id = ?')->execute([$id]);
}

header('Location: /admin/specie_form.php?id=' . (int) $specieId);
exit;
