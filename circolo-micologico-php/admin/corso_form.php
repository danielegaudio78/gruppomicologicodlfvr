<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/functions.php';
require_admin();

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
$lezione = null;
if ($id) {
    $stmt = db()->prepare('SELECT * FROM lezioni_corso WHERE id = ?');
    $stmt->execute([$id]);
    $lezione = $stmt->fetch();
    if (!$lezione) { header('Location: /admin/dashboard.php'); exit; }
}

$errori = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $titolo = trim($_POST['titolo'] ?? '');
    $data_lezione = $_POST['data_lezione'] ?? '';
    $descrizione = trim($_POST['descrizione'] ?? '');
    $link_esterno = trim($_POST['link_esterno'] ?? '');

    if ($titolo === '') $errori[] = 'Il titolo è obbligatorio.';

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
        if ($lezione) {
            $sql = "UPDATE lezioni_corso SET titolo=?, data_lezione=?, descrizione=?, link_esterno=?" .
                ($nuovoPdfPath ? ', pdf_path=?' : '') . " WHERE id=?";
            $params = [$titolo, $data_lezione, $descrizione, $link_esterno];
            if ($nuovoPdfPath) $params[] = $nuovoPdfPath;
            $params[] = $lezione['id'];
            $pdo->prepare($sql)->execute($params);
            $lezioneId = $lezione['id'];
            if ($nuovoPdfPath && $lezione['pdf_path']) {
                @unlink(UPLOAD_CORSO_DIR . '/' . $lezione['pdf_path']);
            }
        } else {
            $ordine = (int) $pdo->query('SELECT COALESCE(MAX(ordine), -1) FROM lezioni_corso')->fetchColumn() + 1;
            $stmt = $pdo->prepare('INSERT INTO lezioni_corso (titolo, data_lezione, descrizione, link_esterno, pdf_path, ordine) VALUES (?,?,?,?,?,?)');
            $stmt->execute([$titolo, $data_lezione, $descrizione, $link_esterno, $nuovoPdfPath, $ordine]);
            $lezioneId = (int) $pdo->lastInsertId();
        }
        if ($nuovoPdfPath) {
            move_uploaded_file($_FILES['pdf']['tmp_name'], UPLOAD_CORSO_DIR . '/' . $nuovoPdfPath);
        }
        header('Location: /admin/corso_form.php?id=' . $lezioneId . '&salvato=1');
        exit;
    }
}

$pageTitle = ($lezione ? 'Modifica' : 'Nuova') . ' lezione — ' . NOME_CIRCOLO;
require_once __DIR__ . '/../includes/header.php';
?>

<div class="admin-bar">
  <span>Connesso come <b><?= h($_SESSION['admin_username']) ?></b></span>
  <a href="/admin/dashboard.php">← Torna al pannello</a>
</div>

<section class="admin-shell">
  <div class="admin-card">
    <div class="eyebrow"><?= $lezione ? 'Modifica lezione' : 'Nuova lezione' ?></div>
    <h2><?= $lezione ? h($lezione['titolo']) : 'Aggiungi una lezione al corso' ?></h2>

    <?php if (!empty($_GET['salvato'])): ?><div class="alert alert-ok">Lezione salvata correttamente.</div><?php endif; ?>
    <?php foreach ($errori as $err): ?><div class="alert alert-err"><?= h($err) ?></div><?php endforeach; ?>

    <form method="post" enctype="multipart/form-data">
      <div class="form-grid">
        <div class="field"><label>Titolo *</label><input type="text" name="titolo" value="<?= h($lezione['titolo'] ?? '') ?>" required></div>
        <div class="field"><label>Data della lezione</label><input type="date" name="data_lezione" value="<?= h($lezione['data_lezione'] ?? '') ?>"></div>
        <div class="field full"><label>Descrizione</label><textarea name="descrizione"><?= h($lezione['descrizione'] ?? '') ?></textarea></div>
        <div class="field full">
          <label>Link SharePoint (o altra cartella condivisa) — opzionale</label>
          <input type="url" name="link_esterno" value="<?= h($lezione['link_esterno'] ?? '') ?>" placeholder="https://...">
        </div>
        <div class="field full">
          <label>PDF della lezione — opzionale</label>
          <input type="file" name="pdf" accept="application/pdf">
          <div class="help">Max <?= MAX_PDF_MB ?> MB. Caricandone uno nuovo, sostituisce quello attuale.</div>
          <?php if (!empty($lezione['pdf_path'])): ?>
            <div class="help">PDF attuale: <a class="vedi" href="/serve_pdf.php?tipo=corso&id=<?= (int)$lezione['id'] ?>" target="_blank" rel="noopener">visualizza</a></div>
          <?php endif; ?>
        </div>
      </div>
      <button class="btn btn-primary" type="submit"><?= $lezione ? 'Salva modifiche' : 'Crea lezione' ?></button>
    </form>
  </div>
</section>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
