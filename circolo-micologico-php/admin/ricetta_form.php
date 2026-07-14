<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/functions.php';
require_admin();

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
$ricetta = null;
if ($id) {
    $stmt = db()->prepare('SELECT * FROM ricetta WHERE id = ?');
    $stmt->execute([$id]);
    $ricetta = $stmt->fetch();
    if (!$ricetta) { header('Location: /admin/dashboard.php'); exit; }
}

$errori = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $titolo = trim($_POST['titolo'] ?? '');
    $categoria = trim($_POST['categoria'] ?? '');
    $funghi_utilizzati = trim($_POST['funghi_utilizzati'] ?? '');
    $tempo_preparazione = trim($_POST['tempo_preparazione'] ?? '');
    $difficolta = trim($_POST['difficolta'] ?? '');
    $ingredienti = trim($_POST['ingredienti'] ?? '');
    $procedimento = trim($_POST['procedimento'] ?? '');
    $abbinamento_vino = trim($_POST['abbinamento_vino'] ?? '');
    $rimuovi_foto = isset($_POST['rimuovi_foto']);

    if ($titolo === '') $errori[] = 'Il titolo della ricetta è obbligatorio.';

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
        $pdo = db();
        if ($ricetta) {
            $set = 'titolo=?, categoria=?, funghi_utilizzati=?, tempo_preparazione=?, difficolta=?, ingredienti=?, procedimento=?, abbinamento_vino=?';
            $params = [$titolo, $categoria, $funghi_utilizzati, $tempo_preparazione, $difficolta, $ingredienti, $procedimento, $abbinamento_vino];
            if ($nuovaFoto) { $set .= ', foto_path=?'; $params[] = $nuovaFoto; }
            elseif ($rimuovi_foto) { $set .= ', foto_path=NULL'; }
            $params[] = $ricetta['id'];
            $pdo->prepare("UPDATE ricetta SET $set WHERE id=?")->execute($params);
            $ricettaId = $ricetta['id'];

            if ($nuovaFoto && $ricetta['foto_path']) @unlink(UPLOAD_RICETTE_DIR . '/' . $ricetta['foto_path']);
            if ($rimuovi_foto && !$nuovaFoto && $ricetta['foto_path']) @unlink(UPLOAD_RICETTE_DIR . '/' . $ricetta['foto_path']);
        } else {
            $ordine = (int) $pdo->query('SELECT COALESCE(MAX(ordine), -1) FROM ricetta')->fetchColumn() + 1;
            $stmt = $pdo->prepare("INSERT INTO ricetta
                (titolo, categoria, funghi_utilizzati, tempo_preparazione, difficolta, ingredienti, procedimento, abbinamento_vino, foto_path, ordine)
                VALUES (?,?,?,?,?,?,?,?,?,?)");
            $stmt->execute([$titolo, $categoria, $funghi_utilizzati, $tempo_preparazione, $difficolta, $ingredienti, $procedimento, $abbinamento_vino, $nuovaFoto, $ordine]);
            $ricettaId = (int) $pdo->lastInsertId();
        }
        if ($nuovaFoto) move_uploaded_file($_FILES['foto']['tmp_name'], UPLOAD_RICETTE_DIR . '/' . $nuovaFoto);

        header('Location: /admin/ricetta_form.php?id=' . $ricettaId . '&salvato=1');
        exit;
    }
}

$pageTitle = ($ricetta ? 'Modifica' : 'Nuova') . ' ricetta — ' . NOME_CIRCOLO;
require_once __DIR__ . '/../includes/header.php';
?>

<div class="admin-bar">
  <span>Connesso come <b><?= h($_SESSION['admin_username']) ?></b></span>
  <a href="/admin/dashboard.php">← Torna al pannello</a>
</div>

<section class="admin-shell">
  <div class="admin-card">
    <div class="eyebrow">In cucina</div>
    <h2><?= $ricetta ? h($ricetta['titolo']) : 'Aggiungi una ricetta' ?></h2>

    <?php if (!empty($_GET['salvato'])): ?><div class="alert alert-ok">Ricetta salvata correttamente.</div><?php endif; ?>
    <?php foreach ($errori as $err): ?><div class="alert alert-err"><?= h($err) ?></div><?php endforeach; ?>

    <form method="post" enctype="multipart/form-data">
      <div class="form-grid">
        <div class="field"><label>Titolo *</label><input type="text" name="titolo" value="<?= h($ricetta['titolo'] ?? '') ?>" required></div>
        <div class="field"><label>Categoria (es. Antipasto, Primo, Secondo, Conserva)</label><input type="text" name="categoria" value="<?= h($ricetta['categoria'] ?? '') ?>"></div>
        <div class="field"><label>Funghi utilizzati</label><input type="text" name="funghi_utilizzati" value="<?= h($ricetta['funghi_utilizzati'] ?? '') ?>" placeholder="Es. Porcino, Finferlo"></div>
        <div class="field"><label>Tempo di preparazione</label><input type="text" name="tempo_preparazione" value="<?= h($ricetta['tempo_preparazione'] ?? '') ?>" placeholder="Es. 35 minuti"></div>
        <div class="field">
          <label>Difficoltà</label>
          <select name="difficolta">
            <?php $curDiff = $ricetta['difficolta'] ?? ''; ?>
            <option value="" <?= $curDiff === '' ? 'selected' : '' ?>>Non indicata</option>
            <option value="Facile" <?= $curDiff === 'Facile' ? 'selected' : '' ?>>Facile</option>
            <option value="Media" <?= $curDiff === 'Media' ? 'selected' : '' ?>>Media</option>
            <option value="Difficile" <?= $curDiff === 'Difficile' ? 'selected' : '' ?>>Difficile</option>
          </select>
        </div>
        <div class="field full"><label>Ingredienti (uno per riga)</label><textarea name="ingredienti" style="min-height:120px;"><?= h($ricetta['ingredienti'] ?? '') ?></textarea></div>
        <div class="field full"><label>Procedimento</label><textarea name="procedimento" style="min-height:160px;"><?= h($ricetta['procedimento'] ?? '') ?></textarea></div>
        <div class="field full"><label>Abbinamento vino</label><textarea name="abbinamento_vino" placeholder="Es. Un bianco strutturato come un Verdicchio dei Castelli di Jesi Riserva"><?= h($ricetta['abbinamento_vino'] ?? '') ?></textarea></div>

        <div class="field full">
          <label>Fotografia — opzionale</label>
          <input type="file" name="foto" accept="image/jpeg, image/png, image/webp">
          <div class="help">JPG, PNG o WEBP, max <?= MAX_FOTO_MB ?> MB. Caricandone una nuova, sostituisce quella attuale.</div>
          <?php if (!empty($ricetta['foto_path'])): ?>
            <div class="thumb-row">
              <div class="t"><img src="/serve_ricetta_foto.php?id=<?= (int)$ricetta['id'] ?>" alt=""></div>
            </div>
            <label style="display:flex;align-items:center;gap:.4rem;margin-top:.5rem;font-size:.85rem;font-family:var(--font-body);text-transform:none;letter-spacing:0;">
              <input type="checkbox" name="rimuovi_foto" value="1" style="width:auto;"> Rimuovi la foto attuale
            </label>
          <?php endif; ?>
        </div>
      </div>
      <button class="btn btn-primary" type="submit"><?= $ricetta ? 'Salva modifiche' : 'Crea ricetta' ?></button>
    </form>
  </div>
</section>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
