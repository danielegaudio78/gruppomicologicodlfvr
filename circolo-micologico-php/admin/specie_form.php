<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/functions.php';
require_admin();

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
$specie = null;
$foto = [];

if ($id) {
    $stmt = db()->prepare('SELECT * FROM specie WHERE id = ?');
    $stmt->execute([$id]);
    $specie = $stmt->fetch();
    if (!$specie) { header('Location: /admin/dashboard.php'); exit; }

    $stmt = db()->prepare('SELECT * FROM foto WHERE specie_id = ? ORDER BY ordine, id');
    $stmt->execute([$id]);
    $foto = $stmt->fetchAll();
}

$errori = [];
$successo = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $dati = [
        'nome_comune'      => trim($_POST['nome_comune'] ?? ''),
        'nome_scientifico' => trim($_POST['nome_scientifico'] ?? ''),
        'nomi_alternativi' => trim($_POST['nomi_alternativi'] ?? ''),
        'commestibilita'   => $_POST['commestibilita'] ?? 'verifica',
        'gruppo'           => trim($_POST['gruppo'] ?? ''),
        'habitat'          => trim($_POST['habitat'] ?? ''),
        'periodo'          => trim($_POST['periodo'] ?? ''),
        'cappello'         => trim($_POST['cappello'] ?? ''),
        'gambo'            => trim($_POST['gambo'] ?? ''),
        'imenio'           => trim($_POST['imenio'] ?? ''),
        'spore'            => trim($_POST['spore'] ?? ''),
        'note'             => trim($_POST['note'] ?? ''),
        'in_evidenza'      => isset($_POST['in_evidenza']) ? 1 : 0,
    ];

    if ($dati['nome_comune'] === '') $errori[] = 'Il nome comune è obbligatorio.';
    if ($dati['nome_scientifico'] === '') $errori[] = 'Il nome scientifico è obbligatorio.';
    if (!in_array($dati['commestibilita'], ['si', 'no', 'verifica'], true)) $errori[] = 'Commestibilità non valida.';
    if ($dati['gruppo'] !== '' && !in_array($dati['gruppo'], gruppi_tassonomici(), true)) $errori[] = 'Gruppo tassonomico non valido.';

    // --- Validazione PDF (opzionale) ---
    $nuovoPdfPath = null;
    if (!empty($_FILES['pdf']['name'])) {
        $f = $_FILES['pdf'];
        if ($f['error'] !== UPLOAD_ERR_OK) {
            $errori[] = 'Errore nel caricamento del PDF.';
        } elseif (strtolower(pathinfo($f['name'], PATHINFO_EXTENSION)) !== 'pdf') {
            $errori[] = 'Il file della scheda tecnica deve essere un PDF.';
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

    // --- Validazione foto multiple (opzionali) ---
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
    }

    if (empty($errori)) {
        $pdo = db();
        $pdo->beginTransaction();
        try {
            if ($specie) {
                $sql = "UPDATE specie SET nome_comune=?, nome_scientifico=?, nomi_alternativi=?, commestibilita=?, gruppo=?, habitat=?, periodo=?,
                        cappello=?, gambo=?, imenio=?, spore=?, note=?, in_evidenza=?" .
                        ($nuovoPdfPath ? ', pdf_path=?' : '') . " WHERE id=?";
                $params = [$dati['nome_comune'], $dati['nome_scientifico'], $dati['nomi_alternativi'], $dati['commestibilita'], $dati['gruppo'], $dati['habitat'],
                    $dati['periodo'], $dati['cappello'], $dati['gambo'], $dati['imenio'], $dati['spore'], $dati['note'], $dati['in_evidenza']];
                if ($nuovoPdfPath) $params[] = $nuovoPdfPath;
                $params[] = $specie['id'];
                $pdo->prepare($sql)->execute($params);
                $specieId = $specie['id'];

                // Rimuovo il vecchio PDF solo dopo che il nuovo è stato registrato con successo.
                if ($nuovoPdfPath && $specie['pdf_path']) {
                    @unlink(UPLOAD_PDF_DIR . '/' . $specie['pdf_path']);
                }
            } else {
                $stmt = $pdo->prepare("INSERT INTO specie
                    (nome_comune, nome_scientifico, nomi_alternativi, commestibilita, gruppo, habitat, periodo, cappello, gambo, imenio, spore, note, pdf_path, in_evidenza)
                    VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?)");
                $stmt->execute([$dati['nome_comune'], $dati['nome_scientifico'], $dati['nomi_alternativi'], $dati['commestibilita'], $dati['gruppo'], $dati['habitat'],
                    $dati['periodo'], $dati['cappello'], $dati['gambo'], $dati['imenio'], $dati['spore'], $dati['note'],
                    $nuovoPdfPath, $dati['in_evidenza']]);
                $specieId = (int) $pdo->lastInsertId();
            }

            // Sposto i file solo ORA che il record è stato scritto correttamente nel database.
            if ($nuovoPdfPath) {
                move_uploaded_file($_FILES['pdf']['tmp_name'], UPLOAD_PDF_DIR . '/' . $nuovoPdfPath);
            }
            $stmtFoto = $pdo->prepare('INSERT INTO foto (specie_id, path, ordine) VALUES (?, ?, ?)');
            $ordineBase = count($foto);
            foreach ($fotoValide as $i => $fv) {
                move_uploaded_file($fv['tmp'], UPLOAD_FOTO_DIR . '/' . $fv['nome']);
                $stmtFoto->execute([$specieId, $fv['nome'], $ordineBase + $i]);
            }

            $pdo->commit();
            header('Location: /admin/specie_form.php?id=' . $specieId . '&salvato=1');
            exit;
        } catch (Exception $e) {
            $pdo->rollBack();
            $errori[] = 'Errore durante il salvataggio: ' . $e->getMessage();
        }
    }
}

