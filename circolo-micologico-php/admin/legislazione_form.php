<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/functions.php';
require_admin();

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
$voce = null;
if ($id) {
    $stmt = db()->prepare('SELECT * FROM legislazione WHERE id = ?');
    $stmt->execute([$id]);
    $voce = $stmt->fetch();
    if (!$voce) { header('Location: /admin/dashboard.php'); exit; }
}

$errori = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $regione = trim($_POST['regione'] ?? '');
    $titolo = trim($_POST['titolo'] ?? '');
    $testo = trim($_POST['testo'] ?? '');
    $link_esterno = trim($_POST['link_esterno'] ?? '');

    if ($regione === '') $errori[] = 'Indica la regione.';
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
        if ($voce) {
            $sql = "UPDATE legislazione SET regione=?, titolo=?, testo=?, link_esterno=?, aggiornato_il=CURRENT_TIMESTAMP" .
                ($nuovoPdfPath ? ', pdf_path=?' : '') . " WHERE id=?";
            $params = [$regione, $titolo, $testo, $link_esterno];
            if ($nuovoPdfPath) $params[] = $nuovoPdfPath;
            $params[] = $voce['id'];
            $pdo->prepare($sql)->execute($params);
            $voceId = $voce['id'];
            if ($nuovoPdfPath && $voce['pdf_path']) {
                @unlink(UPLOAD_LEGISLAZIONE_DIR . '/' . $voce['pdf_path']);
            }
        } else {
            $stmt = $pdo->prepare('INSERT INTO legislazione (regione, titolo, testo, link_esterno, pdf_path) VALUES (?,?,?,?,?)');
            $stmt->execute([$regione, $titolo, $testo, $link_esterno, $nuovoPdfPath]);
            $voceId = (int) $pdo->lastInsertId();
        }
        if ($nuovoPdfPath) {
            move_uploaded_file($_FILES['pdf']['tmp_name'], UPLOAD_LEGISLAZIONE_DIR . '/' . $nuovoPdfPath);
        }
        header('Location: /admin/legislazione_form.php?id=' . $voceId . '&salvato=1');
        exit;
    }
}

$pageTitle = ($voce ? 'Modifica' : 'Nuova') . ' scheda normativa — ' . NOME_CIRCOLO;
require_once __DIR__ . '/../includes/header.php';
?>

<div class="admin-bar">
  <span>Connesso come <b><?= h($_SESSION['admin_username']) ?></b></span>
  <a href="/admin/dashboard.php">← Torna al pannello</a>
</div>

<section class="admin-shell">
  <div class="admin-card">
    <div class="eyebrow"><?= $voce ? 'Modifica scheda' : 'Nuova scheda normativa' ?></div>
    <h2><?= $voce ? h($voce['titolo']) : 'Aggiungi una regione' ?></h2>

    <?php if (!empty($_GET['salvato'])): ?><div class="alert alert-ok">Scheda salvata correttamente.</div><?php endif; ?>
    <?php foreach ($errori as $err): ?><div class="alert alert-err"><?= h($err) ?></div><?php endforeach; ?>

    <form method="post" enctype="multipart/form-data">
      <div class="form-grid">
        <div class="field"><label>Regione *</label><input type="text" name="regione" value="<?= h($voce['regione'] ?? '') ?>" required></div>
        <div class="field"><label>Titolo *</label><input type="text" name="titolo" value="<?= h($voce['titolo'] ?? '') ?>" required></div>
        <div class="field full"><label>Testo / sintesi del regolamento</label><textarea name="testo"><?= h($voce['testo'] ?? '') ?></textarea></div>
        <div class="field full">
          <label>Link alla fonte ufficiale — opzionale</label>
          <input type="url" name="link_esterno" value="<?= h($voce['link_esterno'] ?? '') ?>" placeholder="https://...">
        </div>
        <div class="field full">
          <label>PDF del regolamento — opzionale</label>
          <input type="file" name="pdf" accept="application/pdf">
          <div class="help">Max <?= MAX_PDF_MB ?> MB. Caricandone uno nuovo, sostituisce quello attuale.</div>
          <?php if (!empty($voce['pdf_path'])): ?>
            <div class="help">PDF attuale: <a class="vedi" href="/serve_pdf.php?tipo=legislazione&id=<?= (int)$voce['id'] ?>" target="_blank" rel="noopener">visualizza</a></div>
          <?php endif; ?>
        </div>
      </div>
      <button class="btn btn-primary" type="submit"><?= $voce ? 'Salva modifiche' : 'Crea scheda' ?></button>
    </form>
  </div>
</section>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
