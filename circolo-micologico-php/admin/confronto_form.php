<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/functions.php';
require_admin();

$pdo = db();

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
$confronto = null;
if ($id) {
    $stmt = $pdo->prepare('SELECT * FROM confronto WHERE id = ?');
    $stmt->execute([$id]);
    $confronto = $stmt->fetch();
    if (!$confronto) { header('Location: /admin/dashboard.php'); exit; }
}

$tutteLeSpecie = $pdo->query('SELECT id, nome_comune, nome_scientifico FROM specie ORDER BY nome_comune')->fetchAll();
$errori = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $titolo = trim($_POST['titolo'] ?? '');
    $specie_buona_id = filter_input(INPUT_POST, 'specie_buona_id', FILTER_VALIDATE_INT);
    $specie_cattiva_id = filter_input(INPUT_POST, 'specie_cattiva_id', FILTER_VALIDATE_INT);
    $note_confronto = trim($_POST['note_confronto'] ?? '');

    if (!$specie_buona_id || !$specie_cattiva_id) {
        $errori[] = 'Seleziona entrambe le specie da confrontare.';
    } elseif ($specie_buona_id === $specie_cattiva_id) {
        $errori[] = 'Le due specie del confronto devono essere diverse.';
    }

    if (count($tutteLeSpecie) < 2) {
        $errori[] = 'Servono almeno due specie nel database funghi prima di poter creare un confronto.';
    }

    if (empty($errori)) {
        if ($confronto) {
            $pdo->prepare('UPDATE confronto SET titolo=?, specie_buona_id=?, specie_cattiva_id=?, note_confronto=? WHERE id=?')
                ->execute([$titolo, $specie_buona_id, $specie_cattiva_id, $note_confronto, $confronto['id']]);
        } else {
            $ordine = (int) $pdo->query('SELECT COALESCE(MAX(ordine), -1) FROM confronto')->fetchColumn() + 1;
            $pdo->prepare('INSERT INTO confronto (titolo, specie_buona_id, specie_cattiva_id, note_confronto, ordine) VALUES (?,?,?,?,?)')
                ->execute([$titolo, $specie_buona_id, $specie_cattiva_id, $note_confronto, $ordine]);
        }
        header('Location: /admin/dashboard.php');
        exit;
    }
}

$pageTitle = ($confronto ? 'Modifica' : 'Nuovo') . ' confronto — ' . NOME_CIRCOLO;
require_once __DIR__ . '/../includes/header.php';
?>

<div class="admin-bar">
  <span>Connesso come <b><?= h($_SESSION['admin_username']) ?></b></span>
  <a href="/admin/dashboard.php">← Torna al pannello</a>
</div>

<section class="admin-shell">
  <div class="admin-card">
    <div class="eyebrow">Fungo buono / fungo cattivo</div>
    <h2><?= $confronto ? h($confronto['titolo'] ?: 'Modifica confronto') : 'Nuovo confronto' ?></h2>
    <p class="lead" style="margin-bottom:1.2rem;">Metti a confronto due schede già presenti nel database funghi —
    tipicamente una specie commestibile e un suo "sosia" tossico — per aiutare i soci a riconoscere le differenze.
    Foto e dati tecnici arrivano automaticamente dalle schede specie: qui scegli solo quali confrontare e perché.</p>

    <?php foreach ($errori as $err): ?><div class="alert alert-err"><?= h($err) ?></div><?php endforeach; ?>

    <?php if (count($tutteLeSpecie) < 2): ?>
      <div class="alert alert-err">Devi prima inserire almeno due specie in <a href="/admin/specie_form.php">Database specie</a>.</div>
    <?php else: ?>
      <form method="post">
        <div class="field full">
          <label>Titolo del confronto (facoltativo)</label>
          <input type="text" name="titolo" value="<?= h($confronto['titolo'] ?? '') ?>" placeholder="Es. Porcino vs Fungo del diavolo">
        </div>

        <div class="form-grid">
          <div class="field">
            <label>Fungo buono (commestibile)</label>
            <select name="specie_buona_id" required>
              <option value="">— seleziona —</option>
              <?php foreach ($tutteLeSpecie as $s): ?>
                <option value="<?= (int)$s['id'] ?>" <?= (int)($confronto['specie_buona_id'] ?? 0) === (int)$s['id'] ? 'selected' : '' ?>>
                  <?= h($s['nome_comune']) ?> (<?= h($s['nome_scientifico']) ?>)
                </option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="field">
            <label>Fungo cattivo (tossico / sosia pericoloso)</label>
            <select name="specie_cattiva_id" required>
              <option value="">— seleziona —</option>
              <?php foreach ($tutteLeSpecie as $s): ?>
                <option value="<?= (int)$s['id'] ?>" <?= (int)($confronto['specie_cattiva_id'] ?? 0) === (int)$s['id'] ? 'selected' : '' ?>>
                  <?= h($s['nome_comune']) ?> (<?= h($s['nome_scientifico']) ?>)
                </option>
              <?php endforeach; ?>
            </select>
          </div>
        </div>

        <div class="field full">
          <label>Come distinguerli</label>
          <textarea name="note_confronto" style="min-height:140px;" placeholder="Es. Il gambo del Porcino è reticolato, quello del sosia no. Le spore..."><?= h($confronto['note_confronto'] ?? '') ?></textarea>
          <div class="help">Questo testo compare al centro della pagina di confronto pubblica, tra le due schede.</div>
        </div>

        <button class="btn btn-primary" type="submit"><?= $confronto ? 'Salva modifiche' : 'Crea confronto' ?></button>
      </form>
    <?php endif; ?>
  </div>
</section>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
