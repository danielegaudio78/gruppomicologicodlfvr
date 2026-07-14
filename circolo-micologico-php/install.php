<?php
/**
 * Configurazione iniziale: crea il primo account amministratore.
 * Va eseguito una sola volta la prima volta che il sito viene messo online,
 * poi il file va ELIMINATO (o quantomeno rinominato) dal server per
 * evitare che chiunque possa ricrearne un altro.
 */
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/functions.php';

$pdo = db();
$esistente = (int) $pdo->query('SELECT COUNT(*) FROM admin')->fetchColumn();

$errore = '';
$fatto = false;

if ($esistente > 0) {
    $errore = 'Un account amministratore esiste già. Per motivi di sicurezza questo file non permette di crearne un altro: elimina install.php dal server.';
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $conferma = $_POST['conferma'] ?? '';

    if (strlen($username) < 3) {
        $errore = 'Scegli un nome utente di almeno 3 caratteri.';
    } elseif (strlen($password) < 8) {
        $errore = 'La password deve avere almeno 8 caratteri.';
    } elseif ($password !== $conferma) {
        $errore = 'Le due password non coincidono.';
    } else {
        $stmt = $pdo->prepare('INSERT INTO admin (username, password_hash) VALUES (?, ?)');
        $stmt->execute([$username, password_hash($password, PASSWORD_DEFAULT)]);
        $fatto = true;
    }
}
$pageTitle = 'Configurazione iniziale — ' . NOME_CIRCOLO;
?>
<!DOCTYPE html>
<html lang="it">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= h($pageTitle) ?></title>
<link href="https://fonts.googleapis.com/css2?family=Fraunces:wght@600&family=Work+Sans:wght@400;500&family=IBM+Plex+Mono&display=swap" rel="stylesheet">
<link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>
<div class="admin-shell" style="padding-top:5rem;max-width:460px;">
  <div class="admin-card">
    <div class="eyebrow">Primo avvio</div>
    <h2>Crea l'account amministratore</h2>

    <?php if ($fatto): ?>
      <div class="alert alert-ok">Account creato con successo. <b>Elimina ora il file install.php dal server</b>, poi accedi da <a href="/admin/login.php">/admin/login.php</a>.</div>
    <?php else: ?>
      <?php if ($errore): ?><div class="alert alert-err"><?= h($errore) ?></div><?php endif; ?>
      <?php if ($esistente === 0): ?>
        <form method="post">
          <div class="field"><label for="username">Nome utente</label><input type="text" id="username" name="username" required></div>
          <div class="field"><label for="password">Password</label><input type="password" id="password" name="password" required></div>
          <div class="field"><label for="conferma">Conferma password</label><input type="password" id="conferma" name="conferma" required></div>
          <button class="btn btn-primary" type="submit" style="width:100%;justify-content:center;">Crea account</button>
        </form>
      <?php endif; ?>
    <?php endif; ?>
  </div>
</div>
</body>
</html>
