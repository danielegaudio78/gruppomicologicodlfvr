<?php
/**
 * Serve una fotografia del carosello della homepage.
 * Tabella indipendente da "foto" (che appartiene alle specie): le foto
 * del carosello non sono legate a nessun fungo del database.
 */
require_once __DIR__ . '/includes/config.php';

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$id) {
    http_response_code(404);
    exit;
}

$stmt = db()->prepare('SELECT path FROM home_slide WHERE id = ?');
$stmt->execute([$id]);
$row = $stmt->fetch();

if (!$row) {
    http_response_code(404);
    exit;
}

$fullPath = UPLOAD_SLIDE_DIR . '/' . $row['path'];
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
