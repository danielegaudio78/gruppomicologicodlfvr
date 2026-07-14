<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/functions.php';
require_admin();

$pdo = db();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $slugs = ['micologia_ciclo', 'micologia_nutrizione'];
    foreach ($slugs as $slug) {
        $titolo = trim($_POST[$slug . '_titolo'] ?? '');
        $contenuto = trim($_POST[$slug . '_contenuto'] ?? '');
        $stmt = $pdo->prepare('UPDATE pagine_cms SET titolo = ?, contenuto = ? WHERE slug = ?');
        $stmt->execute([$titolo, $contenuto, $slug]);
    }
    header('Location: /admin/micologia_form.php?salvato=1');
    exit;
}

$stmt = $pdo->query("SELECT * FROM pagine_cms WHERE slug IN ('micologia_ciclo','micologia_nutrizione')");
$pagine = [];
foreach ($stmt->fetchAll() as $r) { $pagine[$r['slug']] = $r; }

$pageTitle = 'Contenuti micologia — ' . NOME_CIRCOLO;
require_once __DIR__ . '/../includes/header.php';
?>

<div class="admin-bar">
  <span>Connesso come <b><?= h($_SESSION['admin_username']) ?></b></span>
  <a href="/admin/dashboard.php">← Torna al pannello</a>
</div>

<section class="admin-shell">
  <div class="admin-card">
    <div class="eyebrow">Pagina pubblica "Micologia"</div>
    <h2>Modifica i contenuti</h2>
    <p class="lead" style="margin-bottom:1.2rem;">Questi testi vengono mostrati così come sono scritti qui, uno per
    sezione: vanno a capo automaticamente, non serve alcun codice HTML.</p>

    <?php if (!empty($_GET['salvato'])): ?><div class="alert alert-ok">Contenuti salvati correttamente.</div><?php endif; ?>

    <form method="post">
      <h3 style="font-size:1.1rem;margin-bottom:.6rem;">Sezione 1</h3>
      <div class="field"><label>Titolo</label><input type="text" name="micologia_ciclo_titolo" value="<?= h($pagine['micologia_ciclo']['titolo'] ?? '') ?>"></div>
      <div class="field"><label>Testo</label><textarea name="micologia_ciclo_contenuto" style="min-height:180px;"><?= h($pagine['micologia_ciclo']['contenuto'] ?? '') ?></textarea></div>

      <h3 style="font-size:1.1rem;margin:1.6rem 0 .6rem;">Sezione 2</h3>
      <div class="field"><label>Titolo</label><input type="text" name="micologia_nutrizione_titolo" value="<?= h($pagine['micologia_nutrizione']['titolo'] ?? '') ?>"></div>
      <div class="field"><label>Testo</label><textarea name="micologia_nutrizione_contenuto" style="min-height:180px;"><?= h($pagine['micologia_nutrizione']['contenuto'] ?? '') ?></textarea></div>

      <button class="btn btn-primary" type="submit" style="margin-top:1rem;">Salva contenuti</button>
    </form>
  </div>
</section>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
