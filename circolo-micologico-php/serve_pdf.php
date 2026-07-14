<?php
/**
 * Serve in modo protetto i PDF di qualunque sezione del sito (specie,
 * lezioni del corso, legislazione, riviste, micotossicologia): un solo
 * script, una tabella di corrispondenza con nome-tabella/colonna/cartella
 * scelti da noi (mai dall'utente), quindi nessun rischio di richiedere
 * file arbitrari dal server.
 *
 * Uso: /serve_pdf.php?tipo=corso&id=5
 * Compatibilità con le versioni precedenti: /serve_pdf.php?specie_id=5
 * continua a funzionare esattamente come prima.
 *
 * Attenzione, va detto con chiarezza: un PDF mostrato in un browser può
 * comunque essere salvato dall'utente tramite il visualizzatore stesso
 * (pulsante "salva" del lettore PDF integrato, stampa in PDF, ecc.).
 * Non esiste un modo tecnico per impedirlo del tutto quando il file deve
 * essere leggibile nel browser: questi accorgimenti (niente URL diretto,
 * niente attachment, no-cache) scoraggiano la condivisione occasionale
 * del link ma non sostituiscono una vera gestione dei diritti digitali.
 */
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/functions.php';

$mappa = [
    'specie'        => ['tabella' => 'specie',        'dir' => UPLOAD_PDF_DIR],
    'corso'         => ['tabella' => 'lezioni_corso',  'dir' => UPLOAD_CORSO_DIR, 'riservato' => true],
    'legislazione'  => ['tabella' => 'legislazione',   'dir' => UPLOAD_LEGISLAZIONE_DIR],
    'riviste'       => ['tabella' => 'pubblicazioni',  'dir' => UPLOAD_RIVISTE_DIR],
    'tossine'       => ['tabella' => 'tossine',        'dir' => UPLOAD_TOSSINE_DIR],
    'sociale'       => ['tabella' => 'documento_sociale', 'dir' => UPLOAD_SOCIALE_DIR],
];

$tipo = $_GET['tipo'] ?? null;
$id = null;

if ($tipo === null && isset($_GET['specie_id'])) {
    // Compatibilità con i link generati dalle versioni precedenti del sito.
    $tipo = 'specie';
    $id = filter_input(INPUT_GET, 'specie_id', FILTER_VALIDATE_INT);
} else {
    $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
}

if (!$tipo || !isset($mappa[$tipo]) || !$id) {
    http_response_code(404);
    exit;
}

$cfg = $mappa[$tipo];

// Alcuni tipi di documento (es. il materiale del corso) sono riservati:
// serviamo il file solo a un socio collegato o all'amministratore, non al
// pubblico. Un 403 invece di un redirect: questo script serve file, non
// pagine, e un redirect qui darebbe solo un'icona di errore nel browser.
if (!empty($cfg['riservato']) && !is_socio() && !is_admin()) {
    http_response_code(403);
    echo 'Contenuto riservato ai soci. Accedi da /soci/login.php';
    exit;
}

// I nomi di tabella qui provengono solo dall'array $mappa sopra (mai
// dall'utente), quindi l'interpolazione nella query è sicura.
$stmt = db()->prepare("SELECT pdf_path FROM {$cfg['tabella']} WHERE id = ?");
$stmt->execute([$id]);
$row = $stmt->fetch();

if (!$row || !$row['pdf_path']) {
    http_response_code(404);
    exit;
}

$fullPath = $cfg['dir'] . '/' . $row['pdf_path'];
if (!is_file($fullPath)) {
    http_response_code(404);
    exit;
}

header('Content-Type: application/pdf');
header('Content-Disposition: inline; filename="scheda.pdf"');
header('Cache-Control: private, no-store, max-age=0');
header('X-Content-Type-Options: nosniff');
header('Content-Length: ' . filesize($fullPath));
readfile($fullPath);

