<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/functions.php';

// Foto del carosello: tabella indipendente dal database dei funghi,
// gestita dall'amministratore in /admin/home_slides.php.
$carosello = db()->query("SELECT id, path FROM home_slide ORDER BY ordine, id LIMIT 10")->fetchAll();

$pageTitle = NOME_CIRCOLO;
$metaDescription = 'Circolo micologico: database delle specie fungine con schede tecniche fotografiche, corso di micologia annuale, calendario uscite ed eventi.';
require_once __DIR__ . '/includes/header.php';

$chiSiamo = db()->query('SELECT * FROM pagina_chi_siamo WHERE id = 1')->fetch();
?>

<header class="hero-carousel">
  <?php if (empty($carosello)): ?>
    <div class="hero-empty">
      <div>
        <div class="eyebrow">Circolo Micologico</div>
        <h1 style="font-family:var(--font-display);font-size:clamp(2rem,5vw,3.4rem);">Benvenuti nel Circolo Micologico</h1>
        <p style="margin-top:1rem;color:#cfd3c2;">Le fotografie del carosello appariranno qui non appena l'amministratore
        caricherà le prime foto dal <a href="/admin/home_slides.php" style="text-decoration:underline;">pannello riservato</a>.</p>
      </div>
    </div>
  <?php else: ?>
    <?php foreach ($carosello as $i => $s): ?>
      <div class="hero-slide protetta-wrap watermarked <?= $i === 0 ? 'active' : '' ?>">
        <img class="ph protetta" src="/serve_slide.php?id=<?= (int)$s['id'] ?>" alt="Fotografia del circolo">
      </div>
    <?php endforeach; ?>
    <div class="hero-caption">
      <div class="eyebrow">Circolo Micologico · dal 1987</div>
      <h1>Il fungo si legge,<br>non si indovina.</h1>
      <div class="hero-ctas">
        <a href="/database.php" class="btn btn-primary">Esplora il database →</a>
        <a href="/eventi.php" class="btn btn-ghost">Prossimi eventi</a>
      </div>
    </div>
    <div class="hero-dots">
      <?php foreach ($carosello as $i => $s): ?>
        <button class="<?= $i === 0 ? 'active' : '' ?>" aria-label="Foto <?= $i + 1 ?>"></button>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</header>

<div class="safety">
  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><path d="M12 2 L22 20 L2 20 Z"/><line x1="12" y1="9" x2="12" y2="13"/><circle cx="12" cy="16.3" r=".6" fill="currentColor"/></svg>
  <div><b>Promemoria di sicurezza:</b> nessuna scheda di questo sito sostituisce il controllo micologico presso l'ASL o un esperto autorizzato. Non consumare mai un fungo raccolto senza una determinazione certa.</div>
</div>

<section class="section section-light" id="chi-siamo">
  <div class="chi-siamo">
    <div>
      <div class="eyebrow">Chi siamo</div>
      <h2>Un circolo, un bosco,<br>un archivio condiviso.</h2>
      <p class="lead"><?= nl2br(h($chiSiamo['testo'] ?? "Sottobosco nasce come punto d'incontro tra chi cammina nei boschi da una vita e chi si affaccia oggi alla micologia.")) ?></p>
      <div class="stat-row">
        <div class="stat"><b><?= h($chiSiamo['soci_numero'] ?? '—') ?></b><span><?= h($chiSiamo['soci_etichetta'] ?? 'Soci attivi') ?></span></div>
        <div class="stat"><b><?= (int)db()->query('SELECT COUNT(*) FROM specie')->fetchColumn() ?></b><span>Specie schedate</span></div>
        <div class="stat"><b><?= h($chiSiamo['anni_numero'] ?? '—') ?></b><span><?= h($chiSiamo['anni_etichetta'] ?? 'Anni di attività') ?></span></div>
      </div>
    </div>
    <div style="text-align:center;">
      <?php if (!empty($chiSiamo['foto_path'])): ?>
        <div class="illus-card protetta-wrap watermarked" style="padding:0;overflow:hidden;">
          <img class="protetta" src="/serve_chi_siamo_foto.php" alt="<?= h(NOME_CIRCOLO) ?>" style="width:100%;display:block;aspect-ratio:4/3;object-fit:cover;">
        </div>
        <a href="/database.php" class="btn btn-primary" style="margin-top:1.4rem;">Consulta il database completo →</a>
      <?php else: ?>
        <a href="/database.php" class="btn btn-primary">Consulta il database completo →</a>
      <?php endif; ?>
    </div>
  </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