$pageTitle = ($specie ? 'Modifica' : 'Nuova') . ' specie — ' . NOME_CIRCOLO;
require_once __DIR__ . '/../includes/header.php';
?>

<div class="admin-bar">
  <span>Connesso come <b><?= h($_SESSION['admin_username']) ?></b></span>
  <a href="/admin/dashboard.php">← Torna al pannello</a>
</div>

<section class="admin-shell">
  <div class="admin-card">
    <div class="eyebrow"><?= $specie ? 'Modifica specie' : 'Nuova specie' ?></div>
    <h2><?= $specie ? h($specie['nome_comune']) : 'Aggiungi un fungo al database' ?></h2>

    <?php if (!empty($_GET['salvato'])): ?><div class="alert alert-ok">Scheda salvata correttamente.</div><?php endif; ?>
    <?php foreach ($errori as $err): ?><div class="alert alert-err"><?= h($err) ?></div><?php endforeach; ?>

    <form method="post" enctype="multipart/form-data">
      <div class="form-grid">
        <div class="field"><label>Nome comune *</label><input type="text" name="nome_comune" value="<?= h($specie['nome_comune'] ?? '') ?>" required></div>
        <div class="field"><label>Nome scientifico *</label><input type="text" name="nome_scientifico" value="<?= h($specie['nome_scientifico'] ?? '') ?>" required></div>
        <div class="field full">
          <label>Nomi alternativi / sinonimi (separati da virgola)</label>
          <input type="text" name="nomi_alternativi" value="<?= h($specie['nomi_alternativi'] ?? '') ?>" placeholder="Es. Ceppatello, Boleto">
          <div class="help">Nomi dialettali, comuni in altre regioni o sinonimi con cui i soci potrebbero cercare questo fungo: compaiono nella ricerca del database, non nella scheda pubblica.</div>
        </div>
        <div class="field">
          <label>Commestibilità *</label>
          <select name="commestibilita">
            <?php $cur = $specie['commestibilita'] ?? 'verifica'; ?>
            <option value="si" <?= $cur === 'si' ? 'selected' : '' ?>>Commestibile</option>
            <option value="no" <?= $cur === 'no' ? 'selected' : '' ?>>Non commestibile</option>
            <option value="verifica" <?= $cur === 'verifica' ? 'selected' : '' ?>>Da verificare con esperto</option>
          </select>
        </div>
        <div class="field">
          <label>Gruppo tassonomico</label>
          <select name="gruppo">
            <?php $curGruppo = $specie['gruppo'] ?? ''; ?>
            <option value="" <?= $curGruppo === '' ? 'selected' : '' ?>>Non specificato</option>
            <?php foreach (gruppi_tassonomici() as $g): ?>
              <option value="<?= h($g) ?>" <?= $curGruppo === $g ? 'selected' : '' ?>><?= h($g) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="field"><label>Habitat</label><input type="text" name="habitat" value="<?= h($specie['habitat'] ?? '') ?>"></div>
        <div class="field"><label>Periodo</label><input type="text" name="periodo" value="<?= h($specie['periodo'] ?? '') ?>"></div>
        <div class="field"><label>Cappello</label><input type="text" name="cappello" value="<?= h($specie['cappello'] ?? '') ?>"></div>
        <div class="field"><label>Gambo</label><input type="text" name="gambo" value="<?= h($specie['gambo'] ?? '') ?>"></div>
        <div class="field"><label>Imenio (lamelle/tubuli/pliche)</label><input type="text" name="imenio" value="<?= h($specie['imenio'] ?? '') ?>"></div>
        <div class="field"><label>Spore</label><input type="text" name="spore" value="<?= h($specie['spore'] ?? '') ?>"></div>
        <div class="field full"><label>Note</label><textarea name="note"><?= h($specie['note'] ?? '') ?></textarea></div>

        <div class="field full">
          <label>Fotografie (puoi selezionarne più di una insieme)</label>
          <input type="file" name="foto[]" accept="image/png, image/jpeg, image/webp" multiple>
          <div class="help">Formati ammessi: JPG, PNG, WEBP · max <?= MAX_FOTO_MB ?> MB ciascuna. Le nuove foto si aggiungono a quelle già presenti.</div>
          <?php if (!empty($foto)): ?>
            <div class="thumb-row">
              <?php foreach ($foto as $ph): ?>
                <div class="t">
                  <img src="/serve_image.php?id=<?= (int)$ph['id'] ?>" alt="">
                  <a class="del" href="/admin/foto_delete.php?id=<?= (int)$ph['id'] ?>&specie_id=<?= (int)$specie['id'] ?>" onclick="return confirm('Eliminare questa foto?');" title="Elimina">×</a>
                </div>
              <?php endforeach; ?>
            </div>
          <?php endif; ?>
        </div>

        <div class="field full">
          <label>Scheda tecnica in PDF (opzionale)</label>
          <input type="file" name="pdf" accept="application/pdf">
          <div class="help">Max <?= MAX_PDF_MB ?> MB. Caricandone uno nuovo, sostituisce quello attuale.</div>
          <?php if (!empty($specie['pdf_path'])): ?>
            <div class="help">PDF attuale: <a class="vedi" href="/serve_pdf.php?specie_id=<?= (int)$specie['id'] ?>" target="_blank" rel="noopener">visualizza</a></div>
          <?php endif; ?>
        </div>
      </div>

      <button class="btn btn-primary" type="submit"><?= $specie ? 'Salva modifiche' : 'Crea specie' ?></button>
    </form>
  </div>
</section>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
