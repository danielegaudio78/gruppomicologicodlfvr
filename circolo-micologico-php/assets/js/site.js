// --- Menu mobile ---
document.addEventListener('DOMContentLoaded', function () {
  const toggle = document.getElementById('navToggle');
  const links = document.getElementById('navLinks');
  if (toggle && links) {
    toggle.addEventListener('click', () => links.classList.toggle('open'));
    links.querySelectorAll('a').forEach(a => a.addEventListener('click', () => links.classList.remove('open')));
  }
});

// --- Toast ---
function showToast(msg) {
  const t = document.getElementById('toast');
  if (!t) return;
  t.textContent = msg;
  t.classList.add('show');
  setTimeout(() => t.classList.remove('show'), 3200);
}

// --- Carosello hero (homepage) ---
function initHeroCarousel() {
  const slides = document.querySelectorAll('.hero-slide');
  const dots = document.querySelectorAll('.hero-dots button');
  if (slides.length < 2) return;
  let i = 0;
  setInterval(() => {
    slides[i].classList.remove('active');
    dots[i] && dots[i].classList.remove('active');
    i = (i + 1) % slides.length;
    slides[i].classList.add('active');
    dots[i] && dots[i].classList.add('active');
  }, 5000);
  dots.forEach((d, idx) => d.addEventListener('click', () => {
    slides[i].classList.remove('active');
    dots[i].classList.remove('active');
    i = idx;
    slides[i].classList.add('active');
    dots[i].classList.add('active');
  }));
}
document.addEventListener('DOMContentLoaded', initHeroCarousel);

// --- Galleria specie: cambia foto principale al click sulle miniature ---
function initGallery() {
  const main = document.getElementById('galleryMain');
  const thumbs = document.querySelectorAll('.gallery-thumbs button');
  if (!main || thumbs.length === 0) return;
  thumbs.forEach(btn => {
    btn.addEventListener('click', () => {
      thumbs.forEach(b => b.classList.remove('active'));
      btn.classList.add('active');
      main.src = btn.dataset.full;
    });
  });
}
document.addEventListener('DOMContentLoaded', initGallery);

// --- Filtri database funghi ---
function initFiltri() {
  const bar = document.getElementById('filterBar');
  const search = document.getElementById('searchInput');
  if (!bar) return;
  const cards = document.querySelectorAll('.fungo-card');

  function normalizza(s) {
    return (s || '').toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g, '');
  }

  function apply() {
    const activeChip = bar.querySelector('.chip.active');
    const gruppo = activeChip ? activeChip.dataset.gruppo : 'tutti';
    const q = normalizza(search && search.value || '').trim();
    cards.forEach(card => {
      const matchGruppo = gruppo === 'tutti' || card.dataset.gruppo === gruppo;
      const testo = normalizza(card.dataset.nome + ' ' + card.dataset.scientifico + ' ' + card.dataset.alternativi);
      const matchQ = q === '' || testo.includes(q);
      card.style.display = (matchGruppo && matchQ) ? '' : 'none';
    });
  }

  bar.addEventListener('click', e => {
    const chip = e.target.closest('.chip');
    if (!chip) return;
    bar.querySelectorAll('.chip').forEach(c => c.classList.remove('active'));
    chip.classList.add('active');
    apply();
  });
  if (search) search.addEventListener('input', apply);
}
// --- Scheda tecnica espandibile nelle card del database funghi ---
function initSchedaToggle() {
  document.addEventListener('click', e => {
    const btn = e.target.closest('.scheda-toggle');
    if (!btn) return;
    const dettaglio = btn.nextElementSibling;
    if (!dettaglio) return;
    const aperta = dettaglio.classList.toggle('open');
    btn.textContent = aperta ? 'Scheda tecnica ▴' : 'Scheda tecnica ▾';
  });
}

document.addEventListener('DOMContentLoaded', initFiltri);
document.addEventListener('DOMContentLoaded', initSchedaToggle);
