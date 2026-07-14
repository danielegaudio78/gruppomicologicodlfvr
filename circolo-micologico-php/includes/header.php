<?php
/**
 * Header condiviso da tutte le pagine pubbliche.
 * Variabili opzionali da impostare PRIMA dell'include:
 * - $pageTitle: titolo della pagina (tag <title> e Open Graph)
 * - $metaDescription: descrizione unica della pagina, 120-160 caratteri
 *   circa. Ogni pagina dovrebbe avere la propria per non generare
 *   descrizioni duplicate agli occhi di Google.
 * - $ogImage: URL assoluto di un'immagine rappresentativa (facoltativo,
 *   altrimenti si usa un'icona generica del circolo).
 */
$pageTitle = $pageTitle ?? NOME_CIRCOLO;
$metaDescription = $metaDescription ?? 'Circolo micologico: database delle specie fungine con schede tecniche, corso di micologia, calendario uscite, legislazione regionale e attività sociali.';
$ogImage = $ogImage ?? (URL_SITO . '/favicon.svg');

// URL canonico della pagina corrente, per evitare contenuti duplicati
// agli occhi di Google (es. la stessa pagina raggiungibile con parametri
// diversi). Costruito da URL_SITO + percorso richiesto, senza parametri
// di tracciamento estranei (utm_*, fbclid, ecc.) che non fanno parte del
// contenuto vero e proprio.
$percorsoCorrente = strtok($_SERVER['REQUEST_URI'] ?? '/', '?');
$queryPulita = [];
if (!empty($_GET)) {
    foreach ($_GET as $k => $v) {
        if (!preg_match('/^(utm_|fbclid|gclid)/i', $k)) $queryPulita[$k] = $v;
    }
}
$urlCanonico = URL_SITO . $percorsoCorrente . (!empty($queryPulita) ? '?' . http_build_query($queryPulita) : '');
?>
<!DOCTYPE html>
<html lang="it">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= h($pageTitle) ?></title>
<meta name="description" content="<?= h($metaDescription) ?>">
<link rel="canonical" href="<?= h($urlCanonico) ?>">

<!-- Open Graph / condivisione social -->
<meta property="og:type" content="website">
<meta property="og:site_name" content="<?= h(NOME_CIRCOLO) ?>">
<meta property="og:title" content="<?= h($pageTitle) ?>">
<meta property="og:description" content="<?= h($metaDescription) ?>">
<meta property="og:url" content="<?= h($urlCanonico) ?>">
<meta property="og:image" content="<?= h($ogImage) ?>">
<meta property="og:locale" content="it_IT">
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="<?= h($pageTitle) ?>">
<meta name="twitter:description" content="<?= h($metaDescription) ?>">
<meta name="twitter:image" content="<?= h($ogImage) ?>">

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Fraunces:ital,opsz,wght@0,9..144,300;0,9..144,500;0,9..144,600;0,9..144,700;1,9..144,500&family=Work+Sans:wght@300;400;500;600&family=IBM+Plex+Mono:ital,wght@0,400;0,500;1,400&display=swap" rel="stylesheet">
<link rel="stylesheet" href="/assets/css/style.css">
<link rel="icon" href="/favicon.svg" type="image/svg+xml">

<!-- Dati strutturati: identifica il circolo come organizzazione presso i
     motori di ricerca (nome, indirizzo, contatti, social) -->
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "Organization",
  "name": <?= json_encode(NOME_CIRCOLO, JSON_UNESCAPED_UNICODE) ?>,
  "url": <?= json_encode(URL_SITO, JSON_UNESCAPED_UNICODE) ?>,
  "email": <?= json_encode(EMAIL_CIRCOLO, JSON_UNESCAPED_UNICODE) ?>,
  "address": {
    "@type": "PostalAddress",
    "streetAddress": <?= json_encode(INDIRIZZO_CIRCOLO, JSON_UNESCAPED_UNICODE) ?>
  },
  "sameAs": [
    <?= json_encode(SOCIAL_FACEBOOK, JSON_UNESCAPED_UNICODE) ?>,
    <?= json_encode(SOCIAL_INSTAGRAM, JSON_UNESCAPED_UNICODE) ?>,
    <?= json_encode(SOCIAL_YOUTUBE, JSON_UNESCAPED_UNICODE) ?>
  ]
}
</script>

