<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/functions.php';

function leggi_pagina_cms(PDO $pdo, string $slug): ?array
{
    $stmt = $pdo->prepare('SELECT * FROM pagine_cms WHERE slug = ?');
    $stmt->execute([$slug]);
    $r = $stmt->fetch();
    return $r ?: null;
}

$ciclo = leggi_pagina_cms(db(), 'micologia_ciclo');
$nutrizione = leggi_pagina_cms(db(), 'micologia_nutrizione');

$pageTitle = 'Micologia — ' . NOME_CIRCOLO;
$metaDescription = 'Il ciclo riproduttivo dei funghi e i loro sistemi nutrizionali spiegati dal circolo micologico.';
require_once __DIR__ . '/includes/header.php';
?>

<section class="section section-light" style="padding-top:7.5rem;">
  <div class="eyebrow">Biologia del fungo</div>
  <h2>Micologia</h2>
  <p class="lead">Le nozioni di base per capire cosa raccogliamo davvero quando troviamo un fungo nel sottobosco.</p>

  <?php if ($ciclo): ?>
    <div style="margin-top:2.6rem;">
      <h3 style="font-size:1.4rem;margin-bottom:.8rem;"><?= h($ciclo['titolo']) ?></h3>
      <div class="lead" style="max-width:70ch;color:#3a3427;"><?= nl2br(h($ciclo['contenuto'])) ?></div>
    </div>
  <?php endif; ?>

  <?php if ($nutrizione): ?>
    <div style="margin-top:2.6rem;">
      <h3 style="font-size:1.4rem;margin-bottom:.8rem;"><?= h($nutrizione['titolo']) ?></h3>
      <div class="lead" style="max-width:70ch;color:#3a3427;"><?= nl2br(h($nutrizione['contenuto'])) ?></div>
    </div>
  <?php endif; ?>

  <?php if (!$ciclo && !$nutrizione): ?>
    <div class="empty-state">Contenuti non ancora pubblicati.</div>
  <?php endif; ?>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
