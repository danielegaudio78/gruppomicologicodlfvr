<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/functions.php';

$lezioni = db()->query("SELECT * FROM lezioni_corso ORDER BY ordine, id")->fetchAll();
$puoVedereMateriale = is_socio() || is_admin();

$pageTitle = 'Corso di micologia — ' . NOME_CIRCOLO;
$metaDescription = 'Corso annuale di micologia del circolo: lezioni di riconoscimento funghi con materiale didattico e slide scaricabili.';
require_once __DIR__ . '/includes/header.php';
?>

<section class="section section-light" style="padding-top:7.5rem;">
  <div class="eyebrow">Formazione</div>
  <h2>Corso di micologia annuale</h2>
  <p class="lead">Il programma delle lezioni è pubblico; il materiale didattico (PDF e cartella condivisa) è
  riservato ai soci: <a href="/soci/login.php" style="text-decoration:underline;">accedi</a> per scaricarlo.</p>

  <?php if (empty($lezioni)): ?>
    <div class="empty-state">Il programma del corso non è ancora stato pubblicato.</div>
  <?php else: ?>
    <div class="timeline" style="margin-top:2.4rem;">
      <?php foreach ($lezioni as $l): ?>
        <div class="evento">
          <?php if ($l['data_lezione']): ?><div class="data"><?= strtoupper(data_it($l['data_lezione'])) ?></div><?php endif; ?>
          <h3><?= h($l['titolo']) ?></h3>
          <?php if ($l['descrizione']): ?><p><?= nl2br(h($l['descrizione'])) ?></p><?php endif; ?>
          <div style="display:flex;gap:1rem;flex-wrap:wrap;margin-top:.5rem;align-items:center;">
            <?php if (!$puoVedereMateriale): ?>
              <?php if ($l['pdf_path'] || $l['link_esterno']): ?>
                <span class="badge badge-verifica">Riservato ai soci</span>
                <a class="vedi" href="/soci/login.php">Accedi per scaricare il materiale →</a>
              <?php else: ?>
                <span style="color:#8a8267;font-size:.88rem;">Materiale non ancora caricato</span>
              <?php endif; ?>
            <?php else: ?>
              <?php if ($l['pdf_path']): ?>
                <a class="vedi" href="/serve_pdf.php?tipo=corso&id=<?= (int)$l['id'] ?>" target="_blank" rel="noopener">Apri il PDF della lezione →</a>
              <?php endif; ?>
              <?php if ($l['link_esterno']): ?>
                <a class="vedi" href="<?= h($l['link_esterno']) ?>" target="_blank" rel="noopener">Materiale su SharePoint →</a>
              <?php endif; ?>
              <?php if (!$l['pdf_path'] && !$l['link_esterno']): ?>
                <span style="color:#8a8267;font-size:.88rem;">Materiale non ancora caricato</span>
              <?php endif; ?>
            <?php endif; ?>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
