<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/functions.php';
require_socio();

$pageTitle = 'Bacheca soci — ' . NOME_CIRCOLO;
require_once __DIR__ . '/../includes/header.php';
?>

<section class="admin-shell" style="max-width:720px;">
  <div class="admin-card">
    <div class="eyebrow">Bacheca interna</div>
    <h2>Ciao, <?= h($_SESSION['socio_nome']) ?></h2>
    <p class="lead" style="margin-bottom:0;">Uno spazio di scambio tra soci: ritrovamenti, domande, organizzazione
    delle uscite. Si aggiorna da solo ogni pochi secondi.</p>
  </div>

  <div class="admin-card">
    <div id="chatBox" style="max-height:420px;overflow-y:auto;display:flex;flex-direction:column;gap:.8rem;padding-right:.4rem;">
      <p style="color:#8a8267;font-size:.85rem;">Caricamento messaggi…</p>
    </div>

    <form id="chatForm" style="display:flex;gap:.6rem;margin-top:1.4rem;">
      <input type="text" id="chatTesto" placeholder="Scrivi un messaggio ai soci…" maxlength="2000" required style="flex:1;padding:.75rem .9rem;border:1px solid #c9bf9f;border-radius:3px;">
      <button class="btn btn-primary" type="submit">Invia</button>
    </form>
    <div class="help" style="margin-top:1.4rem;"><a href="/soci/logout.php">Esci dalla bacheca</a></div>
  </div>
</section>

<script>
(function () {
  const box = document.getElementById('chatBox');
  const form = document.getElementById('chatForm');
  const input = document.getElementById('chatTesto');
  let ultimoId = 0;
  let primoCaricamento = true;

  function messaggioHTML(m) {
    const wrap = document.createElement('div');
    wrap.style.cssText = 'background:#fbf9f3;border:1px solid #eee2ca;border-radius:6px;padding:.7rem .9rem;';
    const meta = document.createElement('div');
    meta.style.cssText = 'font-family:var(--font-mono);font-size:.7rem;color:#8a8267;margin-bottom:.3rem;';
    meta.textContent = m.autore + ' · ' + m.quando;
    const testo = document.createElement('div');
    testo.style.cssText = 'font-size:.92rem;white-space:pre-wrap;word-break:break-word;';
    testo.textContent = m.testo; // testContent: mai HTML non filtrato
    wrap.appendChild(meta);
    wrap.appendChild(testo);
    return wrap;
  }

  async function aggiorna() {
    try {
      const res = await fetch('/soci/bacheca_dati.php' + (ultimoId ? ('?dopo=' + ultimoId) : ''));
      if (res.status === 401) { window.location.href = '/soci/login.php'; return; }
      const dati = await res.json();
      if (primoCaricamento) box.innerHTML = '';
      if (dati.messaggi && dati.messaggi.length) {
        const eraInFondo = box.scrollTop + box.clientHeight >= box.scrollHeight - 40;
        dati.messaggi.forEach(m => {
          box.appendChild(messaggioHTML(m));
          ultimoId = Math.max(ultimoId, m.id);
        });
        if (primoCaricamento || eraInFondo) box.scrollTop = box.scrollHeight;
      } else if (primoCaricamento) {
        box.innerHTML = '<p style="color:#8a8267;font-size:.85rem;">Nessun messaggio ancora: scrivi il primo.</p>';
      }
      primoCaricamento = false;
    } catch (e) {
      // in caso di problemi di rete, il prossimo giro riprova da solo
    }
  }

  form.addEventListener('submit', async (e) => {
    e.preventDefault();
    const testo = input.value.trim();
    if (!testo) return;
    input.disabled = true;
    try {
      const body = new URLSearchParams({ testo });
      await fetch('/soci/bacheca_dati.php', { method: 'POST', body });
      input.value = '';
      await aggiorna();
    } finally {
      input.disabled = false;
      input.focus();
    }
  });

  aggiorna();
  setInterval(aggiorna, 6000);
})();
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
