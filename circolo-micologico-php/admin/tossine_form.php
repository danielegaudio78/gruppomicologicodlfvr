<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/functions.php';
require_admin();

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
$voce = null;
if ($id) {
    $stmt = db()->prepare('SELECT * FROM tossine WHERE id = ?');
    $stmt->execute([$id]);
    $voce = $stmt->fetch();
    if (!$voce) { header('Location: /admin/dashboard.php'); exit; }
}

$errori = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome_sindrome = trim($_POST['nome_sindrome'] ?? '');
    $tempo_latenza = trim($_POST['tempo_latenza'] ?? '');
    $funghi_coinvolti = trim($_POST['funghi_coinvolti'] ?? '');
    $sintomi = trim($_POST['sintomi'] ?? '');
    $gravita = trim($_POST['gravita'] ?? '');
    $note = trim($_POST['note'] ?? '');

    if ($nome_sindrome === '') $errori[] = 'Il nome della sindrome è obbligatorio.';

    $nuovoPdfPath = null;
    if (!empty($_FILES['pdf']['name'])) {
        $f = $_FILES['pdf'];
        if ($f['error'] !== UPLOAD_ERR_OK) {
            $errori[] = 'Errore nel caricamento del PDF.';
        } elseif (strtolower(pathinfo($f['name'], PATHINFO_EXTENSION)) !== 'pdf') {
            $errori[] = 'Il file deve essere un PDF.';
        } elseif ($f['size'] > MAX_PDF_MB * 1024 * 1024) {
            $errori[] = 'Il PDF supera i ' . MAX_PDF_MB . ' MB consentiti.';
        } else {
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mime = finfo_file($finfo, $f['tmp_name']);
            finfo_close($finfo);
            if ($mime !== 'application/pdf') {
                $errori[] = 'Il file caricato non è un PDF valido.';
            } else {
                $nuovoPdfPath = nome_file_sicuro($f['name']);
            }
        }
    }

    if (empty($errori)) {
        $pdo = db();
        if ($voce) {
            $sql = "UPDATE tossine SET nome_sindrome=?, tempo_latenza=?, funghi_coinvolti=?, sintomi=?, gravita=?, note=?" .
                ($nuovoPdfPath ? ', pdf_path=?' : '') . " WHERE id=?";
            $params = [$nome_sindrome, $tempo_latenza, $funghi_coinvolti, $sintomi, $gravita, $note];
            if ($nuovoPdfPath) $params[] = $nuovoPdfPath;
            $params[] = $voce['id'];
            $pdo->prepare($sql)->execute($params);
            $voceId = $voce['id'];
            if ($nuovoPdfPath && $voce['pdf_path']) {
                @unlink(UPLOAD_TOSSINE_DIR . '/' . $voce['pdf_path']);
            }
        } else {
            $stmt = $pdo->prepare('INSERT INTO tossine (nome_sindrome, tempo_latenza, funghi_coinvolti, sintomi, gravita, note, pdf_path) VALUES (?,?,?,?,?,?,?)');
            $stmt->execute([$nome_sindrome, $tempo_latenza, $funghi_coinvolti, $sintomi, $gravita, $note, $nuovoPdfPath]);
            $voceId = (int) $pdo->lastInsertId();
        }
        if ($nuovoPdfPath) {
            move_uploaded_file($_FILES['pdf']['tmp_name'], UPLOAD_TOSSINE_DIR . '/' . $nuovoPdfPath);
        }
        header('Location: /admin/tossine_form.php?id=' . $voceId . '&salvato=1');
        exit;
    }
}

$pageTitle = ($voce ? 'Modifica' : 'Nuova') . ' sindrome — ' . NOME_CIRCOLO;
require_once __DIR__ . '/../includes/header.php';
?>

<div class="admin-bar">
  <span>Connesso come <b><?= h($_SESSION['admin_username']) ?></b></span>
  <a href="/admin/dashboard.php">← Torna al pannello</a>
</div>

<section class="admin-shell">
  <div class="admin-card">
    <div class="eyebrow"><?= $voce ? 'Modifica scheda' : 'Nuova scheda tossicologica' ?></div>
    <h2><?= $voce ? h($voce['nome_sindrome']) : 'Aggiungi una sindrome' ?></h2>

    <?php if (!empty($_GET['salvato'])): ?><div class="alert alert-ok">Scheda salvata correttamente.</div><?php endif; ?>
    <?php foreach ($errori as $err): ?><div class="alert alert-err"><?= h($err) ?></div><?php endforeach; ?>

    <form method="post" enctype="multipart/form-data">
      <div class="form-grid">
        <div class="field"><label>Nome sindrome *</label><input type="text" name="nome_sindrome" value="<?= h($voce['nome_sindrome'] ?? '') ?>" required></div>
        <div class="field"><label>Gravità</label><input type="text" name="gravita" value="<?= h($voce['gravita'] ?? '') ?>" placeholder="Es. Molto grave, può essere mortale"></div>
        <div class="field"><label>Tempo di latenza</label><input type="text" name="tempo_latenza" value="<?= h($voce['tempo_latenza'] ?? '') ?>" placeholder="Es. 6-24 ore"></div>
        <div class="field"><label>Funghi coinvolti</label><input type="text" name="funghi_coinvolti" value="<?= h($voce['funghi_coinvolti'] ?? '') ?>"></div>
        <div class="field full"><label>Sintomi</label><textarea name="sintomi"><?= h($voce['sintomi'] ?? '') ?></textarea></div>
        <div class="field full"><label>Note</label><textarea name="note"><?= h($voce['note'] ?? '') ?></textarea></div>
        <div class="field full">
          <label>PDF della scheda — opzionale</label>
          <input type="file" name="pdf" accept="application/pdf">
          <div class="help">Max <?= MAX_PDF_MB ?> MB. Caricandone uno nuovo, sostituisce quello attuale.</div>
          <?php if (!empty($voce['pdf_path'])): ?>
            <div class="help">PDF attuale: <a class="vedi" href="/serve_pdf.php?tipo=tossine&id=<?= (int)$voce['id'] ?>" target="_blank" rel="noopener">visualizza</a></div>
          <?php endif; ?>
        </div>
      </div>
      <button class="btn btn-primary" type="submit"><?= $voce ? 'Salva modifiche' : 'Crea scheda' ?></button>
    </form>
  </div>
</section>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
