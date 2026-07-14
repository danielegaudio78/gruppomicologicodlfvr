<?php
/**
 * Configurazione generale del sito.
 * Modifica qui il nome del circolo, i contatti e i social.
 */

// --- Impostazioni sessione (usate per il login amministratore) ---
if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'httponly' => true,   // il cookie di sessione non è leggibile da JavaScript
        'samesite' => 'Lax',
    ]);
    session_start();
}

// --- Dati del circolo (personalizza pure) ---
define('NOME_CIRCOLO', 'Circolo Micologico Sottobosco');
define('EMAIL_CIRCOLO', 'info@circolosottobosco.it');
define('INDIRIZZO_CIRCOLO', 'Via del Bosco 14, 31100 Treviso (TV)');
define('SOCIAL_FACEBOOK', '#');
define('SOCIAL_INSTAGRAM', '#');
define('SOCIAL_YOUTUBE', '#');

// Indirizzo pubblico del sito, SENZA slash finale: serve solo per generare
// indirizzi assoluti nella sitemap XML (Google la richiede così).
// IMPORTANTE: sostituiscilo con il dominio reale quando vai online.
define('URL_SITO', 'https://www.circolosottobosco.it');

// --- Percorsi ---
define('BASE_PATH', dirname(__DIR__));
define('DB_PATH', BASE_PATH . '/db/circolo.sqlite');
define('UPLOAD_FOTO_DIR', BASE_PATH . '/uploads/foto');
define('UPLOAD_PDF_DIR', BASE_PATH . '/uploads/pdf');
define('UPLOAD_SLIDE_DIR', BASE_PATH . '/uploads/slide');
define('UPLOAD_CORSO_DIR', BASE_PATH . '/uploads/documenti/corso');
define('UPLOAD_LEGISLAZIONE_DIR', BASE_PATH . '/uploads/documenti/legislazione');
define('UPLOAD_RIVISTE_DIR', BASE_PATH . '/uploads/documenti/riviste');
define('UPLOAD_TOSSINE_DIR', BASE_PATH . '/uploads/documenti/tossine');
define('UPLOAD_SOCIALE_DIR', BASE_PATH . '/uploads/documenti/sociale');
define('UPLOAD_CONSIGLIO_DIR', BASE_PATH . '/uploads/consiglio');
define('UPLOAD_EVENTI_IMG_DIR', BASE_PATH . '/uploads/eventi/immagini');
define('UPLOAD_EVENTI_ALLEGATI_DIR', BASE_PATH . '/uploads/eventi/allegati');
define('UPLOAD_RICETTE_DIR', BASE_PATH . '/uploads/ricette');
define('UPLOAD_CHI_SIAMO_DIR', BASE_PATH . '/uploads/chi-siamo');
define('UPLOAD_GALLERIA_SOCI_DIR', BASE_PATH . '/uploads/galleria-soci');

// Limiti di upload
define('MAX_FOTO_MB', 8);
define('MAX_PDF_MB', 20);
define('MAX_SLIDE_MB', 10);
define('MAX_ALLEGATO_MB', 15);

// --- Connessione al database (SQLite, nessun server esterno richiesto) ---
function db(): PDO
{
    static $pdo = null;
    if ($pdo === null) {
        assicura_cartelle_upload();
        $nuovoDb = !file_exists(DB_PATH);
        $pdo = new PDO('sqlite:' . DB_PATH);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->exec('PRAGMA foreign_keys = ON');
        if ($nuovoDb) {
            require_once __DIR__ . '/db_init.php';
            inizializza_database($pdo);
        } else {
            require_once __DIR__ . '/db_init.php';
            aggiorna_database($pdo);
        }
    }
    return $pdo;
}

/**
 * Crea le cartelle di upload se non esistono ancora (ad es. se lo zip del
 * progetto è stato caricato senza le sottocartelle vuote) e ne copia la
 * protezione .htaccess, così i nuovi tipi di documento restano al sicuro
 * anche su un'installazione aggiornata da una versione precedente.
 */
function assicura_cartelle_upload(): void
{
    $cartelle = [
        UPLOAD_FOTO_DIR, UPLOAD_PDF_DIR, UPLOAD_SLIDE_DIR,
        UPLOAD_CORSO_DIR, UPLOAD_LEGISLAZIONE_DIR, UPLOAD_RIVISTE_DIR, UPLOAD_TOSSINE_DIR,
        UPLOAD_SOCIALE_DIR, UPLOAD_CONSIGLIO_DIR, UPLOAD_EVENTI_IMG_DIR, UPLOAD_EVENTI_ALLEGATI_DIR,
        UPLOAD_RICETTE_DIR, UPLOAD_CHI_SIAMO_DIR, UPLOAD_GALLERIA_SOCI_DIR,
    ];
    $regolaDeny = "<IfModule mod_authz_core.c>\n    Require all denied\n</IfModule>\n"
        . "<IfModule !mod_authz_core.c>\n    Order allow,deny\n    Deny from all\n</IfModule>\n";

    foreach ($cartelle as $cartella) {
        if (!is_dir($cartella)) {
            @mkdir($cartella, 0755, true);
        }
        $htaccess = $cartella . '/.htaccess';
        if (!file_exists($htaccess)) {
            @file_put_contents($htaccess, $regolaDeny);
        }
    }
}
