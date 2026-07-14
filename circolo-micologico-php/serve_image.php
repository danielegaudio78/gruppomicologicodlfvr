<?php
/**
 * Serve una fotografia della galleria SENZA esporre mai il percorso reale
 * del file caricato. L'accesso avviene solo tramite l'id della riga nella
 * tabella "foto": non è possibile indovinare o richiedere altri file
 * (niente path traversal, niente elenco diretto della cartella uploads/).
 *
 * La cartella uploads/foto è comunque bloccata anche a livello di server
 * (vedi uploads/foto/.htaccess), quindi l'unico modo per ottenere
 * un'immagine è passare da qui.
 */
require_once __DIR__ . '/includes/config.php';

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$id) {
    http_response_code(404);
    exit;
}

$stmt = db()->prepare('SELECT path FROM foto WHERE id = ?');
$stmt->execute([$id]);
$row = $stmt->fetch();

if (!$row) {
    http_response_code(404);
    exit;
}

$fullPath = UPLOAD_FOTO_DIR . '/' . $row['path'];
if (!is_file($fullPath)) {
    http_response_code(404);
    exit;
}

$mime = mime_content_type($fullPath) ?: 'image/jpeg';

// Content-Disposition "inline" evita che il browser proponga subito il
// salvataggio. Cache-Control è ora pubblico e cacheabile (invece di
// no-store): serve a farsi indicizzare correttamente da Google Immagini
// e a caricare più in fretta per i visitatori. Non è questo header a
// impedire il download — non potrebbe comunque farlo — ci pensano la
// filigrana visibile e il blocco di tasto destro/trascinamento lato sito.
header('Content-Type: ' . $mime);
header('Content-Disposition: inline; filename="foto.jpg"');
header('Cache-Control: public, max-age=86400');
header('X-Content-Type-Options: nosniff');
header('Content-Length: ' . filesize($fullPath));
readfile($fullPath);
