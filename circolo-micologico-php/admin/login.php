<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/functions.php';

if (is_admin()) {
    header('Location: /admin/dashboard.php');
    exit;
}

$errore = '';

// Protezione minima contro i tentativi ripetuti di login (rallenta il brute-force).
$_SESSION['tentativi_login'] = $_SESSION['tentativi_login'] ?? 0;
$_SESSION['ultimo_tentativo'] = $_SESSION['ultimo_tentativo'] ?? 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($_SESSION['tentativi_login'] >= 6 && (time() - $_SESSION['ultimo_tentativo']) < 60) {
        $errore = 'Troppi tentativi non riusciti. Riprova tra un minuto.';
    } else {
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';

        $stmt = db()->prepare('SELECT * FROM admin WHERE username = ?');
        $stmt->execute([$username]);
        $admin = $stmt->fetch();

        if ($admin && password_verify($password, $admin['password_hash'])) {
            session_regenerate_id(true);
            $_SESSION['admin_id'] = $admin['id'];
            $_SESSION['admin_username'] = $admin['username'];
            $_SESSION['tentativi_login'] = 0;
            header('Location: /admin/dashboard.php');
            exit;
        }
        $_SESSION['tentativi_login']++;
        $_SESSION['ultimo_tentativo'] = time();
        $errore = 'Nome utente o password non corretti.';
    }
}

$niAdmin = (int) db()->query('SELECT COUNT(*) FROM admin')->fetchColumn();

$pageTitle = 'Accesso riservato — ' . NOME_CIRCOLO;
require_once __DIR__ . '/../includes/header.php';
?>

<section class="admin-shell" style="max-width:440px;">
  <div class="admin-card">
    <div class="eyebrow">Accesso riservato</div>
    <h2>Area amministratore</h2>
    <p class="lead" style="margin-bottom:1.4rem;">Riservata al comitato scientifico del circolo per la gestione del database specie e degli eventi.</p>

    <?php if ($niAdmin === 0): ?>
      <div class="alert alert-err">Nessun account configurato. Esegui prima <a href="/install.php">install.php</a>.</div>
    <?php else: ?>
      <?php if ($errore): ?><div class="alert alert-err"><?= h($errore) ?></div><?php endif; ?>
      <form method="post">
        <div class="field"><label for="username">Nome utente</label><input type="text" id="username" name="username" required autofocus></div>
        <div class="field"><label for="password">Password</label><input type="password" id="password" name="password" required></div>
        <button class="btn btn-primary" type="submit" style="width:100%;justify-content:center;">Entra</button>
      </form>
    <?php endif; ?>
  </div>
</section>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
