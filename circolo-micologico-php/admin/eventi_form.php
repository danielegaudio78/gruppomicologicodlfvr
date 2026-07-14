<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/functions.php';
require_admin();

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
$evento = null;
if ($id) {
    $stmt = db()->prepare('SELECT * FROM eventi WHERE id = ?');
    $stmt->execute([$id]);
    $evento = $stmt->fetch();
    if (!$evento) { header('Location: /admin/dashboard.php'); exit; }
}

$errori = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $titolo = trim($_POST['titolo'] ?? '');
    $data_evento = $_POST['data_evento'] ?? '';
    $tag = trim($_POST['tag'] ?? '');
    $descrizione = trim($_POST['descrizione'] ?? '');
    $rimuovi_immagine = isset($_POST['rimuovi_immagine']);
    $rimuovi_allegato = isset($_POST['rimuovi_allegato']);

    if ($titolo === '') $errori[] = 'Il titolo è obbligatorio.';
    if (!$data_evento || !strtotime($data_evento)) $errori[] = 'Indica una data valida.';

    // --- Immagine (opzionale): JPG, PNG o WEBP ---
    $nuovaImmagine = null;
    if (!empty($_FILES['immagine']['name'])) {
        $f = $_FILES['immagine'];
        if ($f['error'] !== UPLOAD_ERR_OK) {
            $errori[] = 'Errore nel caricamento dell\'immagine.';
        } elseif ($f['size'] > MAX_FOTO_MB * 1024 * 1024) {
            $errori[] = 'L\'immagine supera i ' . MAX_FOTO_MB . ' MB consentiti.';
        } else {
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mime = finfo_file($finfo, $f['tmp_name']);
            finfo_close($finfo);
            $consentiti = ['image/jpeg' => 1, 'image/png' => 1, 'image/webp' => 1];
            if (!isset($consentiti[$mime])) {
                $errori[] = 'L\'immagine deve essere in formato JPG, PNG o WEBP.';
            } else {
                $nuovaImmagine = nome_file_sicuro($f['name']);
            }
        }
    }

    // --- Allegato (opzionale): qualunque tipo di documento, es. PDF, Word, Excel, zip ---
    $nuovoAllegato = null;
    $nuovoAllegatoNome = null;
    if (!empty($_FILES['allegato']['name'])) {
        $f = $_FILES['allegato'];
        if ($f['error'] !== UPLOAD_ERR_OK) {
            $errori[] = 'Errore nel caricamento dell\'allegato.';
        } elseif ($f['size'] > MAX_ALLEGATO_MB * 1024 * 1024) {
            $errori[] = 'L\'allegato supera i ' . MAX_ALLEGATO_MB . ' MB consentiti.';
        } else {
            $nuovoAllegato = nome_file_sicuro($f['name']);
            $nuovoAllegatoNome = basename($f['name']);
        }
    }

    if (empty($errori)) {
        $pdo = db();

        if ($evento) {
            $set = 'titolo=?, data_evento=?, tag=?, descrizione=?';
            $params = [$titolo, $data_evento, $tag, $descrizione];

            if ($nuovaImmagine) { $set .= ', immagine_path=?'; $params[] = $nuovaImmagine; }
            elseif ($rimuovi_immagine) { $set .= ', immagine_path=NULL'; }

            if ($nuovoAllegato) { $set .= ', allegato_path=?, allegato_nome_originale=?'; $params[] = $nuovoAllegato; $params[] = $nuovoAllegatoNome; }
            elseif ($rimuovi_allegato) { $set .= ', allegato_path=NULL, allegato_nome_originale=NULL'; }

            $params[] = $evento['id'];
            $pdo->prepare("UPDATE eventi SET $set WHERE id=?")->execute($params);
            $eventoId = $evento['id'];

            if ($nuovaImmagine && $evento['immagine_path']) @unlink(UPLOAD_EVENTI_IMG_DIR . '/' . $evento['immagine_path']);
            if ($rimuovi_immagine && !$nuovaImmagine && $evento['immagine_path']) @unlink(UPLOAD_EVENTI_IMG_DIR . '/' . $evento['immagine_path']);
            if ($nuovoAllegato && $evento['allegato_path']) @unlink(UPLOAD_EVENTI_ALLEGATI_DIR . '/' . $evento['allegato_path']);
            if ($rimuovi_allegato && !$nuovoAllegato && $evento['allegato_path']) @unlink(UPLOAD_EVENTI_ALLEGATI_DIR . '/' . $evento['allegato_path']);
        } else {
            $stmt = $pdo->prepare('INSERT INTO eventi (titolo, data_evento, tag, descrizione, immagine_path, allegato_path, allegato_nome_originale) VALUES (?,?,?,?,?,?,?)');
            $stmt->execute([$titolo, $data_evento, $tag, $descrizione, $nuovaImmagine, $nuovoAllegato, $nuovoAllegatoNome]);
            $eventoId = (int) $pdo->lastInsertId();
        }

        if ($nuovaImmagine) move_uploaded_file($_FILES['immagine']['tmp_name'], UPLOAD_EVENTI_IMG_DIR . '/' . $nuovaImmagine);
        if ($nuovoAllegato) move_uploaded_file($_FILES['allegato']['tmp_name'], UPLOAD_EVENTI_ALLEGATI_DIR . '/' . $nuovoAllegato);

        header('Location: /admin/eventi_form.php?id=' . $eventoId . '&salvato=1');
        exit;
    }
}

