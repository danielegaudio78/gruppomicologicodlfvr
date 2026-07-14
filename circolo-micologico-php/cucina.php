<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/functions.php';

$ricette = db()->query("SELECT * FROM ricetta ORDER BY ordine, id")->fetchAll();

$pageTitle = 'In cucina — ' . NOME_CIRCOLO;
$metaDescription = 'Ricette a base di funghi curate dal circolo, con abbinamenti vino consigliati per ogni piatto.';
require_once __DIR__ . '/includes/header.php';
?>

<section class="section section-light" style="padding-top:7.5rem;">
  <div class="eyebrow">In cucina</div>
  <h2>Ricette del circolo</h2>
  <p class="lead">Dai boschi alla tavola: le ricette raccolte dai soci, con un abbinamento vino consigliato per ogni piatto.</p>

  <?php if (empty($ricette)): ?>
    <div class="empty-state">Nessuna ricetta ancora pubblicata.</div>
  <?php else: ?>
    <div class="fungo-grid" style="margin-top:2.2rem;">
      <?php foreach ($ricette as $r): ?>
        <div class="fungo-card">
          <div class="fungo-photo protetta-wrap watermarked">
            <?php if ($r['foto_path']): ?>
              <img class="protetta" src="/serve_ricetta_foto.php?id=<?= (int)$r['id'] ?>" alt="<?= h($r['titolo']) ?>" loading="lazy">
            <?php else: ?>
              <div class="placeholder">Foto non ancora caricata</div>
            <?php endif; ?>
          </div>
          <div class="fungo-body">
            <h3><?= h($r['titolo']) ?></h3>
            <?php if ($r['categoria']): ?><span class="fungo-sci" style="font-style:normal;"><?= h($r['categoria']) ?></span><?php endif; ?>
            <div style="display:flex;gap:.5rem;flex-wrap:wrap;margin:.5rem 0;">
              <?php if ($r['tempo_preparazione']): ?><span class="badge badge-verifica"><?= h($r['tempo_preparazione']) ?></span><?php endif; ?>
              <?php if ($r['difficolta']): ?><span class="badge badge-si"><?= h($r['difficolta']) ?></span><?php endif; ?>
            </div>
            <?php if ($r['abbinamento_vino']): ?>
              <p style="font-size:.85rem;color:#7b7460;margin-bottom:.4rem;">🍷 <?= h(riassumi_testo($r['abbinamento_vino'], 70)) ?></p>
            <?php endif; ?>
            <a class="vedi" href="/ricetta.php?id=<?= (int)$r['id'] ?>">Vedi la ricetta completa →</a>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
