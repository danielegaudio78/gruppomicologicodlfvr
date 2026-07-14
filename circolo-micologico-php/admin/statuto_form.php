<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/functions.php';
require_admin();

$pdo = db();
$slugs = ['statuto', 'regolamento'];
$errori = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach ($slugs as $slug) {
        $titolo = trim($_POST[$slug . '_titolo'] ?? '');
        $testo = trim($_POST[$slug . '_testo'] ?? '');
        $rimuovi_pdf = isset($_POST[$slug . '_rimuovi_pdf']);

        $stmt = $pdo->prepare('SELECT * FROM documento_sociale WHERE slug = ?');
        $stmt->execute([$slug]);
        $doc = $stmt->fetch();

        $nuovoPdf = null;
        if (!empty($_FILES[$slug . '_pdf']['name'])) {
            $f = $_FILES[$slug . '_pdf'];
            if ($f['error'] === UPLOAD_ERR_OK && $f['size'] <= MAX_PDF_MB * 1024 * 1024
                && strtolower(pathinfo($f['name'], PATHINFO_EXTENSION)) === 'pdf') {
                $finfo = finfo_open(FILEINFO_MIME_TYPE);
                $mime = finfo_file($finfo, $f['tmp_name']);
                finfo_close($finfo);
                if ($mime === 'application/pdf') {
                    $nuovoPdf = nome_file_sicuro($f['name']);
                } else {
                    $errori[] = ucfirst($slug) . ': il file caricato non è un PDF valido.';
                }
            } elseif ($f['error'] === UPLOAD_ERR_OK) {
                $errori[] = ucfirst($slug) . ': il PDF deve essere un file .pdf di massimo ' . MAX_PDF_MB . ' MB.';
            }
        }

        if (empty($errori)) {
            $set = 'titolo=?, testo=?, aggiornato_il=CURRENT_TIMESTAMP';
            $params = [$titolo, $testo];
            if ($nuovoPdf) { $set .= ', pdf_path=?'; $params[] = $nuovoPdf; }
            elseif ($rimuovi_pdf) { $set .= ', pdf_path=NULL'; }
            $params[] = $slug;
            $pdo->prepare("UPDATE documento_sociale SET $set WHERE slug=?")->execute($params);

            if ($doc) {
                if ($nuovoPdf && $doc['pdf_path']) @unlink(UPLOAD_SOCIALE_DIR . '/' . $doc['pdf_path']);
                if ($rimuovi_pdf && !$nuovoPdf && $doc['pdf_path']) @unlink(UPLOAD_SOCIALE_DIR . '/' . $doc['pdf_path']);
            }
            if ($nuovoPdf) move_uploaded_file($_FILES[$slug . '_pdf']['tmp_name'], UPLOAD_SOCIALE_DIR . '/' . $nuovoPdf);
        }
    }

    if (empty($errori)) {
        header('Location: /admin/statuto_form.php?salvato=1');
        exit;
    }
}

$stmt = $pdo->query("SELECT * FROM documento_sociale");
$documenti = [];
foreach ($stmt->fetchAll() as $r) { $documenti[$r['slug']] = $r; }

$pageTitle = 'Statuto e regolamento — ' . NOME_CIRCOLO;
require_once __DIR__ . '/../includes/header.php';

$etichette = ['statuto' => 'Statuto', 'regolamento' => 'Regolamento interno'];
?>

<div class="admin-bar">
  <span>Connesso come <b><?= h($_SESSION['admin_username']) ?></b></span>
  <a href="/admin/dashboard.php">← Torna al pannello</a>
</div>

<section class="admin-shell">
  <div class="admin-card">
    <div class="eyebrow">Chi siamo</div>
    <h2>Statuto e regolamento</h2>
    <p class="lead" style="margin-bottom:1.2rem;">Per ogni documento puoi scrivere un testo, caricare il PDF ufficiale,
    oppure entrambi: se presente, il PDF viene proposto in cima alla pagina pubblica.</p>

    <?php if (!empty($_GET['salvato'])): ?><div class="alert alert-ok">Documenti salvati correttamente.</div><?php endif; ?>
    <?php foreach ($errori as $err): ?><div class="alert alert-err"><?= h($err) ?></div><?php endforeach; ?>

    <form method="post" enctype="multipart/form-data">
      <?php foreach ($slugs as $slug): $doc = $documenti[$slug] ?? null; ?>
        <h3 style="font-size:1.1rem;margin:1.6rem 0 .6rem;"><?= h($etichette[$slug]) ?></h3>
        <div class="field"><label>Titolo</label><input type="text" name="<?= $slug ?>_titolo" value="<?= h($doc['titolo'] ?? '') ?>"></div>
        <div class="field"><label>Testo</label><textarea name="<?= $slug ?>_testo" style="min-height:140px;"><?= h($doc['testo'] ?? '') ?></textarea></div>
        <div class="field">
          <label>PDF ufficiale — opzionale</label>
          <input type="file" name="<?= $slug ?>_pdf" accept="application/pdf">
          <div class="help">Max <?= MAX_PDF_MB ?> MB. Caricandone uno nuovo, sostituisce quello attuale.</div>
          <?php if (!empty($doc['pdf_path'])): ?>
            <div class="help">PDF attuale: <a class="vedi" href="/serve_pdf.php?tipo=sociale&id=<?= (int)$doc['id'] ?>" target="_blank" rel="noopener">visualizza</a></div>
            <label style="display:flex;align-items:center;gap:.4rem;margin-top:.4rem;font-size:.85rem;font-family:var(--font-body);text-transform:none;letter-spacing:0;">
              <input type="checkbox" name="<?= $slug ?>_rimuovi_pdf" value="1" style="width:auto;"> Rimuovi il PDF attuale
            </label>
          <?php endif; ?>
        </div>
      <?php endforeach; ?>

      <button class="btn btn-primary" type="submit" style="margin-top:1rem;">Salva documenti</button>
    </form>
  </div>
</section>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
