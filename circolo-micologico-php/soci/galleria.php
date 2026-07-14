<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/functions.php';
require_socio();

$foto = db()->query('SELECT * FROM galleria_soci ORDER BY ordine, id')->fetchAll();

$pageTitle = 'Galleria soci — ' . NOME_CIRCOLO;
require_once __DIR__ . '/../includes/header.php';
?>

<section class="admin-shell" style="max-width:1000px;">
  <div class="admin-card">
    <div class="eyebrow">Riservata ai soci</div>
    <h2>Galleria fotografica</h2>
    <p class="lead" style="margin-bottom:0;">Fotografie visibili solo ai soci collegati: uscite, ritrovamenti e
    momenti di vita del circolo. Non compaiono in nessuna pagina pubblica del sito.</p>
  </div>

  <div class="admin-card">
    <?php if (empty($foto)): ?>
      <p style="color:#8a8267;">Nessuna fotografia ancora caricata dall'amministratore.</p>
    <?php else: ?>
      <div class="galleria-soci-grid">
        <?php foreach ($foto as $i => $f): ?>
          <button type="button" class="galleria-soci-item" data-full="/serve_galleria_soci_foto.php?id=<?= (int)$f['id'] ?>" data-didascalia="<?= h($f['didascalia'] ?? '') ?>">
            <img src="/serve_galleria_soci_foto.php?id=<?= (int)$f['id'] ?>" alt="<?= h($f['didascalia'] ?: 'Foto della galleria soci') ?>" loading="<?= $i < 4 ? 'eager' : 'lazy' ?>">
          </button>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
    <div class="help" style="margin-top:1.4rem;"><a href="/soci/logout.php">Esci</a></div>
  </div>
</section>

<!-- Lightbox minimale: ingrandisce la foto cliccata, senza librerie esterne -->
<div id="lightbox" style="display:none;position:fixed;inset:0;z-index:400;background:rgba(15,17,10,.92);align-items:center;justify-content:center;flex-direction:column;padding:2rem;">
  <img id="lightboxImg" src="" alt="" style="max-width:min(90vw,900px);max-height:75vh;object-fit:contain;border-radius:4px;">
  <p id="lightboxCaption" style="color:#e9e2d0;margin-top:1rem;font-size:.9rem;max-width:70ch;text-align:center;"></p>
  <button type="button" id="lightboxClose" style="margin-top:1.2rem;color:#e9e2d0;border:1px solid rgba(255,255,255,.3);padding:.5rem 1.2rem;border-radius:3px;">Chiudi</button>
</div>

<script>
(function () {
  const lightbox = document.getElementById('lightbox');
  const img = document.getElementById('lightboxImg');
  const caption = document.getElementById('lightboxCaption');

  document.querySelectorAll('.galleria-soci-item').forEach(function (btn) {
    btn.addEventListener('click', function () {
      img.src = btn.dataset.full;
      caption.textContent = btn.dataset.didascalia || '';
      lightbox.style.display = 'flex';
    });
  });

  function chiudi() { lightbox.style.display = 'none'; img.src = ''; }
  document.getElementById('lightboxClose').addEventListener('click', chiudi);
  lightbox.addEventListener('click', function (e) { if (e.target === lightbox) chiudi(); });
  document.addEventListener('keydown', function (e) { if (e.key === 'Escape') chiudi(); });
})();
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
