<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/functions.php';

$riviste = db()->query("SELECT * FROM pubblicazioni ORDER BY data_pubblicazione DESC, id DESC")->fetchAll();

$pageTitle = 'Riviste — ' . NOME_CIRCOLO;
$metaDescription = 'Pubblicazioni periodiche del circolo micologico: numeri arretrati e ultime uscite in PDF.';
require_once __DIR__ . '/includes/header.php';
?>

<section class="section section-light" style="padding-top:7.5rem;">
  <div class="eyebrow">Pubblicazioni</div>
  <h2>Riviste del circolo</h2>
  <p class="lead">Il notiziario e le pubblicazioni periodiche del circolo, disponibili in PDF.</p>

  <?php if (empty($riviste)): ?>
    <div class="empty-state">Nessuna pubblicazione ancora caricata.</div>
  <?php else: ?>
    <div class="fungo-grid" style="margin-top:2.2rem;">
      <?php foreach ($riviste as $r): ?>
        <div class="fungo-card">
          <div class="fungo-body" style="padding-top:1.4rem;">
            <?php if ($r['data_pubblicazione']): ?><span class="eyebrow"><?= strtoupper(data_it($r['data_pubblicazione'])) ?></span><?php endif; ?>
            <h3 style="margin-top:.3rem;"><?= h($r['titolo']) ?></h3>
            <?php if ($r['descrizione']): ?><p style="font-size:.9rem;color:#4a4437;margin-top:.4rem;"><?= nl2br(h($r['descrizione'])) ?></p><?php endif; ?>
            <?php if ($r['pdf_path']): ?>
              <a class="vedi" style="margin-top:.8rem;" href="/serve_pdf.php?tipo=riviste&id=<?= (int)$r['id'] ?>" target="_blank" rel="noopener">Sfoglia il PDF →</a>
            <?php else: ?>
              <span style="color:#8a8267;font-size:.85rem;">PDF non ancora caricato</span>
            <?php endif; ?>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
