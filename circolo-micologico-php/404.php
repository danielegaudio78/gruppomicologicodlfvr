<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/functions.php';

http_response_code(404);

$pageTitle = 'Pagina non trovata — ' . NOME_CIRCOLO;
$metaDescription = 'La pagina richiesta non esiste o è stata spostata.';
require_once __DIR__ . '/includes/header.php';
?>

<section class="section section-light" style="padding-top:9rem;padding-bottom:8rem;text-align:center;">
  <div class="eyebrow">Errore 404</div>
  <h2>Questa pagina non esiste</h2>
  <p class="lead" style="margin:0 auto 2rem;">Il link potrebbe essere sbagliato o la pagina è stata spostata.
  Prova a ripartire da qui:</p>
  <div class="hero-ctas" style="justify-content:center;">
    <a href="/index.php" class="btn btn-primary">Torna alla home</a>
    <a href="/database.php" class="btn btn-ghost">Database funghi</a>
  </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
