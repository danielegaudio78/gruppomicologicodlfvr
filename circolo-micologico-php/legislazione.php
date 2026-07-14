<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/functions.php';

$voci = db()->query("SELECT * FROM legislazione ORDER BY regione, titolo")->fetchAll();

$pageTitle = 'Legislazione regionale — ' . NOME_CIRCOLO;
$metaDescription = 'Normative regionali sulla raccolta dei funghi: regolamenti, permessi e limiti di raccolta aggiornati per regione.';
require_once __DIR__ . '/includes/header.php';
?>

<section class="section section-light" style="padding-top:7.5rem;">
  <div class="eyebrow">Normativa</div>
  <h2>Legislazione regionale</h2>
  <p class="lead">La raccolta dei funghi epigei è regolata a livello regionale: quantitativi, giorni consentiti,
  tesserino richiesto variano da regione a regione. Verifica sempre anche presso il Comune o l'ASL di competenza.</p>

  <?php if (empty($voci)): ?>
    <div class="empty-state">Nessuna scheda normativa ancora pubblicata.</div>
  <?php else: ?>
    <div class="fungo-grid" style="margin-top:2.2rem;">
      <?php foreach ($voci as $v): ?>
        <div class="fungo-card">
          <div class="fungo-body" style="padding-top:1.4rem;">
            <span class="eyebrow"><?= h($v['regione']) ?></span>
            <h3 style="margin-top:.3rem;"><?= h($v['titolo']) ?></h3>
            <?php if ($v['testo']): ?><p style="font-size:.9rem;color:#4a4437;margin-top:.5rem;"><?= nl2br(h($v['testo'])) ?></p><?php endif; ?>
            <div style="display:flex;gap:1rem;flex-wrap:wrap;margin-top:.8rem;">
              <?php if ($v['pdf_path']): ?>
                <a class="vedi" href="/serve_pdf.php?tipo=legislazione&id=<?= (int)$v['id'] ?>" target="_blank" rel="noopener">Apri il PDF →</a>
              <?php endif; ?>
              <?php if ($v['link_esterno']): ?>
                <a class="vedi" href="<?= h($v['link_esterno']) ?>" target="_blank" rel="noopener">Fonte ufficiale →</a>
              <?php endif; ?>
            </div>
            <div class="help" style="margin-top:.8rem;">Aggiornato il <?= data_it($v['aggiornato_il'] ? substr($v['aggiornato_il'],0,10) : null) ?></div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
