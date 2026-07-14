<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/functions.php';
require_admin();

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if ($id) {
    $pdo = db();
    $stmt = $pdo->prepare('SELECT immagine_path, allegato_path FROM eventi WHERE id = ?');
    $stmt->execute([$id]);
    $row = $stmt->fetch();
    if ($row) {
        if ($row['immagine_path']) @unlink(UPLOAD_EVENTI_IMG_DIR . '/' . $row['immagine_path']);
        if ($row['allegato_path']) @unlink(UPLOAD_EVENTI_ALLEGATI_DIR . '/' . $row['allegato_path']);
    }
    $pdo->prepare('DELETE FROM eventi WHERE id = ?')->execute([$id]);
}
header('Location: /admin/dashboard.php');
exit;
