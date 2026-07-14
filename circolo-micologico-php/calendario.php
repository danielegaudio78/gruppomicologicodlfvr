<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/functions.php';

$oggi = new DateTime();
$anno = filter_input(INPUT_GET, 'anno', FILTER_VALIDATE_INT) ?: (int) $oggi->format('Y');
$mese = filter_input(INPUT_GET, 'mese', FILTER_VALIDATE_INT) ?: (int) $oggi->format('n');
if ($mese < 1) { $mese = 12; $anno--; }
if ($mese > 12) { $mese = 1; $anno++; }

$primoGiorno = new DateTime(sprintf('%04d-%02d-01', $anno, $mese));
$giorniNelMese = (int) $primoGiorno->format('t');
// Colonne lunedì-domenica: 'N' restituisce 1 (lunedì) - 7 (domenica).
$offset = (int) $primoGiorno->format('N') - 1;

$meseNome = [
    1=>'Gennaio',2=>'Febbraio',3=>'Marzo',4=>'Aprile',5=>'Maggio',6=>'Giugno',
    7=>'Luglio',8=>'Agosto',9=>'Settembre',10=>'Ottobre',11=>'Novembre',12=>'Dicembre',
][$mese];

$stmt = db()->prepare("SELECT * FROM eventi WHERE strftime('%Y-%m', data_evento) = ? ORDER BY data_evento");
$stmt->execute([sprintf('%04d-%02d', $anno, $mese)]);
$eventiMese = $stmt->fetchAll();

$eventiPerGiorno = [];
foreach ($eventiMese as $e) {
    $g = (int) date('j', strtotime($e['data_evento']));
    $eventiPerGiorno[$g][] = $e;
}

$meseScorso = $mese - 1; $annoScorso = $anno; if ($meseScorso < 1) { $meseScorso = 12; $annoScorso--; }
$meseProssimo = $mese + 1; $annoProssimo = $anno; if ($meseProssimo > 12) { $meseProssimo = 1; $annoProssimo++; }

$pageTitle = 'Calendario — ' . NOME_CIRCOLO;
$metaDescription = 'Vista mensile a calendario degli appuntamenti del circolo: uscite, corsi e incontri micologici.';
require_once __DIR__ . '/includes/header.php';
?>

<section class="section section-light" style="padding-top:7.5rem;">
  <div class="eyebrow">Calendario</div>
  <h2>Appuntamenti micologici</h2>
  <p class="lead">Vista a calendario degli stessi appuntamenti dell'
  <a href="/eventi.php" style="text-decoration:underline;">elenco eventi</a>, gestiti dall'amministratore.</p>

  <div style="display:flex;align-items:center;justify-content:space-between;margin:2rem 0 1.2rem;flex-wrap:wrap;gap:1rem;">
    <a class="btn btn-ghost btn-sm" href="/calendario.php?anno=<?= $annoScorso ?>&mese=<?= $meseScorso ?>">← Mese precedente</a>
    <h3 style="font-family:var(--font-display);font-size:1.5rem;"><?= $meseNome ?> <?= $anno ?></h3>
    <a class="btn btn-ghost btn-sm" href="/calendario.php?anno=<?= $annoProssimo ?>&mese=<?= $meseProssimo ?>">Mese successivo →</a>
  </div>

  <div class="cal-grid">
    <?php foreach (['Lun','Mar','Mer','Gio','Ven','Sab','Dom'] as $g): ?>
      <div class="cal-intestazione"><?= $g ?></div>
    <?php endforeach; ?>

    <?php for ($i = 0; $i < $offset; $i++): ?>
      <div class="cal-vuota"></div>
    <?php endfor; ?>

    <?php for ($giorno = 1; $giorno <= $giorniNelMese; $giorno++):
        $eOggi = $giorno === (int)$oggi->format('j') && $mese === (int)$oggi->format('n') && $anno === (int)$oggi->format('Y');
    ?>
      <div class="cal-giorno <?= $eOggi ? 'oggi' : '' ?>">
        <div class="cal-numero <?= $eOggi ? 'oggi' : '' ?>"><?= $giorno ?></div>
        <?php if (!empty($eventiPerGiorno[$giorno])): ?>
          <?php foreach ($eventiPerGiorno[$giorno] as $e): ?>
            <div class="cal-evento-chip"><?= h($e['titolo']) ?></div>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>
    <?php endfor; ?>
  </div>

  <?php if (empty($eventiMese)): ?>
    <p style="margin-top:1.5rem;color:#8a8267;font-size:.9rem;">Nessun appuntamento in questo mese.</p>
  <?php endif; ?>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
