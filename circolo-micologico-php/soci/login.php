<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/functions.php';

if (is_socio()) {
    header('Location: /soci/bacheca.php');
    exit;
}

$errore = '';

$_SESSION['tentativi_login_socio'] = $_SESSION['tentativi_login_socio'] ?? 0;
$_SESSION['ultimo_tentativo_socio'] = $_SESSION['ultimo_tentativo_socio'] ?? 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($_SESSION['tentativi_login_socio'] >= 6 && (time() - $_SESSION['ultimo_tentativo_socio']) < 60) {
        $errore = 'Troppi tentativi non riusciti. Riprova tra un minuto.';
    } else {
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';

        $stmt = db()->prepare('SELECT * FROM soci WHERE username = ?');
        $stmt->execute([$username]);
        $socio = $stmt->fetch();

        if ($socio && password_verify($password, $socio['password_hash'])) {
            session_regenerate_id(true);
            $_SESSION['socio_id'] = $socio['id'];
            $_SESSION['socio_nome'] = $socio['nome'];
            $_SESSION['tentativi_login_socio'] = 0;
            header('Location: /soci/bacheca.php');
            exit;
        }
        $_SESSION['tentativi_login_socio']++;
        $_SESSION['ultimo_tentativo_socio'] = time();
        $errore = 'Username o password non corretti.';
    }
}

$pageTitle = 'Accesso soci — ' . NOME_CIRCOLO;
require_once __DIR__ . '/../includes/header.php';
?>

<section class="admin-shell" style="max-width:440px;">
  <div class="admin-card">
    <div class="eyebrow">Bacheca interna</div>
    <h2>Accesso soci</h2>
    <p class="lead" style="margin-bottom:1.4rem;">Riservato ai soci con un account creato dal circolo. Se non hai
    ancora le credenziali, chiedile al comitato.</p>

    <?php if ($errore): ?><div class="alert alert-err"><?= h($errore) ?></div><?php endif; ?>
    <form method="post">
      <div class="field"><label for="username">Username</label><input type="text" id="username" name="username" required autofocus></div>
      <div class="field"><label for="password">Password</label><input type="password" id="password" name="password" required></div>
      <button class="btn btn-primary" type="submit" style="width:100%;justify-content:center;">Entra</button>
    </form>
  </div>
</section>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