<?php
// Pattern di filigrana: un piccolo SVG ripetuto a piastrelle con il nome
// del circolo e il dominio del sito, così chi trova la foto (anche fuori
// dal sito, es. su Google Immagini) sa sempre a cosa è riconducibile.
// Generato una sola volta per pagina e riusato da tutte le immagini con
// la classe .watermarked (vedi assets/css/style.css).
$wmDominio = preg_replace('#^https?://#', '', rtrim(URL_SITO, '/'));
$wmSvg = '<svg xmlns="http://www.w3.org/2000/svg" width="300" height="300">'
    . '<text x="-20" y="128" transform="rotate(-28 150,150)" '
    . 'font-family="IBM Plex Mono, monospace" font-size="15" letter-spacing="1" '
    . 'fill="rgba(255,255,255,0.5)" stroke="rgba(0,0,0,0.35)" stroke-width="0.4">'
    . htmlspecialchars(NOME_CIRCOLO) . '</text>'
    . '<text x="-20" y="150" transform="rotate(-28 150,150)" '
    . 'font-family="IBM Plex Mono, monospace" font-size="12" letter-spacing="1" '
    . 'fill="rgba(255,255,255,0.45)" stroke="rgba(0,0,0,0.3)" stroke-width="0.3">'
    . htmlspecialchars($wmDominio) . '</text></svg>';
$wmDataUri = 'data:image/svg+xml,' . rawurlencode($wmSvg);
?>
<style>:root{--wm-pattern:url("<?= $wmDataUri ?>");}</style>
</head>
<body>

<nav class="site">
  <a class="logo" href="/index.php">
    <svg viewBox="0 0 40 40" fill="none"><circle cx="20" cy="20" r="18" fill="none" stroke="#b6a9c9" stroke-width="1.2"/><circle cx="20" cy="20" r="12" fill="none" stroke="#b6a9c9" stroke-width="1" opacity=".7"/><circle cx="20" cy="20" r="6" fill="#b6a9c9" opacity=".8"/></svg>
    <span>Sottobosco</span>
  </a>
  <button class="nav-toggle" id="navToggle" aria-label="Apri menu">☰</button>
  <ul class="nav-links" id="navLinks">
    <li class="nav-dropdown">
      <details>
        <summary>Chi siamo</summary>
        <div class="nav-dropdown-menu">
          <a href="/index.php#chi-siamo">Il circolo</a>
          <a href="/consiglio-direttivo.php">Consiglio direttivo</a>
          <a href="/statuto.php">Statuto e regolamento</a>
        </div>
      </details>
    </li>
    <li><a href="/database.php">Database funghi</a></li>
    <li><a href="/cucina.php">In cucina</a></li>
    <li><a href="/eventi.php">Eventi</a></li>
    <li class="nav-dropdown">
      <details>
        <summary>Risorse</summary>
        <div class="nav-dropdown-menu">
          <a href="/corso.php">Corso di micologia</a>
          <a href="/confronti.php">Fungo buono, fungo cattivo</a>
          <a href="/micologia.php">Micologia</a>
          <a href="/micotossicologia.php">Micotossicologia</a>
          <a href="/legislazione.php">Legislazione regionale</a>
          <a href="/riviste.php">Riviste</a>
        </div>
      </details>
    </li>
    <?php if (is_socio()): ?>
      <li class="nav-dropdown">
        <details>
          <summary>Area soci</summary>
          <div class="nav-dropdown-menu">
            <a href="/soci/bacheca.php">Bacheca soci</a>
            <a href="/soci/galleria.php">Galleria fotografica</a>
            <a href="/soci/logout.php">Esci</a>
          </div>
        </details>
      </li>
    <?php else: ?>
      <li><a href="/soci/login.php">Area soci</a></li>
    <?php endif; ?>
    <li><a href="/index.php#contatti">Contatti</a></li>
    <?php if (is_admin()): ?>
      <li><a href="/admin/dashboard.php" class="btn btn-primary btn-sm">Pannello admin</a></li>
    <?php else: ?>
      <li><a href="/admin/login.php" style="opacity:.6;font-size:.78rem;">Accesso amministratore</a></li>
    <?php endif; ?>
  </ul>
</nav>
