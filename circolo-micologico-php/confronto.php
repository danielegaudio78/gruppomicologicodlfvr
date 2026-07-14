<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/functions.php';

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$id) {
    header('Location: /confronti.php');
    exit;
}

$stmt = db()->prepare('SELECT * FROM confronto WHERE id = ?');
$stmt->execute([$id]);
$c = $stmt->fetch();
if (!$c) {
    header('Location: /confronti.php');
    exit;
}

function carica_specie_confronto(PDO $pdo, int $id): ?array
{
    $stmt = $pdo->prepare('SELECT * FROM specie WHERE id = ?');
    $stmt->execute([$id]);
    $s = $stmt->fetch();
    if (!$s) return null;
    $stmt = $pdo->prepare('SELECT id FROM foto WHERE specie_id = ? ORDER BY ordine, id LIMIT 1');
    $stmt->execute([$id]);
    $s['foto_id'] = $stmt->fetchColumn() ?: null;
    return $s;
}

$buona = carica_specie_confronto(db(), (int) $c['specie_buona_id']);
$cattiva = carica_specie_confronto(db(), (int) $c['specie_cattiva_id']);

if (!$buona || !$cattiva) {
    header('Location: /confronti.php');
    exit;
}

$pageTitle = ($c['titolo'] ?: ($buona['nome_comune'] . ' vs ' . $cattiva['nome_comune'])) . ' — ' . NOME_CIRCOLO;
$metaDescription = riassumi_testo(
    'Confronto tra ' . $buona['nome_comune'] . ' (commestibile) e ' . $cattiva['nome_comune'] . ' (tossico): come riconoscere le differenze.',
    160
);
if ($buona['foto_id']) {
    $ogImage = URL_SITO . '/serve_image.php?id=' . $buona['foto_id'];
}
require_once __DIR__ . '/includes/header.php';

/** Renderizza la colonna di una specie nel confronto. */
function colonna_confronto(array $s, string $etichetta, string $classeBadge): void
{
    ?>
    <div>
      <span class="badge <?= $classeBadge ?>"><?= h($etichetta) ?></span>
      <?php if ($s['foto_id']): ?>
        <div class="gallery-main protetta-wrap watermarked" style="margin-top:.6rem;">
          <img class="protetta" src="/serve_image.php?id=<?= (int)$s['foto_id'] ?>" alt="<?= h($s['nome_comune']) ?>">
        </div>
      <?php else: ?>
        <div class="gallery-main" style="margin-top:.6rem;display:flex;align-items:center;justify-content:center;color:#a89f86;font-family:var(--font-mono);font-size:.85rem;">
          Nessuna fotografia caricata
        </div>
      <?php endif; ?>

      <h3 style="font-family:var(--font-display);font-size:1.5rem;margin-top:1rem;">
        <a href="/specie.php?id=<?= (int)$s['id'] ?>" style="border-bottom:1px dotted currentColor;"><?= h($s['nome_comune']) ?></a>
      </h3>
      <p class="fungo-sci" style="font-size:.95rem;"><?= h($s['nome_scientifico']) ?></p>
      <span class="badge <?= classe_commestibilita($s['commestibilita']) ?>"><?= etichetta_commestibilita($s['commestibilita']) ?></span>

      <dl class="scheda-dl">
        <?php
        $campi = [
            'Habitat' => $s['habitat'], 'Periodo' => $s['periodo'], 'Cappello' => $s['cappello'],
            'Gambo' => $s['gambo'], 'Imenio' => $s['imenio'], 'Spore' => $s['spore'],
        ];
        foreach ($campi as $label => $val):
            if (!$val) continue; ?>
          <dt><?= h($label) ?></dt><dd><?= nl2br(h($val)) ?></dd>
        <?php endforeach; ?>
      </dl>
    </div>
    <?php
}
?>

<section class="section section-light" style="padding-top:7.5rem;">
  <a href="/confronti.php" class="eyebrow" style="display:inline-block;margin-bottom:1.2rem;">← Tutti i confronti</a>
  <h1 style="font-family:var(--font-display);font-size:clamp(1.8rem,4vw,2.6rem);margin-bottom:2rem;">
    <?= h($c['titolo'] ?: ($buona['nome_comune'] . ' vs ' . $cattiva['nome_comune'])) ?>
  </h1>
</section>

<div class="safety">
  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><path d="M12 2 L22 20 L2 20 Z"/><line x1="12" y1="9" x2="12" y2="13"/><circle cx="12" cy="16.3" r=".6" fill="currentColor"/></svg>
  <div><b>Promemoria di sicurezza:</b> questo confronto è un supporto didattico, non un metodo di identificazione
  definitivo. Non consumare mai un fungo raccolto senza una determinazione certa da parte di un esperto micologo.</div>
</div>

<section class="section section-light">
  <div class="specie-header" style="align-items:start;">
    <?php colonna_confronto($buona, 'Fungo buono', 'badge-si'); ?>
    <?php colonna_confronto($cattiva, 'Fungo cattivo', 'badge-no'); ?>
  </div>

  <?php if (!empty($c['note_confronto'])): ?>
    <div style="max-width:75ch;margin:3rem auto 0;padding:1.8rem;background:var(--crema-2);border:1px solid #e3dcc7;border-radius:6px;">
      <div class="eyebrow" style="margin-bottom:.6rem;">Come distinguerli</div>
      <div style="color:#3a3427;line-height:1.75;"><?= nl2br(h($c['note_confronto'])) ?></div>
    </div>
  <?php endif; ?>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
