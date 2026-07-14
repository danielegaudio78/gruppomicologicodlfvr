<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/functions.php';
require_admin();

$pdo = db();
$errori = [];

// --- Upload di nuove foto ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['foto'])) {
    $fotoValide = [];
    if (!empty($_FILES['foto']['name'][0])) {
        $n = count($_FILES['foto']['name']);
        for ($i = 0; $i < $n; $i++) {
            if ($_FILES['foto']['error'][$i] !== UPLOAD_ERR_OK) continue;
            $tmp = $_FILES['foto']['tmp_name'][$i];
            $orig = $_FILES['foto']['name'][$i];
            $size = $_FILES['foto']['size'][$i];

            if ($size > MAX_FOTO_MB * 1024 * 1024) {
                $errori[] = "La foto \"$orig\" supera i " . MAX_FOTO_MB . " MB consentiti.";
                continue;
            }
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mime = finfo_file($finfo, $tmp);
            finfo_close($finfo);
            $consentiti = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
            if (!isset($consentiti[$mime])) {
                $errori[] = "\"$orig\" non è un'immagine valida (sono ammessi JPG, PNG, WEBP).";
                continue;
            }
            $fotoValide[] = ['tmp' => $tmp, 'nome' => nome_file_sicuro($orig)];
        }
    } else {
        $errori[] = 'Seleziona almeno una fotografia da caricare.';
    }

    if (empty($errori) && !empty($fotoValide)) {
        $ordineBase = (int) $pdo->query('SELECT COALESCE(MAX(ordine), -1) FROM galleria_soci')->fetchColumn() + 1;
        $stmt = $pdo->prepare('INSERT INTO galleria_soci (path, ordine) VALUES (?, ?)');
        foreach ($fotoValide as $i => $fv) {
            move_uploaded_file($fv['tmp'], UPLOAD_GALLERIA_SOCI_DIR . '/' . $fv['nome']);
            $stmt->execute([$fv['nome'], $ordineBase + $i]);
        }
        header('Location: /admin/galleria_soci.php?salvato=1');
        exit;
    }
}

// --- Riordino (sposta su/giù) ---
if (isset($_GET['sposta'], $_GET['id'])) {
    $id = (int) $_GET['id'];
    $direzione = $_GET['sposta'] === 'su' ? 'su' : 'giu';

    $foto = $pdo->query('SELECT id, ordine FROM galleria_soci ORDER BY ordine, id')->fetchAll();
    $pos = null;
    foreach ($foto as $i => $f) { if ((int)$f['id'] === $id) { $pos = $i; break; } }

    if ($pos !== null) {
        $vicino = $direzione === 'su' ? $pos - 1 : $pos + 1;
        if (isset($foto[$vicino])) {
            $a = $foto[$pos]; $b = $foto[$vicino];
            $pdo->prepare('UPDATE galleria_soci SET ordine = ? WHERE id = ?')->execute([$b['ordine'], $a['id']]);
            $pdo->prepare('UPDATE galleria_soci SET ordine = ? WHERE id = ?')->execute([$a['ordine'], $b['id']]);
        }
    }
    header('Location: /admin/galleria_soci.php');
    exit;
}

// --- Eliminazione ---
if (isset($_GET['elimina'])) {
    $id = (int) $_GET['elimina'];
    $stmt = $pdo->prepare('SELECT * FROM galleria_soci WHERE id = ?');
    $stmt->execute([$id]);
    $f = $stmt->fetch();
    if ($f) {
        @unlink(UPLOAD_GALLERIA_SOCI_DIR . '/' . $f['path']);
        $pdo->prepare('DELETE FROM galleria_soci WHERE id = ?')->execute([$id]);
    }
    header('Location: /admin/galleria_soci.php');
    exit;
}

$foto = $pdo->query('SELECT * FROM galleria_soci ORDER BY ordine, id')->fetchAll();

$pageTitle = 'Galleria soci — ' . NOME_CIRCOLO;
require_once __DIR__ . '/../includes/header.php';
?>

<div class="admin-bar">
  <span>Connesso come <b><?= h($_SESSION['admin_username']) ?></b></span>
  <a href="/admin/dashboard.php">← Torna al pannello</a>
</div>

<section class="admin-shell">
  <div class="admin-card">
    <div class="eyebrow">Riservata ai soci</div>
    <h2>Galleria fotografica soci</h2>
    <p class="lead" style="margin-bottom:1.4rem;">Queste fotografie sono visibili <b>solo ai soci collegati</b>
    (in <code>/soci/galleria.php</code>): non compaiono in nessuna pagina pubblica del sito, e non sono
    raggiungibili nemmeno conoscendo l'indirizzo diretto della foto senza aver eseguito l'accesso.</p>

    <?php if (!empty($_GET['salvato'])): ?><div class="alert alert-ok">Foto caricate correttamente.</div><?php endif; ?>
    <?php foreach ($errori as $err): ?><div class="alert alert-err"><?= h($err) ?></div><?php endforeach; ?>

    <form method="post" enctype="multipart/form-data">
      <div class="field">
        <label>Aggiungi fotografie (puoi selezionarne più di una insieme)</label>
        <input type="file" name="foto[]" accept="image/png, image/jpeg, image/webp" multiple required>
        <div class="help">Formati ammessi: JPG, PNG, WEBP · max <?= MAX_FOTO_MB ?> MB ciascuna.</div>
      </div>
      <button class="btn btn-primary" type="submit">Carica nella galleria soci</button>
    </form>
  </div>

  <div class="admin-card">
    <h2 style="font-size:1.2rem;">Foto attuali (<?= count($foto) ?>)</h2>
    <?php if (empty($foto)): ?>
      <p style="color:#8a8267;margin-top:.8rem;">Nessuna foto ancora caricata.</p>
    <?php else: ?>
      <div class="thumb-row" style="margin-top:1rem;">
        <?php foreach ($foto as $i => $f): ?>
          <div style="text-align:center;">
            <div class="t" style="width:130px;height:130px;">
              <img src="/serve_galleria_soci_foto.php?id=<?= (int)$f['id'] ?>" alt="Foto <?= $i + 1 ?>">
              <a class="del" href="/admin/galleria_soci.php?elimina=<?= (int)$f['id'] ?>" onclick="return confirm('Eliminare questa foto dalla galleria soci?');" title="Elimina">×</a>
            </div>
            <div style="display:flex;gap:.3rem;justify-content:center;margin-top:.4rem;">
              <a class="vedi" href="/admin/galleria_soci.php?sposta=su&id=<?= (int)$f['id'] ?>" title="Sposta prima">↑</a>
              <a class="vedi" href="/admin/galleria_soci.php?sposta=giu&id=<?= (int)$f['id'] ?>" title="Sposta dopo">↓</a>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>
</section>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
