<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/functions.php';

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$id) {
    header('Location: /cucina.php');
    exit;
}

$stmt = db()->prepare('SELECT * FROM ricetta WHERE id = ?');
$stmt->execute([$id]);
$r = $stmt->fetch();

if (!$r) {
    header('Location: /cucina.php');
    exit;
}

$pageTitle = $r['titolo'] . ' — ' . NOME_CIRCOLO;
$metaDescription = riassumi_testo(
    $r['titolo'] . ($r['funghi_utilizzati'] ? ' con ' . $r['funghi_utilizzati'] : '')
    . ($r['abbinamento_vino'] ? '. Abbinamento consigliato: ' . $r['abbinamento_vino'] : ''),
    160
);
if ($r['foto_path']) {
    $ogImage = URL_SITO . '/serve_ricetta_foto.php?id=' . $r['id'];
}
require_once __DIR__ . '/includes/header.php';

// Elenco ingredienti e passaggi come array, per il testo e per i dati
// strutturati Recipe (Google può mostrarla nei risultati ricetta).
$listaIngredienti = array_values(array_filter(array_map('trim', explode("\n", $r['ingredienti'] ?? ''))));
$listaPassaggi = array_values(array_filter(array_map('trim', preg_split('/\n{2,}|\r\n\r\n/', $r['procedimento'] ?? ''))));
?>

<?php if (!empty($listaIngredienti) || !empty($listaPassaggi)): ?>
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "Recipe",
  "name": <?= json_encode($r['titolo'], JSON_UNESCAPED_UNICODE) ?>,
  "description": <?= json_encode($metaDescription, JSON_UNESCAPED_UNICODE) ?>,
  <?php if ($r['foto_path']): ?>"image": <?= json_encode(URL_SITO . '/serve_ricetta_foto.php?id=' . $r['id'], JSON_UNESCAPED_UNICODE) ?>,<?php endif; ?>
  <?php if ($r['categoria']): ?>"recipeCategory": <?= json_encode($r['categoria'], JSON_UNESCAPED_UNICODE) ?>,<?php endif; ?>
  "recipeCuisine": "Italiana",
  "author": { "@type": "Organization", "name": <?= json_encode(NOME_CIRCOLO, JSON_UNESCAPED_UNICODE) ?> },
  "recipeIngredient": <?= json_encode($listaIngredienti, JSON_UNESCAPED_UNICODE) ?>,
  "recipeInstructions": [
    <?php foreach ($listaPassaggi as $i => $passo): ?>
    { "@type": "HowToStep", "text": <?= json_encode($passo, JSON_UNESCAPED_UNICODE) ?> }<?= $i < count($listaPassaggi) - 1 ? ',' : '' ?>
    <?php endforeach; ?>
  ]
}
</script>
<?php endif; ?>

<section class="section section-light" style="padding-top:7.5rem;">
  <a href="/cucina.php" class="eyebrow" style="display:inline-block;margin-bottom:1.6rem;">← Torna a In cucina</a>

  <div class="specie-header">
    <div>
      <?php if ($r['foto_path']): ?>
        <div class="gallery-main protetta-wrap watermarked">
          <img class="protetta" src="/serve_ricetta_foto.php?id=<?= (int)$r['id'] ?>" alt="<?= h($r['titolo']) ?>">
        </div>
      <?php else: ?>
        <div class="gallery-main" style="display:flex;align-items:center;justify-content:center;color:#a89f86;font-family:var(--font-mono);font-size:.85rem;">
          Nessuna fotografia caricata per questa ricetta
        </div>
      <?php endif; ?>
    </div>

    <div>
      <?php if ($r['categoria']): ?><span class="badge badge-verifica"><?= h($r['categoria']) ?></span><?php endif; ?>
      <h1 style="font-family:var(--font-display);font-size:clamp(1.8rem,4vw,2.6rem);"><?= h($r['titolo']) ?></h1>
      <?php if ($r['funghi_utilizzati']): ?><p class="fungo-sci" style="font-size:1rem;">Con <?= h($r['funghi_utilizzati']) ?></p><?php endif; ?>

      <div style="display:flex;gap:.6rem;flex-wrap:wrap;margin:.8rem 0 1.2rem;">
        <?php if ($r['tempo_preparazione']): ?><span class="badge badge-verifica"><?= h($r['tempo_preparazione']) ?></span><?php endif; ?>
        <?php if ($r['difficolta']): ?><span class="badge badge-si"><?= h($r['difficolta']) ?></span><?php endif; ?>
      </div>

      <?php if ($r['abbinamento_vino']): ?>
        <div class="pdf-box" style="align-items:flex-start;">
          <div style="font-size:1.4rem;line-height:1;">🍷</div>
          <div>
            <div style="font-weight:500;margin-bottom:.2rem;">Abbinamento vino consigliato</div>
            <div style="font-size:.92rem;color:#4a4437;"><?= nl2br(h($r['abbinamento_vino'])) ?></div>
          </div>
        </div>
      <?php endif; ?>
    </div>
  </div>

  <div style="max-width:70ch;margin-top:3rem;">
    <?php if (!empty($listaIngredienti)): ?>
      <h3 style="font-size:1.3rem;margin-bottom:.8rem;">Ingredienti</h3>
      <ul style="margin-bottom:2.2rem;padding-left:1.3rem;color:#3a3427;line-height:1.8;">
        <?php foreach ($listaIngredienti as $ing): ?>
          <li><?= h($ing) ?></li>
        <?php endforeach; ?>
      </ul>
    <?php endif; ?>

    <?php if (!empty($listaPassaggi)): ?>
      <h3 style="font-size:1.3rem;margin-bottom:.8rem;">Procedimento</h3>
      <ol style="padding-left:1.3rem;color:#3a3427;line-height:1.8;">
        <?php foreach ($listaPassaggi as $passo): ?>
          <li style="margin-bottom:.8rem;"><?= h($passo) ?></li>
        <?php endforeach; ?>
      </ol>
    <?php endif; ?>
  </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
