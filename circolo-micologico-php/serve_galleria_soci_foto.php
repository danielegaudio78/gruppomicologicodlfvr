<?php
/**
 * Serve una fotografia della galleria riservata ai soci. A differenza
 * degli altri script serve_*.php (pubblici), questo richiede una sessione
 * socio o amministratore valida: un visitatore qualunque, anche
 * conoscendo l'id della foto, riceve un 403 e non l'immagine.
 */
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/functions.php';

if (!is_socio() && !is_admin()) {
    http_response_code(403);
    exit;
}

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$id) {
    http_response_code(404);
    exit;
}

$stmt = db()->prepare('SELECT path FROM galleria_soci WHERE id = ?');
$stmt->execute([$id]);
$row = $stmt->fetch();

if (!$row) {
    http_response_code(404);
    exit;
}

$fullPath = UPLOAD_GALLERIA_SOCI_DIR . '/' . $row['path'];
if (!is_file($fullPath)) {
    http_response_code(404);
    exit;
}

$mime = mime_content_type($fullPath) ?: 'image/jpeg';

header('Content-Type: ' . $mime);
header('Content-Disposition: inline; filename="foto.jpg"');
// Riservata: a differenza delle foto pubbliche del sito, qui NON usiamo
// cache pubblica. Un proxy o una cache condivisa non deve poter servire
// questa immagine a qualcun altro al posto nostro.
header('Cache-Control: private, no-store, max-age=0');
header('X-Content-Type-Options: nosniff');
header('Content-Length: ' . filesize($fullPath));
readfile($fullPath);
