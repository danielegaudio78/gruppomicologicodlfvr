<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/functions.php';

// Per ogni specie prendiamo la prima foto (copertina) da mostrare nella card.
$specie = db()->query("
    SELECT s.*, (
        SELECT f.id FROM foto f WHERE f.specie_id = s.id ORDER BY f.ordine, f.id LIMIT 1
    ) AS copertina_id
    FROM specie s
    ORDER BY s.nome_comune
")->fetchAll();

$pageTitle = 'Database funghi — ' . NOME_CIRCOLO;
$metaDescription = 'Database fotografico delle specie fungine schedate dal circolo: commestibilità, habitat, periodo di crescita e caratteristiche morfologiche.';
require_once __DIR__ . '/includes/header.php';
?>

<section class="section section-light" style="padding-top:7.5rem;">
  <div class="eyebrow">Archivio specie</div>
  <h2>Database funghi</h2>
  <p class="lead">Ogni scheda nasce da un ritrovamento verificato dal circolo: fotografie, dati tassonomici e, quando
  disponibile, la scheda tecnica completa in PDF. Filtra per genere oppure cerca per nome comune, scientifico o un
  sinonimo/nome alternativo.</p>

  <div class="filters" id="filterBar">
    <button class="chip active" data-gruppo="tutti">Tutti i generi</button>
    <?php foreach (gruppi_tassonomici() as $g): ?>
      <button class="chip" data-gruppo="<?= h($g) ?>"><?= h($g) ?></button>
    <?php endforeach; ?>
    <div class="search-box"><input type="text" id="searchInput" placeholder="Cerca per nome comune, scientifico o alternativo…"></div>
  </div>

  <?php if (empty($specie)): ?>
    <div class="fungo-card" style="padding:2rem;text-align:center;color:#8a8267;">Nessuna specie ancora pubblicata.</div>
  <?php else: ?>
    <div class="fungo-grid">
      <?php foreach ($specie as $i => $f): ?>
        <div class="fungo-card" data-gruppo="<?= h($f['gruppo'] ?? '') ?>" data-nome="<?= h($f['nome_comune']) ?>" data-scientifico="<?= h($f['nome_scientifico']) ?>" data-alternativi="<?= h($f['nomi_alternativi'] ?? '') ?>">
          <div class="fungo-photo protetta-wrap watermarked">
            <?php if ($f['copertina_id']): ?>
              <img class="protetta" src="/serve_image.php?id=<?= (int)$f['copertina_id'] ?>" alt="<?= h($f['nome_comune']) ?>" loading="lazy">
            <?php else: ?>
              <div class="placeholder">Foto non ancora caricata</div>
            <?php endif; ?>
          </div>
          <div class="fungo-body">
            <h3><?= h($f['nome_comune']) ?></h3>
            <span class="fungo-sci"><?= h($f['nome_scientifico']) ?></span>
            <br>
            <span class="badge <?= classe_commestibilita($f['commestibilita']) ?>"><?= etichetta_commestibilita($f['commestibilita']) ?></span>
            <?php if (!empty($f['gruppo'])): ?><span class="badge badge-verifica"><?= h($f['gruppo']) ?></span><?php endif; ?>
            <br>
            <button type="button" class="scheda-toggle">Scheda tecnica ▾</button>
            <div class="scheda-dettaglio">
              <dl class="scheda-dl">
                <?php if (!empty($f['nomi_alternativi'])): ?><dt>Detto anche</dt><dd><?= h($f['nomi_alternativi']) ?></dd><?php endif; ?>
                <?php if ($f['habitat']): ?><dt>Habitat</dt><dd><?= h($f['habitat']) ?></dd><?php endif; ?>
                <?php if ($f['periodo']): ?><dt>Periodo</dt><dd><?= h($f['periodo']) ?></dd><?php endif; ?>
                <?php if ($f['cappello']): ?><dt>Cappello</dt><dd><?= h($f['cappello']) ?></dd><?php endif; ?>
                <?php if ($f['gambo']): ?><dt>Gambo</dt><dd><?= h($f['gambo']) ?></dd><?php endif; ?>
                <?php if ($f['imenio']): ?><dt>Imenio</dt><dd><?= h($f['imenio']) ?></dd><?php endif; ?>
                <?php if ($f['spore']): ?><dt>Spore</dt><dd><?= h($f['spore']) ?></dd><?php endif; ?>
                <?php if ($f['note']): ?><dt>Note</dt><dd><?= h($f['note']) ?></dd><?php endif; ?>
              </dl>
            </div>
            <br>
            <a class="vedi" href="/specie.php?id=<?= (int)$f['id'] ?>">Vedi la pagina completa (foto e PDF) →</a>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
