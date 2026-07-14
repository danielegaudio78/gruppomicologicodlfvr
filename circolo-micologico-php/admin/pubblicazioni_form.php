<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/functions.php';
require_admin();

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
$rivista = null;
if ($id) {
    $stmt = db()->prepare('SELECT * FROM pubblicazioni WHERE id = ?');
    $stmt->execute([$id]);
    $rivista = $stmt->fetch();
    if (!$rivista) { header('Location: /admin/dashboard.php'); exit; }
}

$errori = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $titolo = trim($_POST['titolo'] ?? '');
    $data_pubblicazione = $_POST['data_pubblicazione'] ?? '';
    $descrizione = trim($_POST['descrizione'] ?? '');

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
        if ($rivista) {
            $sql = "UPDATE pubblicazioni SET titolo=?, data_pubblicazione=?, descrizione=?" .
                ($nuovoPdfPath ? ', pdf_path=?' : '') . " WHERE id=?";
            $params = [$titolo, $data_pubblicazione, $descrizione];
            if ($nuovoPdfPath) $params[] = $nuovoPdfPath;
            $params[] = $rivista['id'];
            $pdo->prepare($sql)->execute($params);
            $rivistaId = $rivista['id'];
            if ($nuovoPdfPath && $rivista['pdf_path']) {
                @unlink(UPLOAD_RIVISTE_DIR . '/' . $rivista['pdf_path']);
            }
        } else {
            $stmt = $pdo->prepare('INSERT INTO pubblicazioni (titolo, data_pubblicazione, descrizione, pdf_path) VALUES (?,?,?,?)');
            $stmt->execute([$titolo, $data_pubblicazione, $descrizione, $nuovoPdfPath]);
            $rivistaId = (int) $pdo->lastInsertId();
        }
        if ($nuovoPdfPath) {
            move_uploaded_file($_FILES['pdf']['tmp_name'], UPLOAD_RIVISTE_DIR . '/' . $nuovoPdfPath);
        }
        header('Location: /admin/pubblicazioni_form.php?id=' . $rivistaId . '&salvato=1');
        exit;
    }
}

$pageTitle = ($rivista ? 'Modifica' : 'Nuova') . ' pubblicazione — ' . NOME_CIRCOLO;
require_once __DIR__ . '/../includes/header.php';
?>

<div class="admin-bar">
  <span>Connesso come <b><?= h($_SESSION['admin_username']) ?></b></span>
  <a href="/admin/dashboard.php">← Torna al pannello</a>
</div>

<section class="admin-shell">
  <div class="admin-card">
    <div class="eyebrow"><?= $rivista ? 'Modifica pubblicazione' : 'Nuova pubblicazione' ?></div>
    <h2><?= $rivista ? h($rivista['titolo']) : 'Aggiungi una rivista' ?></h2>

    <?php if (!empty($_GET['salvato'])): ?><div class="alert alert-ok">Pubblicazione salvata correttamente.</div><?php endif; ?>
    <?php foreach ($errori as $err): ?><div class="alert alert-err"><?= h($err) ?></div><?php endforeach; ?>

    <form method="post" enctype="multipart/form-data">
      <div class="form-grid">
        <div class="field"><label>Titolo *</label><input type="text" name="titolo" value="<?= h($rivista['titolo'] ?? '') ?>" required></div>
        <div class="field"><label>Data di pubblicazione</label><input type="date" name="data_pubblicazione" value="<?= h($rivista['data_pubblicazione'] ?? '') ?>"></div>
        <div class="field full"><label>Descrizione</label><textarea name="descrizione"><?= h($rivista['descrizione'] ?? '') ?></textarea></div>
        <div class="field full">
          <label>PDF della rivista</label>
          <input type="file" name="pdf" accept="application/pdf">
          <div class="help">Max <?= MAX_PDF_MB ?> MB. Caricandone uno nuovo, sostituisce quello attuale.</div>
          <?php if (!empty($rivista['pdf_path'])): ?>
            <div class="help">PDF attuale: <a class="vedi" href="/serve_pdf.php?tipo=riviste&id=<?= (int)$rivista['id'] ?>" target="_blank" rel="noopener">visualizza</a></div>
          <?php endif; ?>
        </div>
      </div>
      <button class="btn btn-primary" type="submit"><?= $rivista ? 'Salva modifiche' : 'Crea pubblicazione' ?></button>
    </form>
  </div>
</section>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
