<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/functions.php';

$stmt = db()->query("SELECT * FROM documento_sociale ORDER BY CASE slug WHEN 'statuto' THEN 0 ELSE 1 END");
$documenti = $stmt->fetchAll();

$pageTitle = 'Statuto e regolamento — ' . NOME_CIRCOLO;
$metaDescription = 'Statuto e regolamento interno del circolo micologico: i documenti che regolano la vita associativa.';
require_once __DIR__ . '/includes/header.php';
?>

<section class="section section-light" style="padding-top:7.5rem;">
  <div class="eyebrow">Chi siamo</div>
  <h2>Statuto e regolamento</h2>
  <p class="lead">I documenti che regolano la vita associativa del circolo.</p>

  <?php if (empty($documenti)): ?>
    <div class="empty-state">Documenti non ancora pubblicati.</div>
  <?php else: ?>
    <?php foreach ($documenti as $d): ?>
      <div style="margin-top:2.6rem;padding-top:2.2rem;border-top:1px solid #e3dcc7;">
        <h3 style="font-size:1.4rem;margin-bottom:.8rem;"><?= h($d['titolo']) ?></h3>

        <?php if ($d['pdf_path']): ?>
          <div class="pdf-box" style="margin-bottom:1.2rem;">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M6 2h9l5 5v15H6Z"/><path d="M15 2v5h5"/></svg>
            <div>
              <div style="font-weight:500;">Versione ufficiale in PDF</div>
              <a href="/serve_pdf.php?tipo=sociale&id=<?= (int)$d['id'] ?>" target="_blank" rel="noopener" class="vedi">Apri il documento →</a>
            </div>
          </div>
        <?php endif; ?>

        <?php if ($d['testo']): ?>
          <div class="lead" style="max-width:70ch;color:#3a3427;"><?= nl2br(h($d['testo'])) ?></div>
        <?php endif; ?>
      </div>
    <?php endforeach; ?>
  <?php endif; ?>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
