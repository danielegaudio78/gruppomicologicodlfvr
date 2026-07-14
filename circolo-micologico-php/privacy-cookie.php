<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/functions.php';

$pageTitle = 'Privacy e cookie — ' . NOME_CIRCOLO;
$metaDescription = 'Informativa privacy e cookie del sito del circolo micologico.';
require_once __DIR__ . '/includes/header.php';
?>

<section class="section section-light" style="padding-top:7.5rem;">
  <div class="eyebrow">Informazioni legali</div>
  <h2>Privacy e cookie</h2>
  <p class="lead">Come trattiamo i dati e quali cookie utilizza questo sito.</p>

  <div style="max-width:70ch;margin-top:2.2rem;color:#3a3427;line-height:1.7;">

    <h3 style="font-size:1.2rem;margin:2rem 0 .6rem;">Titolare del trattamento</h3>
    <p><?= h(NOME_CIRCOLO) ?>, <?= h(INDIRIZZO_CIRCOLO) ?> — per qualunque richiesta relativa ai tuoi dati puoi
    scrivere a <a href="mailto:<?= h(EMAIL_CIRCOLO) ?>" style="text-decoration:underline;"><?= h(EMAIL_CIRCOLO) ?></a>.</p>

    <h3 style="font-size:1.2rem;margin:2rem 0 .6rem;">Cookie tecnici (sempre attivi)</h3>
    <p>Questo sito utilizza esclusivamente cookie tecnici necessari al funzionamento delle sue funzioni essenziali.
    Non richiedono consenso perché indispensabili al servizio richiesto (art. 122 Codice Privacy):</p>
    <table class="admin-table" style="margin-top:1rem;">
      <thead><tr><th>Cookie</th><th>Finalità</th><th>Durata</th></tr></thead>
      <tbody>
        <tr>
          <td><code>PHPSESSID</code></td>
          <td>Mantiene attiva la sessione di accesso per soci e amministratore (es. resta collegato mentre navighi tra le pagine riservate).</td>
          <td>Fino alla chiusura del browser</td>
        </tr>
        <tr>
          <td><code>consenso_cookie</code></td>
          <td>Ricorda la scelta espressa nel banner cookie, per non richiederla di nuovo a ogni visita.</td>
          <td>180 giorni</td>
        </tr>
      </tbody>
    </table>

    <h3 style="font-size:1.2rem;margin:2rem 0 .6rem;">Cookie statistici o di profilazione</h3>
    <p><?= h(NOME_CIRCOLO) ?> <strong>non utilizza</strong> attualmente cookie statistici, di profilazione o di
    terze parti (es. Google Analytics, pixel pubblicitari, plugin social con tracciamento). Se in futuro il circolo
    dovesse introdurne, questa pagina verrà aggiornata di conseguenza e il banner cookie richiederà un consenso
    specifico prima di attivarli.</p>

    <h3 style="font-size:1.2rem;margin:2rem 0 .6rem;">Come gestire le tue preferenze</h3>
    <p>Puoi modificare la scelta fatta in qualunque momento dal link "Gestisci cookie" in fondo a ogni pagina del
    sito, oppure cancellando i cookie di questo sito dalle impostazioni del tuo browser.</p>

    <h3 style="font-size:1.2rem;margin:2rem 0 .6rem;">Dati raccolti tramite i moduli del sito</h3>
    <p>I dati inseriti nei moduli di accesso riservato (soci e amministratore) sono trattati esclusivamente per
    gestire l'accesso alle aree riservate del sito e non vengono ceduti a terzi. Le fotografie e i documenti
    caricati nell'area amministratore sono pubblicati sul sito su decisione del circolo stesso.</p>

    <h3 style="font-size:1.2rem;margin:2rem 0 .6rem;">Ulteriori informazioni</h3>
    <p>Per approfondimenti sulla normativa puoi consultare il sito del
    <a href="https://www.garanteprivacy.it" target="_blank" rel="noopener" style="text-decoration:underline;">Garante per la protezione dei dati personali</a>.</p>
  </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
