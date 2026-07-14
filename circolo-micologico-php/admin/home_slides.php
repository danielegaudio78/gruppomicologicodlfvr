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

            if ($size > MAX_SLIDE_MB * 1024 * 1024) {
                $errori[] = "La foto \"$orig\" supera i " . MAX_SLIDE_MB . " MB consentiti.";
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
        $ordineBase = (int) $pdo->query('SELECT COALESCE(MAX(ordine), -1) FROM home_slide')->fetchColumn() + 1;
        $stmt = $pdo->prepare('INSERT INTO home_slide (path, ordine) VALUES (?, ?)');
        foreach ($fotoValide as $i => $fv) {
            move_uploaded_file($fv['tmp'], UPLOAD_SLIDE_DIR . '/' . $fv['nome']);
            $stmt->execute([$fv['nome'], $ordineBase + $i]);
        }
        header('Location: /admin/home_slides.php?salvato=1');
        exit;
    }
}

// --- Riordino (sposta su/giù) ---
if (isset($_GET['sposta'], $_GET['id'])) {
    $id = (int) $_GET['id'];
    $direzione = $_GET['sposta'] === 'su' ? 'su' : 'giu';

    $slides = $pdo->query('SELECT id, ordine FROM home_slide ORDER BY ordine, id')->fetchAll();
    $pos = null;
    foreach ($slides as $i => $s) { if ((int)$s['id'] === $id) { $pos = $i; break; } }

    if ($pos !== null) {
        $vicino = $direzione === 'su' ? $pos - 1 : $pos + 1;
        if (isset($slides[$vicino])) {
            $a = $slides[$pos]; $b = $slides[$vicino];
            $pdo->prepare('UPDATE home_slide SET ordine = ? WHERE id = ?')->execute([$b['ordine'], $a['id']]);
            $pdo->prepare('UPDATE home_slide SET ordine = ? WHERE id = ?')->execute([$a['ordine'], $b['id']]);
        }
    }
    header('Location: /admin/home_slides.php');
    exit;
}

// --- Eliminazione ---
if (isset($_GET['elimina'])) {
    $id = (int) $_GET['elimina'];
    $stmt = $pdo->prepare('SELECT * FROM home_slide WHERE id = ?');
    $stmt->execute([$id]);
    $s = $stmt->fetch();
    if ($s) {
        @unlink(UPLOAD_SLIDE_DIR . '/' . $s['path']);
        $pdo->prepare('DELETE FROM home_slide WHERE id = ?')->execute([$id]);
    }
    header('Location: /admin/home_slides.php');
    exit;
}

$slides = $pdo->query('SELECT * FROM home_slide ORDER BY ordine, id')->fetchAll();

$pageTitle = 'Carosello home — ' . NOME_CIRCOLO;
require_once __DIR__ . '/../includes/header.php';
?>

<div class="admin-bar">
  <span>Connesso come <b><?= h($_SESSION['admin_username']) ?></b></span>
  <a href="/admin/dashboard.php">← Torna al pannello</a>
</div>

<section class="admin-shell">
  <div class="admin-card">
    <div class="eyebrow">Homepage</div>
    <h2>Foto del carosello</h2>
    <p class="lead" style="margin-bottom:1.4rem;">Queste fotografie ruotano nella homepage e sono indipendenti dal
    database dei funghi: non è necessario collegarle a una specie. Vengono mostrate <b>centrate e ritagliate</b> per
    riempire tutta la larghezza dello schermo.</p>

    <?php if (!empty($_GET['salvato'])): ?><div class="alert alert-ok">Foto caricate correttamente.</div><?php endif; ?>
    <?php foreach ($errori as $err): ?><div class="alert alert-err"><?= h($err) ?></div><?php endforeach; ?>

    <form method="post" enctype="multipart/form-data">
      <div class="field">
        <label>Aggiungi fotografie (puoi selezionarne più di una insieme)</label>
        <input type="file" name="foto[]" accept="image/png, image/jpeg, image/webp" multiple required>
        <div class="help">
          Formati ammessi: JPG, PNG, WEBP · max <?= MAX_SLIDE_MB ?> MB ciascuna.<br>
          <b>Dimensioni consigliate:</b> foto orizzontali (paesaggio) almeno <b>1920×1080 px</b> (16:9),
          idealmente <b>2400×1350 px</b> per una resa nitida anche su schermi grandi; evita di superare i
          4000 px di larghezza per non appesantire il caricamento. L'immagine viene ritagliata centrata per
          riempire lo schermo: tieni il soggetto principale verso il centro della foto, non troppo vicino
          ai bordi superiore e inferiore (la fascia sotto viene scurita per il testo in sovrimpressione).
        </div>
      </div>
      <button class="btn btn-primary" type="submit">Carica nel carosello</button>
    </form>
  </div>

  <div class="admin-card">
    <h2 style="font-size:1.2rem;">Ordine attuale (<?= count($slides) ?>)</h2>
    <?php if (empty($slides)): ?>
      <p style="color:#8a8267;margin-top:.8rem;">Nessuna foto ancora caricata: il carosello in home mostrerà un messaggio di benvenuto finché non ne aggiungi almeno una.</p>
    <?php else: ?>
      <div class="thumb-row" style="margin-top:1rem;">
        <?php foreach ($slides as $i => $s): ?>
          <div style="text-align:center;">
            <div class="t" style="width:130px;height:130px;">
              <img src="/serve_slide.php?id=<?= (int)$s['id'] ?>" alt="Foto carosello <?= $i + 1 ?>">
              <a class="del" href="/admin/home_slides.php?elimina=<?= (int)$s['id'] ?>" onclick="return confirm('Eliminare questa foto dal carosello?');" title="Elimina">×</a>
            </div>
            <div style="display:flex;gap:.3rem;justify-content:center;margin-top:.4rem;">
              <a class="vedi" href="/admin/home_slides.php?sposta=su&id=<?= (int)$s['id'] ?>" title="Sposta prima">↑</a>
              <a class="vedi" href="/admin/home_slides.php?sposta=giu&id=<?= (int)$s['id'] ?>" title="Sposta dopo">↓</a>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>
</section>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
