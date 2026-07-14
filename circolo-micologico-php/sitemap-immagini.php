<?php
/**
 * Sitemap XML dedicata alle immagini (formato "Image sitemap" di Google),
 * per aiutare Google Immagini a trovare e indicizzare tutte le fotografie
 * pubbliche del sito, associate alla pagina su cui compaiono.
 *
 * Si aggiorna da sola: non serve editarla a mano, legge il database ad
 * ogni richiesta. Va indicata in robots.txt con la riga "Sitemap:" e può
 * essere inviata manualmente da Google Search Console.
 */
require_once __DIR__ . '/includes/config.php';

header('Content-Type: application/xml; charset=UTF-8');

$pdo = db();

// specie_id => elenco foto, tutte associate alla pagina di quella specie
$specieFoto = [];
foreach ($pdo->query("SELECT specie_id, id FROM foto ORDER BY specie_id, ordine") as $r) {
    $specieFoto[$r['specie_id']][] = $r['id'];
}
$specie = $pdo->query("SELECT id, nome_comune FROM specie WHERE id IN (SELECT DISTINCT specie_id FROM foto)")->fetchAll();

// membri del consiglio con foto, associati a /consiglio-direttivo.php
$consiglio = $pdo->query("SELECT id, nome FROM consiglio WHERE foto_path IS NOT NULL")->fetchAll();

// eventi con immagine, associati a /eventi.php
$eventi = $pdo->query("SELECT id, titolo FROM eventi WHERE immagine_path IS NOT NULL")->fetchAll();

// ricette con foto, ciascuna associata alla propria pagina di dettaglio
$ricette = $pdo->query("SELECT id, titolo FROM ricetta WHERE foto_path IS NOT NULL")->fetchAll();

// foto della sezione "Chi siamo" (riga singola), associata alla home
$chiSiamo = $pdo->query("SELECT foto_path FROM pagina_chi_siamo WHERE id = 1 AND foto_path IS NOT NULL")->fetch();

function xml_escape(string $s): string
{
    return htmlspecialchars($s, ENT_XML1 | ENT_QUOTES, 'UTF-8');
}

echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"
        xmlns:image="http://www.google.com/schemas/sitemap-image/1.1">

<?php foreach ($specie as $s): ?>
  <url>
    <loc><?= xml_escape(URL_SITO . '/specie.php?id=' . $s['id']) ?></loc>
<?php foreach ($specieFoto[$s['id']] as $fotoId): ?>
    <image:image>
      <image:loc><?= xml_escape(URL_SITO . '/serve_image.php?id=' . $fotoId) ?></image:loc>
      <image:title><?= xml_escape($s['nome_comune']) ?></image:title>
    </image:image>
<?php endforeach; ?>
  </url>
<?php endforeach; ?>

<?php if (!empty($consiglio)): ?>
  <url>
    <loc><?= xml_escape(URL_SITO . '/consiglio-direttivo.php') ?></loc>
<?php foreach ($consiglio as $m): ?>
    <image:image>
      <image:loc><?= xml_escape(URL_SITO . '/serve_consiglio_foto.php?id=' . $m['id']) ?></image:loc>
      <image:title><?= xml_escape($m['nome']) ?></image:title>
    </image:image>
<?php endforeach; ?>
  </url>
<?php endif; ?>

<?php if (!empty($eventi)): ?>
  <url>
    <loc><?= xml_escape(URL_SITO . '/eventi.php') ?></loc>
<?php foreach ($eventi as $e): ?>
    <image:image>
      <image:loc><?= xml_escape(URL_SITO . '/serve_evento_immagine.php?id=' . $e['id']) ?></image:loc>
      <image:title><?= xml_escape($e['titolo']) ?></image:title>
    </image:image>
<?php endforeach; ?>
  </url>
<?php endif; ?>

<?php if (!empty($ricette)): ?>
<?php foreach ($ricette as $r): ?>
  <url>
    <loc><?= xml_escape(URL_SITO . '/ricetta.php?id=' . $r['id']) ?></loc>
    <image:image>
      <image:loc><?= xml_escape(URL_SITO . '/serve_ricetta_foto.php?id=' . $r['id']) ?></image:loc>
      <image:title><?= xml_escape($r['titolo']) ?></image:title>
    </image:image>
  </url>
<?php endforeach; ?>
<?php endif; ?>

<?php if (!empty($chiSiamo)): ?>
  <url>
    <loc><?= xml_escape(URL_SITO . '/index.php') ?></loc>
    <image:image>
      <image:loc><?= xml_escape(URL_SITO . '/serve_chi_siamo_foto.php') ?></image:loc>
      <image:title><?= xml_escape(NOME_CIRCOLO) ?></image:title>
    </image:image>
  </url>
<?php endif; ?>

</urlset>
