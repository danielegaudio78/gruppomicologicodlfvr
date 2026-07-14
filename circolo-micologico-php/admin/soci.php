<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/functions.php';
require_admin();

$pdo = db();
$errori = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['crea'])) {
    $nome = trim($_POST['nome'] ?? '');
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($nome === '') $errori[] = 'Il nome è obbligatorio.';
    if (strlen($username) < 3) $errori[] = 'Lo username deve avere almeno 3 caratteri.';
    if (strlen($password) < 6) $errori[] = 'La password deve avere almeno 6 caratteri.';

    if (empty($errori)) {
        try {
            $stmt = $pdo->prepare('INSERT INTO soci (nome, username, password_hash) VALUES (?, ?, ?)');
            $stmt->execute([$nome, $username, password_hash($password, PASSWORD_DEFAULT)]);
            header('Location: /admin/soci.php?creato=1');
            exit;
        } catch (PDOException $e) {
            $errori[] = 'Questo username è già in uso.';
        }
    }
}

if (isset($_GET['elimina'])) {
    $pdo->prepare('DELETE FROM soci WHERE id = ?')->execute([(int) $_GET['elimina']]);
    header('Location: /admin/soci.php');
    exit;
}

$soci = $pdo->query('SELECT * FROM soci ORDER BY nome')->fetchAll();

$pageTitle = 'Gestione soci — ' . NOME_CIRCOLO;
require_once __DIR__ . '/../includes/header.php';
?>

<div class="admin-bar">
  <span>Connesso come <b><?= h($_SESSION['admin_username']) ?></b></span>
  <a href="/admin/dashboard.php">← Torna al pannello</a>
</div>

<section class="admin-shell">
  <div class="admin-card">
    <div class="eyebrow">Bacheca interna</div>
    <h2>Account soci</h2>
    <p class="lead" style="margin-bottom:1.2rem;">Crea qui un accesso per ogni socio che deve poter scrivere nella
    bacheca. Non esiste un'auto-registrazione: solo l'amministratore può creare o rimuovere un account socio.</p>

    <?php if (!empty($_GET['creato'])): ?><div class="alert alert-ok">Account socio creato correttamente.</div><?php endif; ?>
    <?php foreach ($errori as $err): ?><div class="alert alert-err"><?= h($err) ?></div><?php endforeach; ?>

    <form method="post">
      <input type="hidden" name="crea" value="1">
      <div class="form-grid">
        <div class="field"><label>Nome e cognome *</label><input type="text" name="nome" required></div>
        <div class="field"><label>Username *</label><input type="text" name="username" required></div>
        <div class="field"><label>Password iniziale *</label><input type="text" name="password" required></div>
      </div>
      <button class="btn btn-primary" type="submit">Crea account socio</button>
    </form>
  </div>

  <div class="admin-card">
    <h2 style="font-size:1.2rem;">Soci abilitati (<?= count($soci) ?>)</h2>
    <table class="admin-table">
      <thead><tr><th>Nome</th><th>Username</th><th>Creato il</th><th></th></tr></thead>
      <tbody>
        <?php foreach ($soci as $s): ?>
          <tr>
            <td><?= h($s['nome']) ?></td>
            <td><?= h($s['username']) ?></td>
            <td><?= h(substr($s['creato_il'] ?? '', 0, 10)) ?></td>
            <td><a class="vedi" style="color:#9c3b2e;border-color:#9c3b2e;" href="/admin/soci.php?elimina=<?= (int)$s['id'] ?>" onclick="return confirm('Eliminare questo account socio? Non potrà più accedere alla bacheca.');">Elimina</a></td>
          </tr>
        <?php endforeach; ?>
        <?php if (empty($soci)): ?><tr><td colspan="4" style="color:#8a8267;">Nessun account socio creato.</td></tr><?php endif; ?>
      </tbody>
    </table>
    <p class="help" style="margin-top:1rem;">Per cambiare la password di un socio, al momento occorre eliminare
    l'account e ricrearlo con una nuova password.</p>
  </div>
</section>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
