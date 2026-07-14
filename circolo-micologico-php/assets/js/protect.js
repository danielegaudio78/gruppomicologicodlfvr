/**
 * Deterrenti standard contro il salvataggio rapido delle immagini:
 * - blocco del tasto destro sulle immagini protette
 * - blocco del trascinamento (drag & drop) delle immagini
 * - blocco della selezione di testo/immagini nella galleria
 *
 * NB: questi accorgimenti scoraggiano il download occasionale ma non
 * possono impedire in modo assoluto la copia di un contenuto mostrato
 * in un browser (screenshot, strumenti di sviluppo, ecc.). Per una
 * protezione più forte sulle foto pubblicate si usano marchiature
 * (watermark) e risoluzioni ridotte lato server, già applicate alle
 * immagini servite da serve_image.php.
 */
document.addEventListener('contextmenu', function (e) {
  if (e.target.closest('.protetta-wrap, .protetta')) {
    e.preventDefault();
  }
});

document.addEventListener('dragstart', function (e) {
  if (e.target.closest('.protetta-wrap, .protetta')) {
    e.preventDefault();
  }
});

document.querySelectorAll('.protetta-wrap img, .protetta').forEach(function (img) {
  img.setAttribute('draggable', 'false');
});
