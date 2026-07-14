<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/functions.php';

$eventi = db()->query("SELECT * FROM eventi ORDER BY data_evento ASC")->fetchAll();

$pageTitle = 'Eventi — ' . NOME_CIRCOLO;
$metaDescription = 'Calendario di uscite micologiche, corsi di riconoscimento funghi e incontri del circolo, aperti a soci e simpatizzanti.';
require_once __DIR__ . '/includes/header.php';
?>

<?php if (!empty($eventi)): ?>
<script type="application/ld+json">
[
<?php foreach ($eventi as $i => $e): ?>
  {
    "@context": "https://schema.org",
    "@type": "Event",
    "name": <?= json_encode($e['titolo'], JSON_UNESCAPED_UNICODE) ?>,
    "startDate": <?= json_encode($e['data_evento'], JSON_UNESCAPED_UNICODE) ?>,
    "eventAttendanceMode": "https://schema.org/OfflineEventAttendanceMode",
    "eventStatus": "https://schema.org/EventScheduled",
    "description": <?= json_encode($e['descrizione'] ?: $e['titolo'], JSON_UNESCAPED_UNICODE) ?>,
    <?php if (!empty($e['immagine_path'])): ?>"image": <?= json_encode(URL_SITO . '/serve_evento_immagine.php?id=' . $e['id'], JSON_UNESCAPED_UNICODE) ?>,<?php endif; ?>
    "location": {
      "@type": "Place",
      "name": <?= json_encode(NOME_CIRCOLO, JSON_UNESCAPED_UNICODE) ?>,
      "address": <?= json_encode(INDIRIZZO_CIRCOLO, JSON_UNESCAPED_UNICODE) ?>
    },
    "organizer": { "@type": "Organization", "name": <?= json_encode(NOME_CIRCOLO, JSON_UNESCAPED_UNICODE) ?>, "url": <?= json_encode(URL_SITO, JSON_UNESCAPED_UNICODE) ?> }
  }<?= $i < count($eventi) - 1 ? ',' : '' ?>
<?php endforeach; ?>
]
</script>
<?php endif; ?>

<section class="section section-light" style="padding-top:7.5rem;">
  <div class="eyebrow">Calendario</div>
  <h2>Eventi del circolo</h2>
  <p class="lead">Uscite, corsi e incontri aperti a soci e simpatizzanti. <a href="/calendario.php" style="text-decoration:underline;">Vedi in formato calendario →</a></p>

  <?php if (empty($eventi)): ?>
    <p style="margin-top:2rem;color:#cfd3c2;">Nessun evento in programma al momento.</p>
  <?php else: ?>
    <div class="timeline">
      <?php foreach ($eventi as $e): ?>
        <div class="evento">
          <div class="data"><?= strtoupper(data_it($e['data_evento'])) ?></div>
          <h3><?= h($e['titolo']) ?></h3>
          <?php if (!empty($e['immagine_path'])): ?>
            <div class="protetta-wrap" style="max-width:420px;border-radius:6px;overflow:hidden;margin:.7rem 0;">
              <img class="protetta" src="/serve_evento_immagine.php?id=<?= (int)$e['id'] ?>" alt="<?= h($e['titolo']) ?>" style="width:100%;display:block;">
            </div>
          <?php endif; ?>
          <p><?= nl2br(h($e['descrizione'])) ?></p>
          <div style="display:flex;gap:1rem;align-items:center;flex-wrap:wrap;margin-top:.4rem;">
            <?php if ($e['tag']): ?><span class="tag"><?= h($e['tag']) ?></span><?php endif; ?>
            <?php if (!empty($e['allegato_path'])): ?>
              <a class="vedi" href="/serve_evento_allegato.php?id=<?= (int)$e['id'] ?>"><?= h($e['allegato_nome_originale'] ?: 'Scarica l\'allegato') ?> →</a>
            <?php endif; ?>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
