<?php
/**
 * Endpoint JSON per la bacheca interna dei soci.
 * GET  ?dopo=ID   -> restituisce i messaggi con id maggiore di ID
 *                    (o gli ultimi 50 se "dopo" non è indicato)
 * POST testo=...  -> pubblica un nuovo messaggio a nome del socio collegato
 *
 * Richiede una sessione socio valida: non essendo una pagina HTML,
 * in assenza di sessione risponde con JSON e codice 401 invece di
 * reindirizzare al login.
 */
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/functions.php';

header('Content-Type: application/json; charset=utf-8');

if (!is_socio()) {
    http_response_code(401);
    echo json_encode(['errore' => 'Accesso richiesto']);
    exit;
}

$pdo = db();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $testo = trim($_POST['testo'] ?? '');
    if ($testo === '') {
        http_response_code(422);
        echo json_encode(['errore' => 'Il messaggio non può essere vuoto']);
        exit;
    }
    if (lunghezza_testo($testo) > 2000) {
        $testo = taglia_testo($testo, 2000);
    }
    $stmt = $pdo->prepare('INSERT INTO messaggi_bacheca (socio_id, testo) VALUES (?, ?)');
    $stmt->execute([$_SESSION['socio_id'], $testo]);
    echo json_encode(['ok' => true]);
    exit;
}

// --- GET: elenco messaggi ---
$dopo = filter_input(INPUT_GET, 'dopo', FILTER_VALIDATE_INT);

if ($dopo) {
    $stmt = $pdo->prepare("
        SELECT m.id, m.testo, m.creato_il, s.nome AS autore
        FROM messaggi_bacheca m JOIN soci s ON s.id = m.socio_id
        WHERE m.id > ? ORDER BY m.id ASC
    ");
    $stmt->execute([$dopo]);
    $righe = $stmt->fetchAll();
} else {
    $stmt = $pdo->query("
        SELECT m.id, m.testo, m.creato_il, s.nome AS autore
        FROM messaggi_bacheca m JOIN soci s ON s.id = m.socio_id
        ORDER BY m.id DESC LIMIT 50
    ");
    $righe = array_reverse($stmt->fetchAll());
}

$messaggi = array_map(function ($r) {
    return [
        'id' => (int) $r['id'],
        'autore' => $r['autore'],
        'testo' => $r['testo'],
        'quando' => data_ora_it($r['creato_il']),
    ];
}, $righe);

echo json_encode(['messaggi' => $messaggi, 'socio_corrente' => $_SESSION['socio_nome']]);
