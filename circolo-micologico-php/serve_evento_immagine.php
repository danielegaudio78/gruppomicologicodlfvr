<?php
/**
 * Serve l'immagine collegata a un evento del calendario.
 */
require_once __DIR__ . '/includes/config.php';

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$id) {
    http_response_code(404);
    exit;
}

$stmt = db()->prepare('SELECT immagine_path FROM eventi WHERE id = ?');
$stmt->execute([$id]);
$row = $stmt->fetch();

if (!$row || !$row['immagine_path']) {
    http_response_code(404);
    exit;
}

$fullPath = UPLOAD_EVENTI_IMG_DIR . '/' . $row['immagine_path'];
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
