<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/functions.php';

$membri = db()->query("SELECT * FROM consiglio ORDER BY ordine, id")->fetchAll();

$pageTitle = 'Consiglio direttivo — ' . NOME_CIRCOLO;
$metaDescription = 'Le persone che coordinano le attività del circolo micologico, elette dai soci.';
require_once __DIR__ . '/includes/header.php';
?>

<section class="section section-light" style="padding-top:7.5rem;">
  <div class="eyebrow">Chi siamo</div>
  <h2>Consiglio direttivo</h2>
  <p class="lead">Le persone che coordinano le attività del circolo, elette dai soci.</p>

  <?php if (empty($membri)): ?>
    <div class="empty-state">L'elenco del consiglio direttivo non è ancora stato pubblicato.</div>
  <?php else: ?>
    <div class="fungo-grid" style="margin-top:2.2rem;">
      <?php foreach ($membri as $m): ?>
        <div class="fungo-card">
          <?php if (!empty($m['foto_path'])): ?>
            <div class="fungo-photo protetta-wrap">
              <img class="protetta" src="/serve_consiglio_foto.php?id=<?= (int)$m['id'] ?>" alt="<?= h($m['nome']) ?>" loading="lazy">
            </div>
          <?php endif; ?>
          <div class="fungo-body" style="<?= empty($m['foto_path']) ? 'padding-top:1.4rem;' : '' ?>">
            <h3><?= h($m['nome']) ?></h3>
            <?php if ($m['ruolo']): ?><span class="fungo-sci" style="font-style:normal;"><?= h($m['ruolo']) ?></span><?php endif; ?>
            <?php if ($m['bio']): ?><p style="font-size:.9rem;color:#4a4437;margin-top:.5rem;"><?= nl2br(h($m['bio'])) ?></p><?php endif; ?>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
