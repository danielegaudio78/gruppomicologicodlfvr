<?php
/**
 * Serve la fotografia della sezione "Chi siamo" in homepage. Riga singola
 * (id sempre 1): stesso principio degli altri script serve_*.php.
 */
require_once __DIR__ . '/includes/config.php';

$stmt = db()->query('SELECT foto_path FROM pagina_chi_siamo WHERE id = 1');
$row = $stmt->fetch();

if (!$row || !$row['foto_path']) {
    http_response_code(404);
    exit;
}

$fullPath = UPLOAD_CHI_SIAMO_DIR . '/' . $row['foto_path'];
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
