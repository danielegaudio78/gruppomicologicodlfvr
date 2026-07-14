<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/functions.php';
require_admin();

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
$membro = null;
if ($id) {
    $stmt = db()->prepare('SELECT * FROM consiglio WHERE id = ?');
    $stmt->execute([$id]);
    $membro = $stmt->fetch();
    if (!$membro) { header('Location: /admin/dashboard.php'); exit; }
}

$errori = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = trim($_POST['nome'] ?? '');
    $ruolo = trim($_POST['ruolo'] ?? '');
    $bio = trim($_POST['bio'] ?? '');
    $rimuovi_foto = isset($_POST['rimuovi_foto']);

    if ($nome === '') $errori[] = 'Il nome è obbligatorio.';

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
        if ($membro) {
            $set = 'nome=?, ruolo=?, bio=?';
            $params = [$nome, $ruolo, $bio];
            if ($nuovaFoto) { $set .= ', foto_path=?'; $params[] = $nuovaFoto; }
            elseif ($rimuovi_foto) { $set .= ', foto_path=NULL'; }
            $params[] = $membro['id'];
            $pdo->prepare("UPDATE consiglio SET $set WHERE id=?")->execute($params);
            $membroId = $membro['id'];

            if ($nuovaFoto && $membro['foto_path']) @unlink(UPLOAD_CONSIGLIO_DIR . '/' . $membro['foto_path']);
            if ($rimuovi_foto && !$nuovaFoto && $membro['foto_path']) @unlink(UPLOAD_CONSIGLIO_DIR . '/' . $membro['foto_path']);
        } else {
            $ordine = (int) $pdo->query('SELECT COALESCE(MAX(ordine), -1) FROM consiglio')->fetchColumn() + 1;
            $stmt = $pdo->prepare('INSERT INTO consiglio (nome, ruolo, bio, foto_path, ordine) VALUES (?,?,?,?,?)');
            $stmt->execute([$nome, $ruolo, $bio, $nuovaFoto, $ordine]);
            $membroId = (int) $pdo->lastInsertId();
        }
        if ($nuovaFoto) move_uploaded_file($_FILES['foto']['tmp_name'], UPLOAD_CONSIGLIO_DIR . '/' . $nuovaFoto);

        header('Location: /admin/consiglio_form.php?id=' . $membroId . '&salvato=1');
        exit;
    }
}

$pageTitle = ($membro ? 'Modifica' : 'Nuovo') . ' membro del consiglio — ' . NOME_CIRCOLO;
require_once __DIR__ . '/../includes/header.php';
?>

<div class="admin-bar">
  <span>Connesso come <b><?= h($_SESSION['admin_username']) ?></b></span>
  <a href="/admin/dashboard.php">← Torna al pannello</a>
</div>

<section class="admin-shell">
  <div class="admin-card">
    <div class="eyebrow"><?= $membro ? 'Modifica membro' : 'Nuovo membro' ?></div>
    <h2><?= $membro ? h($membro['nome']) : 'Aggiungi un membro del consiglio' ?></h2>

    <?php if (!empty($_GET['salvato'])): ?><div class="alert alert-ok">Scheda salvata correttamente.</div><?php endif; ?>
    <?php foreach ($errori as $err): ?><div class="alert alert-err"><?= h($err) ?></div><?php endforeach; ?>

    <form method="post" enctype="multipart/form-data">
      <div class="form-grid">
        <div class="field"><label>Nome e cognome *</label><input type="text" name="nome" value="<?= h($membro['nome'] ?? '') ?>" required></div>
        <div class="field"><label>Carica (es. Presidente, Segretario)</label><input type="text" name="ruolo" value="<?= h($membro['ruolo'] ?? '') ?>"></div>
        <div class="field full"><label>Breve biografia</label><textarea name="bio"><?= h($membro['bio'] ?? '') ?></textarea></div>

        <div class="field full">
          <label>Fotografia — opzionale</label>
          <input type="file" name="foto" accept="image/jpeg, image/png, image/webp">
          <div class="help">JPG, PNG o WEBP, max <?= MAX_FOTO_MB ?> MB. Caricandone una nuova, sostituisce quella attuale.</div>
          <?php if (!empty($membro['foto_path'])): ?>
            <div class="thumb-row">
              <div class="t"><img src="/serve_consiglio_foto.php?id=<?= (int)$membro['id'] ?>" alt=""></div>
            </div>
            <label style="display:flex;align-items:center;gap:.4rem;margin-top:.5rem;font-size:.85rem;font-family:var(--font-body);text-transform:none;letter-spacing:0;">
              <input type="checkbox" name="rimuovi_foto" value="1" style="width:auto;"> Rimuovi la foto attuale
            </label>
          <?php endif; ?>
        </div>
      </div>
      <button class="btn btn-primary" type="submit"><?= $membro ? 'Salva modifiche' : 'Crea scheda' ?></button>
    </form>
  </div>
</section>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
