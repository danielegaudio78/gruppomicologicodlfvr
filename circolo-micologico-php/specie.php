<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/functions.php';

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$id) {
    header('Location: /database.php');
    exit;
}

$stmt = db()->prepare('SELECT * FROM specie WHERE id = ?');
$stmt->execute([$id]);
$f = $stmt->fetch();

if (!$f) {
    header('Location: /database.php');
    exit;
}

$stmt = db()->prepare('SELECT * FROM foto WHERE specie_id = ? ORDER BY ordine, id');
$stmt->execute([$id]);
$foto = $stmt->fetchAll();

$pageTitle = $f['nome_comune'] . ' (' . $f['nome_scientifico'] . ') — ' . NOME_CIRCOLO;

// Descrizione unica per ogni specie: usa la nota se c'è, altrimenti
// compone habitat/periodo/commestibilità. Ogni scheda ha così una
// meta description diversa dalle altre, invece di una generica ripetuta
// su tutte le pagine (fondamentale per l'indicizzazione di ogni fungo).
if (!empty($f['note'])) {
    $metaDescription = riassumi_testo($f['nome_comune'] . ' (' . $f['nome_scientifico'] . '): ' . $f['note'], 160);
} else {
    $etichettaCom = etichetta_commestibilita($f['commestibilita']);
    $metaDescription = riassumi_testo(
        $f['nome_comune'] . ' (' . $f['nome_scientifico'] . '), ' . strtolower($etichettaCom)
        . ($f['habitat'] ? '. Habitat: ' . $f['habitat'] : '')
        . ($f['periodo'] ? '. Periodo: ' . $f['periodo'] : ''),
        160
    );
}
if (!empty($foto)) {
    $ogImage = URL_SITO . '/serve_image.php?id=' . $foto[0]['id'];
}
require_once __DIR__ . '/includes/header.php';
?>

<?php if (!empty($f['nome_comune'])): ?>
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "Article",
  "headline": <?= json_encode($f['nome_comune'] . ' (' . $f['nome_scientifico'] . ')', JSON_UNESCAPED_UNICODE) ?>,
  "description": <?= json_encode($metaDescription, JSON_UNESCAPED_UNICODE) ?>,
  <?php if (!empty($foto)): ?>"image": <?= json_encode(URL_SITO . '/serve_image.php?id=' . $foto[0]['id'], JSON_UNESCAPED_UNICODE) ?>,<?php endif; ?>
  "author": { "@type": "Organization", "name": <?= json_encode(NOME_CIRCOLO, JSON_UNESCAPED_UNICODE) ?> },
  "publisher": { "@type": "Organization", "name": <?= json_encode(NOME_CIRCOLO, JSON_UNESCAPED_UNICODE) ?> }
}
</script>
<?php endif; ?>

<section class="section section-light" style="padding-top:7.5rem;">
  <a href="/database.php" class="eyebrow" style="display:inline-block;margin-bottom:1.6rem;">← Torna al database</a>

  <div class="specie-header">
    <div>
      <?php if (!empty($foto)): ?>
        <div class="gallery-main protetta-wrap watermarked">
          <img id="galleryMain" class="protetta" src="/serve_image.php?id=<?= (int)$foto[0]['id'] ?>" alt="<?= h($f['nome_comune']) ?>">
        </div>
        <?php if (count($foto) > 1): ?>
          <div class="gallery-thumbs">
            <?php foreach ($foto as $i => $ph): ?>
              <button class="protetta-wrap watermarked <?= $i === 0 ? 'active' : '' ?>" data-full="/serve_image.php?id=<?= (int)$ph['id'] ?>">
                <img class="protetta" src="/serve_image.php?id=<?= (int)$ph['id'] ?>" alt="Foto <?= $i + 1 ?> di <?= h($f['nome_comune']) ?>" loading="lazy">
              </button>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      <?php else: ?>
        <div class="gallery-main" style="display:flex;align-items:center;justify-content:center;color:#a89f86;font-family:var(--font-mono);font-size:.85rem;">
          Nessuna fotografia caricata per questa specie
        </div>
      <?php endif; ?>
    </div>

    <div>
      <span class="badge <?= classe_commestibilita($f['commestibilita']) ?>"><?= etichetta_commestibilita($f['commestibilita']) ?></span>
      <h1 style="font-family:var(--font-display);font-size:clamp(1.8rem,4vw,2.6rem);"><?= h($f['nome_comune']) ?></h1>
      <p class="fungo-sci" style="font-size:1rem;"><?= h($f['nome_scientifico']) ?></p>

      <dl class="scheda-dl">
        <?php
        $campi = [
            'Detto anche' => $f['nomi_alternativi'] ?? '', 'Gruppo' => $f['gruppo'] ?? '', 'Habitat' => $f['habitat'], 'Periodo' => $f['periodo'], 'Cappello' => $f['cappello'],
            'Gambo' => $f['gambo'], 'Imenio' => $f['imenio'], 'Spore' => $f['spore'], 'Note' => $f['note'],
        ];
        foreach ($campi as $label => $val):
            if (!$val) continue; ?>
          <dt><?= h($label) ?></dt><dd><?= nl2br(h($val)) ?></dd>
        <?php endforeach; ?>
      </dl>

      <?php if ($f['pdf_path']): ?>
        <div class="pdf-box">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M6 2h9l5 5v15H6Z"/><path d="M15 2v5h5"/></svg>
          <div>
            <div style="font-weight:500;">Scheda tecnica completa (PDF)</div>
            <a href="/serve_pdf.php?specie_id=<?= (int)$f['id'] ?>" target="_blank" rel="noopener" class="vedi">Apri la scheda →</a>
          </div>
        </div>
      <?php endif; ?>
    </div>
  </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
