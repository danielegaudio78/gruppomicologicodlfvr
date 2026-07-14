<?php
/**
 * Serve la fotografia di una ricetta. Stesso principio degli altri script
 * serve_*.php: tabella indipendente, accesso solo tramite id, cartella
 * bloccata a livello di server.
 */
require_once __DIR__ . '/includes/config.php';

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$id) {
    http_response_code(404);
    exit;
}

$stmt = db()->prepare('SELECT foto_path FROM ricetta WHERE id = ?');
$stmt->execute([$id]);
$row = $stmt->fetch();

if (!$row || !$row['foto_path']) {
    http_response_code(404);
    exit;
}

$fullPath = UPLOAD_RICETTE_DIR . '/' . $row['foto_path'];
if (!is_file($fullPath)) {
    http_response_code(404);
    exit;
}

$mime = mime_content_type($fullPath) ?: 'image/jpeg';

header('Content-Type: ' . $mime);
header('Content-Disposition: inline; filename="foto.jpg"');
header('Cache-Control: public, max-age=86400');
header('X-Content-Type-Options: nosniff');
header('Content-Length: ' . filesize($fullPath));
readfile($fullPath);
