<?php
/**
 * Serve l'allegato collegato a un evento (PDF, documento Word/Excel, zip...).
 * A differenza delle foto e dei PDF di scheda, qui l'obiettivo è il
 * download vero e proprio: il file viene proposto con il suo nome
 * originale (sanificato) invece del nome casuale con cui è salvato su disco.
 */
require_once __DIR__ . '/includes/config.php';

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$id) {
    http_response_code(404);
    exit;
}

$stmt = db()->prepare('SELECT allegato_path, allegato_nome_originale FROM eventi WHERE id = ?');
$stmt->execute([$id]);
$row = $stmt->fetch();

if (!$row || !$row['allegato_path']) {
    http_response_code(404);
    exit;
}

$fullPath = UPLOAD_EVENTI_ALLEGATI_DIR . '/' . $row['allegato_path'];
if (!is_file($fullPath)) {
    http_response_code(404);
    exit;
}

// Il nome originale viene ripulito da virgolette/newline (per l'header)
// e da un eventuale percorso: serve solo per il nome proposto in download.
$nomeOriginale = $row['allegato_nome_originale'] ?: basename($row['allegato_path']);
$nomeOriginale = preg_replace('/[\r\n"]/', '', basename($nomeOriginale));

$mime = mime_content_type($fullPath) ?: 'application/octet-stream';

header('Content-Type: ' . $mime);
header('Content-Disposition: attachment; filename="' . $nomeOriginale . '"');
header('Cache-Control: private, no-store, max-age=0');
header('X-Content-Type-Options: nosniff');
header('Content-Length: ' . filesize($fullPath));
readfile($fullPath);
