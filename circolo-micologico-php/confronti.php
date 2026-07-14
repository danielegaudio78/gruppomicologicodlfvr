<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/functions.php';

$confronti = db()->query("
    SELECT c.*,
        sb.nome_comune AS buona_nome, sb.nome_scientifico AS buona_sci,
        sc.nome_comune AS cattiva_nome, sc.nome_scientifico AS cattiva_sci,
        (SELECT f.id FROM foto f WHERE f.specie_id = sb.id ORDER BY f.ordine, f.id LIMIT 1) AS buona_foto_id,
        (SELECT f.id FROM foto f WHERE f.specie_id = sc.id ORDER BY f.ordine, f.id LIMIT 1) AS cattiva_foto_id
    FROM confronto c
    JOIN specie sb ON sb.id = c.specie_buona_id
    JOIN specie sc ON sc.id = c.specie_cattiva_id
    ORDER BY c.ordine, c.id
")->fetchAll();

$pageTitle = 'Fungo buono, fungo cattivo — ' . NOME_CIRCOLO;
$metaDescription = 'Confronti fotografici tra specie fungine commestibili e i loro sosia tossici, per imparare a riconoscere le differenze.';
require_once __DIR__ . '/includes/header.php';
?>

<section class="section section-light" style="padding-top:7.5rem;">
  <div class="eyebrow">Riconoscimento</div>
  <h2>Fungo buono, fungo cattivo</h2>
  <p class="lead">Confronti fotografici tra specie commestibili e i loro sosia pericolosi, per imparare a
  riconoscere le differenze prima di raccogliere.</p>
</section>

<div class="safety">
  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><path d="M12 2 L22 20 L2 20 Z"/><line x1="12" y1="9" x2="12" y2="13"/><circle cx="12" cy="16.3" r=".6" fill="currentColor"/></svg>
  <div><b>Promemoria di sicurezza:</b> questi confronti sono un supporto didattico, non un metodo di identificazione
  definitivo. Non consumare mai un fungo raccolto senza una determinazione certa da parte di un esperto micologo.</div>
</div>

<section class="section section-light">
  <?php if (empty($confronti)): ?>
    <div class="empty-state">Nessun confronto ancora pubblicato.</div>
  <?php else: ?>
    <div class="fungo-grid">
      <?php foreach ($confronti as $c): ?>
        <a href="/confronto.php?id=<?= (int)$c['id'] ?>" class="fungo-card" style="display:block;text-decoration:none;color:inherit;">
          <div style="display:grid;grid-template-columns:1fr 1fr;">
            <div class="fungo-photo protetta-wrap watermarked" style="border-radius:0;">
              <?php if ($c['buona_foto_id']): ?>
                <img class="protetta" src="/serve_image.php?id=<?= (int)$c['buona_foto_id'] ?>" alt="<?= h($c['buona_nome']) ?>" loading="lazy">
              <?php else: ?><div class="placeholder">Foto non caricata</div><?php endif; ?>
            </div>
            <div class="fungo-photo protetta-wrap watermarked" style="border-radius:0;">
              <?php if ($c['cattiva_foto_id']): ?>
                <img class="protetta" src="/serve_image.php?id=<?= (int)$c['cattiva_foto_id'] ?>" alt="<?= h($c['cattiva_nome']) ?>" loading="lazy">
              <?php else: ?><div class="placeholder">Foto non caricata</div><?php endif; ?>
            </div>
          </div>
          <div class="fungo-body">
            <h3><?= h($c['titolo'] ?: ($c['buona_nome'] . ' vs ' . $c['cattiva_nome'])) ?></h3>
            <div style="display:flex;gap:.5rem;flex-wrap:wrap;margin-top:.5rem;">
              <span class="badge badge-si"><?= h($c['buona_nome']) ?></span>
              <span class="badge badge-no"><?= h($c['cattiva_nome']) ?></span>
            </div>
            <span class="vedi" style="margin-top:.7rem;">Vedi il confronto →</span>
          </div>
        </a>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
