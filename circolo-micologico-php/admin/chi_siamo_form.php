<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/functions.php';
require_admin();

$pdo = db();
$errori = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $testo = trim($_POST['testo'] ?? '');
    $soci_numero = trim($_POST['soci_numero'] ?? '');
    $soci_etichetta = trim($_POST['soci_etichetta'] ?? '');
    $anni_numero = trim($_POST['anni_numero'] ?? '');
    $anni_etichetta = trim($_POST['anni_etichetta'] ?? '');
    $rimuovi_foto = isset($_POST['rimuovi_foto']);

    $stmt = $pdo->query('SELECT * FROM pagina_chi_siamo WHERE id = 1');
    $attuale = $stmt->fetch();

    $nuovaFoto = null;
    if (!empty($_FILES['foto']['name'])) {
        $f = $_FILES['foto'];
        if ($f['error'] !== UPLOAD_ERR_OK) {
            $errori[] = 'Errore nel caricamento della foto.';
        } elseif ($f['size'] > MAX_FOTO_MB * 1024 * 1024) {
            $errori[] = 'La foto supera i ' . MAX_FOTO_MB . ' MB consentiti.';
        } else {
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mime = finfo_file($finfo, $f['tmp_name']);
            finfo_close($finfo);
            $consentiti = ['image/jpeg' => 1, 'image/png' => 1, 'image/webp' => 1];
            if (!isset($consentiti[$mime])) {
                $errori[] = 'La foto deve essere in formato JPG, PNG o WEBP.';
            } else {
                $nuovaFoto = nome_file_sicuro($f['name']);
            }
        }
    }

    if (empty($errori)) {
        $set = 'testo=?, soci_numero=?, soci_etichetta=?, anni_numero=?, anni_etichetta=?';
        $params = [$testo, $soci_numero, $soci_etichetta, $anni_numero, $anni_etichetta];
        if ($nuovaFoto) { $set .= ', foto_path=?'; $params[] = $nuovaFoto; }
        elseif ($rimuovi_foto) { $set .= ', foto_path=NULL'; }

        if ($attuale) {
            $pdo->prepare("UPDATE pagina_chi_siamo SET $set WHERE id=1")->execute($params);
        } else {
            $pdo->prepare("INSERT INTO pagina_chi_siamo (id, testo, soci_numero, soci_etichetta, anni_numero, anni_etichetta, foto_path)
                VALUES (1, ?, ?, ?, ?, ?, ?)")->execute([$testo, $soci_numero, $soci_etichetta, $anni_numero, $anni_etichetta, $nuovaFoto]);
        }

        if ($attuale) {
            if ($nuovaFoto && $attuale['foto_path']) @unlink(UPLOAD_CHI_SIAMO_DIR . '/' . $attuale['foto_path']);
            if ($rimuovi_foto && !$nuovaFoto && $attuale['foto_path']) @unlink(UPLOAD_CHI_SIAMO_DIR . '/' . $attuale['foto_path']);
        }
        if ($nuovaFoto) move_uploaded_file($_FILES['foto']['tmp_name'], UPLOAD_CHI_SIAMO_DIR . '/' . $nuovaFoto);

        header('Location: /admin/chi_siamo_form.php?salvato=1');
        exit;
    }
}

$stmt = $pdo->query('SELECT * FROM pagina_chi_siamo WHERE id = 1');
$dati = $stmt->fetch();

$pageTitle = 'Chi siamo — ' . NOME_CIRCOLO;
require_once __DIR__ . '/../includes/header.php';
?>

<div class="admin-bar">
  <span>Connesso come <b><?= h($_SESSION['admin_username']) ?></b></span>
  <a href="/admin/dashboard.php">← Torna al pannello</a>
</div>

<section class="admin-shell">
  <div class="admin-card">
    <div class="eyebrow">Homepage</div>
    <h2>Sezione "Chi siamo"</h2>
    <p class="lead" style="margin-bottom:1.2rem;">Testo e fotografia mostrati nella sezione "Chi siamo" della homepage.
    Il numero di specie schedate non è modificabile qui: si aggiorna da solo in base al database funghi.</p>

    <?php if (!empty($_GET['salvato'])): ?><div class="alert alert-ok">Contenuto salvato correttamente.</div><?php endif; ?>
    <?php foreach ($errori as $err): ?><div class="alert alert-err"><?= h($err) ?></div><?php endforeach; ?>

    <form method="post" enctype="multipart/form-data">
      <div class="field full"><label>Testo di presentazione</label><textarea name="testo" style="min-height:140px;"><?= h($dati['testo'] ?? '') ?></textarea></div>

      <div class="form-grid">
        <div class="field"><label>Numero soci (es. 210+)</label><input type="text" name="soci_numero" value="<?= h($dati['soci_numero'] ?? '') ?>"></div>
        <div class="field"><label>Etichetta soci</label><input type="text" name="soci_etichetta" value="<?= h($dati['soci_etichetta'] ?? 'Soci attivi') ?>"></div>
        <div class="field"><label>Numero anni (es. 38)</label><input type="text" name="anni_numero" value="<?= h($dati['anni_numero'] ?? '') ?>"></div>
        <div class="field"><label>Etichetta anni</label><input type="text" name="anni_etichetta" value="<?= h($dati['anni_etichetta'] ?? 'Anni di attività') ?>"></div>
      </div>

      <div class="field full">
        <label>Fotografia — opzionale</label>
        <input type="file" name="foto" accept="image/jpeg, image/png, image/webp">
        <div class="help">JPG, PNG o WEBP, max <?= MAX_FOTO_MB ?> MB. Se non ne carichi una, la homepage mostra
        un'illustrazione generica al suo posto. Caricandone una nuova, sostituisce quella attuale.</div>
        <?php if (!empty($dati['foto_path'])): ?>
          <div class="thumb-row">
            <div class="t"><img src="/serve_chi_siamo_foto.php" alt=""></div>
          </div>
          <label style="display:flex;align-items:center;gap:.4rem;margin-top:.5rem;font-size:.85rem;font-family:var(--font-body);text-transform:none;letter-spacing:0;">
            <input type="checkbox" name="rimuovi_foto" value="1" style="width:auto;"> Rimuovi la foto attuale
          </label>
        <?php endif; ?>
      </div>

      <button class="btn btn-primary" type="submit">Salva</button>
    </form>
  </div>
</section>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
