<?php
/**
 * Sitemap XML generale (pagine, non immagini — per quella vedi
 * sitemap-immagini.php) di tutte le pagine pubbliche del sito. Si
 * aggiorna da sola: include automaticamente ogni specie presente nel
 * database. Le pagine di login e l'area amministratore non compaiono,
 * volutamente: non sono contenuto da indicizzare.
 */
require_once __DIR__ . '/includes/config.php';

header('Content-Type: application/xml; charset=UTF-8');

$pdo = db();
$specie = $pdo->query("SELECT id, creato_il FROM specie ORDER BY id")->fetchAll();
$ricette = $pdo->query("SELECT id, creato_il FROM ricetta ORDER BY id")->fetchAll();
$confronti = $pdo->query("SELECT id, creato_il FROM confronto ORDER BY id")->fetchAll();

function xml_escape(string $s): string
{
    return htmlspecialchars($s, ENT_XML1 | ENT_QUOTES, 'UTF-8');
}

// changefreq/priority sono indicazioni di massima per i motori di ricerca,
// non una promessa vincolante: vanno bene valori approssimativi.
$paginePrincipali = [
    ['/index.php', 'weekly', '1.0'],
    ['/database.php', 'weekly', '0.9'],
    ['/cucina.php', 'weekly', '0.8'],
    ['/confronti.php', 'monthly', '0.7'],
    ['/eventi.php', 'weekly', '0.8'],
    ['/calendario.php', 'weekly', '0.6'],
    ['/corso.php', 'monthly', '0.7'],
    ['/micologia.php', 'monthly', '0.6'],
    ['/micotossicologia.php', 'monthly', '0.6'],
    ['/legislazione.php', 'monthly', '0.6'],
    ['/riviste.php', 'monthly', '0.6'],
    ['/consiglio-direttivo.php', 'yearly', '0.4'],
    ['/statuto.php', 'yearly', '0.3'],
    ['/privacy-cookie.php', 'yearly', '0.2'],
];

echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">

<?php foreach ($paginePrincipali as [$percorso, $freq, $priorita]): ?>
  <url>
    <loc><?= xml_escape(URL_SITO . $percorso) ?></loc>
    <changefreq><?= $freq ?></changefreq>
    <priority><?= $priorita ?></priority>
  </url>
<?php endforeach; ?>

<?php foreach ($specie as $s): ?>
  <url>
    <loc><?= xml_escape(URL_SITO . '/specie.php?id=' . $s['id']) ?></loc>
<?php if (!empty($s['creato_il'])): ?>
    <lastmod><?= xml_escape(date('Y-m-d', strtotime($s['creato_il']))) ?></lastmod>
<?php endif; ?>
    <changefreq>monthly</changefreq>
    <priority>0.7</priority>
  </url>
<?php endforeach; ?>

<?php foreach ($ricette as $r): ?>
  <url>
    <loc><?= xml_escape(URL_SITO . '/ricetta.php?id=' . $r['id']) ?></loc>
<?php if (!empty($r['creato_il'])): ?>
    <lastmod><?= xml_escape(date('Y-m-d', strtotime($r['creato_il']))) ?></lastmod>
<?php endif; ?>
    <changefreq>monthly</changefreq>
    <priority>0.6</priority>
  </url>
<?php endforeach; ?>

<?php foreach ($confronti as $c): ?>
  <url>
    <loc><?= xml_escape(URL_SITO . '/confronto.php?id=' . $c['id']) ?></loc>
<?php if (!empty($c['creato_il'])): ?>
    <lastmod><?= xml_escape(date('Y-m-d', strtotime($c['creato_il']))) ?></lastmod>
<?php endif; ?>
    <changefreq>monthly</changefreq>
    <priority>0.6</priority>
  </url>
<?php endforeach; ?>

</urlset>
