/**
 * Banner cookie minimale conforme alle linee guida del Garante Privacy:
 * - nessun cookie non necessario viene impostato prima di una scelta esplicita;
 * - "Accetta" e "Solo necessari" hanno lo stesso peso visivo (nessun pulsante
 *   preselezionato o reso più evidente dell'altro);
 * - la scelta è modificabile in ogni momento dal link "Gestisci cookie" nel footer;
 * - il sito funziona comunque anche senza dare consenso, perché non abbiamo
 *   cookie non necessari da attivare: il banner ha oggi soprattutto valore
 *   informativo, ma è pronto per governare eventuali script futuri (vedi
 *   window.consenso.consentito() più sotto).
 */
(function () {
  var NOME_COOKIE = 'consenso_cookie';
  var DURATA_GIORNI = 180;

  function leggiCookie(nome) {
    var match = document.cookie.match(new RegExp('(?:^|; )' + nome + '=([^;]*)'));
    return match ? decodeURIComponent(match[1]) : null;
  }

  function scriviCookie(nome, valore, giorni) {
    var data = new Date();
    data.setTime(data.getTime() + giorni * 24 * 60 * 60 * 1000);
    document.cookie = nome + '=' + encodeURIComponent(valore) + '; expires=' + data.toUTCString() + '; path=/; SameSite=Lax';
  }

  function leggiPreferenze() {
    var raw = leggiCookie(NOME_COOKIE);
    if (!raw) return null;
    try { return JSON.parse(raw); } catch (e) { return null; }
  }

  // API pubblica: altri script del sito (es. un futuro Google Analytics)
  // possono controllare window.consenso.consentito('statistiche') prima di
  // caricarsi, invece di partire incondizionatamente.
  window.consenso = {
    ottieni: leggiPreferenze,
    consentito: function (categoria) {
      if (categoria === 'necessari') return true;
      var p = leggiPreferenze();
      return !!(p && p[categoria]);
    }
  };

  function salvaEChiudi(preferenze) {
    scriviCookie(NOME_COOKIE, JSON.stringify(preferenze), DURATA_GIORNI);
    var b = document.getElementById('cookieBanner');
    if (b) b.remove();
    document.dispatchEvent(new CustomEvent('consenso-cookie-aggiornato', { detail: preferenze }));
  }

  function mostraBanner() {
    if (document.getElementById('cookieBanner')) return;
    var div = document.createElement('div');
    div.id = 'cookieBanner';
    div.className = 'cookie-banner';
    div.setAttribute('role', 'dialog');
    div.setAttribute('aria-label', 'Informativa sui cookie');
    div.innerHTML =
      '<div class="cookie-banner-inner">' +
      '<p>Questo sito utilizza solo cookie tecnici necessari al suo funzionamento (es. restare collegati all\'area riservata). Non utilizziamo cookie statistici o di terze parti. <a href="/privacy-cookie.php">Maggiori informazioni</a></p>' +
      '<div class="cookie-banner-azioni">' +
      '<button type="button" class="btn btn-ghost btn-sm" id="cookieRifiuta">Solo necessari</button>' +
      '<button type="button" class="btn btn-primary btn-sm" id="cookieAccetta">Accetta</button>' +
      '</div>' +
      '</div>';
    document.body.appendChild(div);
    document.getElementById('cookieAccetta').addEventListener('click', function () {
      salvaEChiudi({ statistiche: true });
    });
    document.getElementById('cookieRifiuta').addEventListener('click', function () {
      salvaEChiudi({ statistiche: false });
    });
  }

  // Richiamabile dal link "Gestisci cookie" nel footer per cambiare scelta.
  window.gestisciCookie = function () {
    mostraBanner();
  };

  document.addEventListener('DOMContentLoaded', function () {
    if (!leggiPreferenze()) mostraBanner();
  });
})();