$pageTitle = ($evento ? 'Modifica' : 'Nuovo') . ' evento — ' . NOME_CIRCOLO;
require_once __DIR__ . '/../includes/header.php';
?>

<div class="admin-bar">
  <span>Connesso come <b><?= h($_SESSION['admin_username']) ?></b></span>
  <a href="/admin/dashboard.php">← Torna al pannello</a>
</div>

<section class="admin-shell">
  <div class="admin-card">
    <div class="eyebrow"><?= $evento ? 'Modifica evento' : 'Nuovo evento' ?></div>
    <h2><?= $evento ? h($evento['titolo']) : 'Aggiungi un evento' ?></h2>

    <?php if (!empty($_GET['salvato'])): ?><div class="alert alert-ok">Evento salvato correttamente.</div><?php endif; ?>
    <?php foreach ($errori as $err): ?><div class="alert alert-err"><?= h($err) ?></div><?php endforeach; ?>

    <form method="post" enctype="multipart/form-data">
      <div class="form-grid">
        <div class="field"><label>Titolo *</label><input type="text" name="titolo" value="<?= h($evento['titolo'] ?? '') ?>" required></div>
        <div class="field"><label>Data *</label><input type="date" name="data_evento" value="<?= h($evento['data_evento'] ?? '') ?>" required></div>
        <div class="field"><label>Tag (es. Uscita, Corso, Mostra)</label><input type="text" name="tag" value="<?= h($evento['tag'] ?? '') ?>"></div>
        <div class="field full"><label>Descrizione</label><textarea name="descrizione"><?= h($evento['descrizione'] ?? '') ?></textarea></div>

        <div class="field">
          <label>Immagine dell'evento — opzionale</label>
          <input type="file" name="immagine" accept="image/jpeg, image/png, image/webp">
          <div class="help">JPG, PNG o WEBP, max <?= MAX_FOTO_MB ?> MB. Caricandone una nuova, sostituisce quella attuale.</div>
          <?php if (!empty($evento['immagine_path'])): ?>
            <div class="thumb-row">
              <div class="t"><img src="/serve_evento_immagine.php?id=<?= (int)$evento['id'] ?>" alt=""></div>
            </div>
            <label style="display:flex;align-items:center;gap:.4rem;margin-top:.5rem;font-size:.85rem;font-family:var(--font-body);text-transform:none;letter-spacing:0;">
              <input type="checkbox" name="rimuovi_immagine" value="1" style="width:auto;"> Rimuovi l'immagine attuale
            </label>
          <?php endif; ?>
        </div>

        <div class="field">
          <label>Allegato — opzionale</label>
          <input type="file" name="allegato">
          <div class="help">Qualunque tipo di file (PDF, Word, Excel, immagine, zip…), max <?= MAX_ALLEGATO_MB ?> MB. Caricandone uno nuovo, sostituisce quello attuale.</div>
          <?php if (!empty($evento['allegato_path'])): ?>
            <div class="help">Allegato attuale: <a class="vedi" href="/serve_evento_allegato.php?id=<?= (int)$evento['id'] ?>" target="_blank" rel="noopener"><?= h($evento['allegato_nome_originale'] ?: 'scarica') ?></a></div>
            <label style="display:flex;align-items:center;gap:.4rem;margin-top:.5rem;font-size:.85rem;font-family:var(--font-body);text-transform:none;letter-spacing:0;">
              <input type="checkbox" name="rimuovi_allegato" value="1" style="width:auto;"> Rimuovi l'allegato attuale
            </label>
          <?php endif; ?>
        </div>
      </div>
      <button class="btn btn-primary" type="submit"><?= $evento ? 'Salva modifiche' : 'Crea evento' ?></button>
    </form>
  </div>
</section>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
