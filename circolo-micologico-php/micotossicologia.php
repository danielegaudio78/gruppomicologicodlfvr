<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/functions.php';

$sindromi = db()->query("SELECT * FROM tossine ORDER BY nome_sindrome")->fetchAll();

$pageTitle = 'Micotossicologia — ' . NOME_CIRCOLO;
$metaDescription = 'Sindromi da avvelenamento da funghi: tempi di latenza, sintomi e gravità delle principali intossicazioni micologiche.';
require_once __DIR__ . '/includes/header.php';
?>

<section class="section section-dark" style="padding-top:7.5rem;">
  <div class="eyebrow">Tossicologia</div>
  <h2>Micotossicologia</h2>
  <p class="lead">Le principali sindromi da avvelenamento fungino, per riconoscere i segnali e sapere quando è
  un'emergenza. Questa pagina non sostituisce in alcun caso un consulto medico.</p>

  <div class="safety" style="margin-top:2rem;background:var(--rosso-allerta);">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><path d="M12 2 L22 20 L2 20 Z"/><line x1="12" y1="9" x2="12" y2="13"/><circle cx="12" cy="16.3" r=".6" fill="currentColor"/></svg>
    <div><b>In caso di sospetta intossicazione</b> dopo aver mangiato funghi, rivolgiti subito al pronto soccorso o
    contatta un Centro Antiveleni, anche se i sintomi sembrano lievi o tardano a comparire: alcune sindromi hanno un
    esordio ritardato di molte ore. Se possibile, porta con te un campione del fungo consumato (anche gli avanzi di cottura).</div>
  </div>

  <?php if (empty($sindromi)): ?>
    <p style="margin-top:2rem;color:#cfd3c2;">Nessuna scheda ancora pubblicata.</p>
  <?php else: ?>
    <div class="fungo-grid" style="margin-top:2.2rem;">
      <?php foreach ($sindromi as $t): ?>
        <div class="fungo-card">
          <div class="fungo-body" style="padding-top:1.4rem;">
            <h3><?= h($t['nome_sindrome']) ?></h3>
            <?php if ($t['gravita']): ?><span class="badge badge-no"><?= h($t['gravita']) ?></span><?php endif; ?>
            <dl class="scheda-dl" style="margin-top:.6rem;">
              <?php if ($t['tempo_latenza']): ?><dt>Latenza</dt><dd><?= h($t['tempo_latenza']) ?></dd><?php endif; ?>
              <?php if ($t['funghi_coinvolti']): ?><dt>Funghi coinvolti</dt><dd><?= h($t['funghi_coinvolti']) ?></dd><?php endif; ?>
              <?php if ($t['sintomi']): ?><dt>Sintomi</dt><dd><?= nl2br(h($t['sintomi'])) ?></dd><?php endif; ?>
              <?php if ($t['note']): ?><dt>Note</dt><dd><?= nl2br(h($t['note'])) ?></dd><?php endif; ?>
            </dl>
            <?php if ($t['pdf_path']): ?>
              <a class="vedi" style="margin-top:.8rem;" href="/serve_pdf.php?tipo=tossine&id=<?= (int)$t['id'] ?>" target="_blank" rel="noopener">Apri la scheda PDF →</a>
            <?php endif; ?>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
