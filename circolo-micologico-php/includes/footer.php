<footer class="site" id="contatti">
  <div class="footer-map">
    <iframe
      src="https://www.google.com/maps?q=<?= urlencode(INDIRIZZO_CIRCOLO) ?>&output=embed"
      loading="lazy"
      referrerpolicy="no-referrer-when-downgrade"
      title="Posizione di <?= h(NOME_CIRCOLO) ?>">
    </iframe>
  </div>
  <div class="footer-grid">
    <div>
      <h4><?= h(NOME_CIRCOLO) ?></h4>
      <p><?= h(INDIRIZZO_CIRCOLO) ?></p>
      <p><a href="mailto:<?= h(EMAIL_CIRCOLO) ?>"><?= h(EMAIL_CIRCOLO) ?></a></p>
      <div class="socials">
        <a href="<?= h(SOCIAL_FACEBOOK) ?>" aria-label="Facebook" target="_blank" rel="noopener">
          <svg viewBox="0 0 24 24" fill="currentColor"><path d="M13.5 22v-8.5H16l.4-3.3h-2.9V8.1c0-.96.27-1.6 1.66-1.6H16.5V3.5C16.2 3.46 15.2 3.4 14 3.4c-2.4 0-4 1.46-4 4.16v2.6H7.5v3.3H10V22h3.5Z"/></svg>
        </a>
        <a href="<?= h(SOCIAL_INSTAGRAM) ?>" aria-label="Instagram" target="_blank" rel="noopener">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><rect x="3" y="3" width="18" height="18" rx="5"/><circle cx="12" cy="12" r="4"/><circle cx="17.2" cy="6.8" r="1"/></svg>
        </a>
        <a href="<?= h(SOCIAL_YOUTUBE) ?>" aria-label="YouTube" target="_blank" rel="noopener">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><rect x="2.5" y="5.5" width="19" height="13" rx="3"/><path d="M10.5 9.5 L15 12 L10.5 14.5 Z" fill="currentColor" stroke="none"/></svg>
        </a>
      </div>
    </div>
    <div>
      <h4>Sezioni</h4>
      <a href="/index.php#chi-siamo">Chi siamo</a>
      <a href="/database.php">Database funghi</a>
      <a href="/eventi.php">Eventi</a>
      <a href="/admin/login.php">Accesso riservato</a>
    </div>
    <div>
      <h4>Il circolo</h4>
      <p style="font-size:.85rem;">Le fotografie e le schede tecniche pubblicate sono materiale del circolo: la riproduzione non è consentita senza autorizzazione.</p>
      <a href="/privacy-cookie.php">Privacy e cookie</a>
      <a href="#" onclick="window.gestisciCookie && window.gestisciCookie(); return false;">Gestisci cookie</a>
    </div>
  </div>
  <div class="foot-bottom">
    <span>© <?= date('Y') ?> <?= h(NOME_CIRCOLO) ?></span>
    <span>Tutti i contenuti fotografici sono protetti</span>
  </div>
</footer>

<div class="toast" id="toast"></div>

<script src="/assets/js/protect.js"></script>
<script src="/assets/js/site.js"></script>
<script src="/assets/js/cookie-consent.js"></script>
</body>
</html>
