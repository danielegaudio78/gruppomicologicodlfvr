<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/functions.php';
require_admin();

$specie = db()->query("
    SELECT s.*, (SELECT COUNT(*) FROM foto f WHERE f.specie_id = s.id) AS n_foto
    FROM specie s ORDER BY s.creato_il DESC
")->fetchAll();

$eventi = db()->query("SELECT * FROM eventi ORDER BY data_evento DESC")->fetchAll();
$lezioni = db()->query("SELECT * FROM lezioni_corso ORDER BY ordine")->fetchAll();
$legislazioni = db()->query("SELECT * FROM legislazione ORDER BY regione")->fetchAll();
$tossine = db()->query("SELECT * FROM tossine ORDER BY nome_sindrome")->fetchAll();
$riviste = db()->query("SELECT * FROM pubblicazioni ORDER BY data_pubblicazione DESC")->fetchAll();
$consiglio = db()->query("SELECT * FROM consiglio ORDER BY ordine")->fetchAll();
$ricette = db()->query("SELECT * FROM ricetta ORDER BY ordine")->fetchAll();
$confronti = db()->query("
    SELECT c.*, sb.nome_comune AS buona_nome, sc.nome_comune AS cattiva_nome
    FROM confronto c
    JOIN specie sb ON sb.id = c.specie_buona_id
    JOIN specie sc ON sc.id = c.specie_cattiva_id
    ORDER BY c.ordine
")->fetchAll();
$nSoci = (int) db()->query("SELECT COUNT(*) FROM soci")->fetchColumn();
$nMessaggi = (int) db()->query("SELECT COUNT(*) FROM messaggi_bacheca")->fetchColumn();
$nFotoGalleriaSoci = (int) db()->query("SELECT COUNT(*) FROM galleria_soci")->fetchColumn();

$pageTitle = 'Pannello admin — ' . NOME_CIRCOLO;
require_once __DIR__ . '/../includes/header.php';
?>

<div class="admin-bar">
  <span>Connesso come <b><?= h($_SESSION['admin_username']) ?></b></span>
  <a href="/admin/logout.php">Esci</a>
</div>

<section class="admin-shell">
  <div class="admin-card" style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:1rem;">
    <div>
      <div class="eyebrow">Homepage</div>
      <h2>Sezione "Chi siamo"</h2>
    </div>
    <a href="/admin/chi_siamo_form.php" class="btn btn-primary">Modifica testo e foto →</a>
  </div>

  <div class="admin-card" style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:1rem;">
    <div>
      <div class="eyebrow">Homepage</div>
      <h2>Carosello fotografico</h2>
    </div>
    <a href="/admin/home_slides.php" class="btn btn-primary">Gestisci foto carosello →</a>
  </div>

  <div class="admin-card" style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:1rem;">
    <div>
      <div class="eyebrow">Database specie</div>
      <h2>Funghi pubblicati (<?= count($specie) ?>)</h2>
    </div>
    <a href="/admin/specie_form.php" class="btn btn-primary">+ Nuova specie</a>
  </div>

  <div class="admin-card">
    <table class="admin-table">
      <thead><tr><th>Nome</th><th>Nome scientifico</th><th>Commestibilità</th><th>Foto</th><th>PDF</th><th>In evidenza</th><th></th></tr></thead>
      <tbody>
        <?php foreach ($specie as $s): ?>
          <tr>
            <td><?= h($s['nome_comune']) ?></td>
            <td style="font-style:italic;color:#7b7460;"><?= h($s['nome_scientifico']) ?></td>
            <td><span class="badge <?= classe_commestibilita($s['commestibilita']) ?>"><?= etichetta_commestibilita($s['commestibilita']) ?></span></td>
            <td><?= (int)$s['n_foto'] ?></td>
            <td><?= $s['pdf_path'] ? '✓' : '—' ?></td>
            <td><?= $s['in_evidenza'] ? '✓' : '—' ?></td>
            <td style="white-space:nowrap;">
              <a class="vedi" href="/admin/specie_form.php?id=<?= (int)$s['id'] ?>">Modifica</a>
              &nbsp;·&nbsp;
              <a class="vedi" style="color:#9c3b2e;border-color:#9c3b2e;" href="/admin/specie_delete.php?id=<?= (int)$s['id'] ?>" onclick="return confirm('Eliminare definitivamente questa specie e tutte le sue foto/PDF?');">Elimina</a>
            </td>
          </tr>
        <?php endforeach; ?>
        <?php if (empty($specie)): ?><tr><td colspan="7" style="color:#8a8267;">Nessuna specie inserita.</td></tr><?php endif; ?>
      </tbody>
    </table>
  </div>

  <!-- ===== In cucina ===== -->
  <div class="admin-card" style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:1rem;">
    <div><div class="eyebrow">In cucina</div><h2>Ricette (<?= count($ricette) ?>)</h2></div>
    <a href="/admin/ricetta_form.php" class="btn btn-primary">+ Nuova ricetta</a>
  </div>
  <div class="admin-card">
    <table class="admin-table">
      <thead><tr><th>Titolo</th><th>Categoria</th><th>Foto</th><th>Abbinamento vino</th><th></th></tr></thead>
      <tbody>
        <?php foreach ($ricette as $r): ?>
          <tr>
            <td><?= h($r['titolo']) ?></td>
            <td><?= h($r['categoria']) ?></td>
            <td><?= $r['foto_path'] ? '✓' : '—' ?></td>
            <td><?= $r['abbinamento_vino'] ? '✓' : '—' ?></td>
            <td style="white-space:nowrap;">
              <a class="vedi" href="/admin/ricetta_form.php?id=<?= (int)$r['id'] ?>">Modifica</a>&nbsp;·&nbsp;
              <a class="vedi" style="color:#9c3b2e;border-color:#9c3b2e;" href="/admin/ricetta_delete.php?id=<?= (int)$r['id'] ?>" onclick="return confirm('Eliminare questa ricetta?');">Elimina</a>
            </td>
          </tr>
        <?php endforeach; ?>
        <?php if (empty($ricette)): ?><tr><td colspan="5" style="color:#8a8267;">Nessuna ricetta inserita.</td></tr><?php endif; ?>
      </tbody>
    </table>
  </div>

  <!-- ===== Fungo buono, fungo cattivo ===== -->
  <div class="admin-card" style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:1rem;">
    <div><div class="eyebrow">Riconoscimento</div><h2>Confronti (<?= count($confronti) ?>)</h2></div>
    <a href="/admin/confronto_form.php" class="btn btn-primary">+ Nuovo confronto</a>
  </div>
  <div class="admin-card">
    <table class="admin-table">
      <thead><tr><th>Titolo</th><th>Fungo buono</th><th>Fungo cattivo</th><th></th></tr></thead>
      <tbody>
        <?php foreach ($confronti as $c): ?>
          <tr>
            <td><?= h($c['titolo'] ?: '—') ?></td>
            <td><span class="badge badge-si"><?= h($c['buona_nome']) ?></span></td>
            <td><span class="badge badge-no"><?= h($c['cattiva_nome']) ?></span></td>
            <td style="white-space:nowrap;">
              <a class="vedi" href="/admin/confronto_form.php?id=<?= (int)$c['id'] ?>">Modifica</a>&nbsp;·&nbsp;
              <a class="vedi" style="color:#9c3b2e;border-color:#9c3b2e;" href="/admin/confronto_delete.php?id=<?= (int)$c['id'] ?>" onclick="return confirm('Eliminare questo confronto?');">Elimina</a>
            </td>
          </tr>
        <?php endforeach; ?>
        <?php if (empty($confronti)): ?><tr><td colspan="4" style="color:#8a8267;">Nessun confronto inserito.</td></tr><?php endif; ?>
      </tbody>
    </table>
  </div>

  <div class="admin-card" style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:1rem;">
    <div>
      <div class="eyebrow">Calendario</div>
      <h2>Eventi (<?= count($eventi) ?>)</h2>
    </div>
    <a href="/admin/eventi_form.php" class="btn btn-primary">+ Nuovo evento</a>
  </div>

  <div class="admin-card">
    <table class="admin-table">
      <thead><tr><th>Data</th><th>Titolo</th><th>Tag</th><th></th></tr></thead>
      <tbody>
        <?php foreach ($eventi as $e): ?>
          <tr>
            <td><?= data_it($e['data_evento']) ?></td>
            <td><?= h($e['titolo']) ?></td>
            <td><?= h($e['tag']) ?></td>
            <td style="white-space:nowrap;">
              <a class="vedi" href="/admin/eventi_form.php?id=<?= (int)$e['id'] ?>">Modifica</a>
              &nbsp;·&nbsp;
              <a class="vedi" style="color:#9c3b2e;border-color:#9c3b2e;" href="/admin/eventi_delete.php?id=<?= (int)$e['id'] ?>" onclick="return confirm('Eliminare questo evento?');">Elimina</a>
            </td>
          </tr>
        <?php endforeach; ?>
        <?php if (empty($eventi)): ?><tr><td colspan="4" style="color:#8a8267;">Nessun evento inserito.</td></tr><?php endif; ?>
      </tbody>
    </table>
  </div>

  <!-- ===== Corso di micologia ===== -->
  <div class="admin-card" style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:1rem;">
    <div><div class="eyebrow">Formazione</div><h2>Lezioni del corso (<?= count($lezioni) ?>)</h2></div>
    <a href="/admin/corso_form.php" class="btn btn-primary">+ Nuova lezione</a>
  </div>
  <div class="admin-card">
    <table class="admin-table">
      <thead><tr><th>Data</th><th>Titolo</th><th>PDF</th><th>Link</th><th></th></tr></thead>
      <tbody>
        <?php foreach ($lezioni as $l): ?>
          <tr>
            <td><?= data_it($l['data_lezione']) ?></td>
            <td><?= h($l['titolo']) ?></td>
            <td><?= $l['pdf_path'] ? '✓' : '—' ?></td>
            <td><?= $l['link_esterno'] ? '✓' : '—' ?></td>
            <td style="white-space:nowrap;">
              <a class="vedi" href="/admin/corso_form.php?id=<?= (int)$l['id'] ?>">Modifica</a>&nbsp;·&nbsp;
              <a class="vedi" style="color:#9c3b2e;border-color:#9c3b2e;" href="/admin/corso_delete.php?id=<?= (int)$l['id'] ?>" onclick="return confirm('Eliminare questa lezione?');">Elimina</a>
            </td>
          </tr>
        <?php endforeach; ?>
        <?php if (empty($lezioni)): ?><tr><td colspan="5" style="color:#8a8267;">Nessuna lezione inserita.</td></tr><?php endif; ?>
      </tbody>
    </table>
  </div>

  <!-- ===== Legislazione regionale ===== -->
  <div class="admin-card" style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:1rem;">
    <div><div class="eyebrow">Normativa</div><h2>Legislazione regionale (<?= count($legislazioni) ?>)</h2></div>
    <a href="/admin/legislazione_form.php" class="btn btn-primary">+ Nuova scheda</a>
  </div>
  <div class="admin-card">
    <table class="admin-table">
      <thead><tr><th>Regione</th><th>Titolo</th><th>PDF</th><th></th></tr></thead>
      <tbody>
        <?php foreach ($legislazioni as $v): ?>
          <tr>
            <td><?= h($v['regione']) ?></td>
            <td><?= h($v['titolo']) ?></td>
            <td><?= $v['pdf_path'] ? '✓' : '—' ?></td>
            <td style="white-space:nowrap;">
              <a class="vedi" href="/admin/legislazione_form.php?id=<?= (int)$v['id'] ?>">Modifica</a>&nbsp;·&nbsp;
              <a class="vedi" style="color:#9c3b2e;border-color:#9c3b2e;" href="/admin/legislazione_delete.php?id=<?= (int)$v['id'] ?>" onclick="return confirm('Eliminare questa scheda?');">Elimina</a>
            </td>
          </tr>
        <?php endforeach; ?>
        <?php if (empty($legislazioni)): ?><tr><td colspan="4" style="color:#8a8267;">Nessuna scheda inserita.</td></tr><?php endif; ?>
      </tbody>
    </table>
  </div>

  <!-- ===== Micologia (CMS) ===== -->
  <div class="admin-card" style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:1rem;">
    <div><div class="eyebrow">Biologia del fungo</div><h2>Pagina "Micologia"</h2></div>
    <a href="/admin/micologia_form.php" class="btn btn-primary">Modifica contenuti →</a>
  </div>

  <!-- ===== Micotossicologia ===== -->
  <div class="admin-card" style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:1rem;">
    <div><div class="eyebrow">Tossicologia</div><h2>Sindromi tossiche (<?= count($tossine) ?>)</h2></div>
    <a href="/admin/tossine_form.php" class="btn btn-primary">+ Nuova sindrome</a>
  </div>
  <div class="admin-card">
    <table class="admin-table">
      <thead><tr><th>Sindrome</th><th>Gravità</th><th>PDF</th><th></th></tr></thead>
      <tbody>
        <?php foreach ($tossine as $t): ?>
          <tr>
            <td><?= h($t['nome_sindrome']) ?></td>
            <td><?= h($t['gravita']) ?></td>
            <td><?= $t['pdf_path'] ? '✓' : '—' ?></td>
            <td style="white-space:nowrap;">
              <a class="vedi" href="/admin/tossine_form.php?id=<?= (int)$t['id'] ?>">Modifica</a>&nbsp;·&nbsp;
              <a class="vedi" style="color:#9c3b2e;border-color:#9c3b2e;" href="/admin/tossine_delete.php?id=<?= (int)$t['id'] ?>" onclick="return confirm('Eliminare questa scheda?');">Elimina</a>
            </td>
          </tr>
        <?php endforeach; ?>
        <?php if (empty($tossine)): ?><tr><td colspan="4" style="color:#8a8267;">Nessuna scheda inserita.</td></tr><?php endif; ?>
      </tbody>
    </table>
  </div>

  <!-- ===== Riviste ===== -->
  <div class="admin-card" style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:1rem;">
    <div><div class="eyebrow">Pubblicazioni</div><h2>Riviste (<?= count($riviste) ?>)</h2></div>
    <a href="/admin/pubblicazioni_form.php" class="btn btn-primary">+ Nuova pubblicazione</a>
  </div>
  <div class="admin-card">
    <table class="admin-table">
      <thead><tr><th>Data</th><th>Titolo</th><th>PDF</th><th></th></tr></thead>
      <tbody>
        <?php foreach ($riviste as $r): ?>
          <tr>
            <td><?= data_it($r['data_pubblicazione']) ?></td>
            <td><?= h($r['titolo']) ?></td>
            <td><?= $r['pdf_path'] ? '✓' : '—' ?></td>
            <td style="white-space:nowrap;">
              <a class="vedi" href="/admin/pubblicazioni_form.php?id=<?= (int)$r['id'] ?>">Modifica</a>&nbsp;·&nbsp;
              <a class="vedi" style="color:#9c3b2e;border-color:#9c3b2e;" href="/admin/pubblicazioni_delete.php?id=<?= (int)$r['id'] ?>" onclick="return confirm('Eliminare questa pubblicazione?');">Elimina</a>
            </td>
          </tr>
        <?php endforeach; ?>
        <?php if (empty($riviste)): ?><tr><td colspan="4" style="color:#8a8267;">Nessuna pubblicazione inserita.</td></tr><?php endif; ?>
      </tbody>
    </table>
  </div>

  <!-- ===== Consiglio direttivo ===== -->
  <div class="admin-card" style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:1rem;">
    <div><div class="eyebrow">Chi siamo</div><h2>Consiglio direttivo (<?= count($consiglio) ?>)</h2></div>
    <a href="/admin/consiglio_form.php" class="btn btn-primary">+ Nuovo membro</a>
  </div>
  <div class="admin-card">
    <table class="admin-table">
      <thead><tr><th>Nome</th><th>Carica</th><th>Foto</th><th></th></tr></thead>
      <tbody>
        <?php foreach ($consiglio as $m): ?>
          <tr>
            <td><?= h($m['nome']) ?></td>
            <td><?= h($m['ruolo']) ?></td>
            <td><?= $m['foto_path'] ? '✓' : '—' ?></td>
            <td style="white-space:nowrap;">
              <a class="vedi" href="/admin/consiglio_form.php?id=<?= (int)$m['id'] ?>">Modifica</a>&nbsp;·&nbsp;
              <a class="vedi" style="color:#9c3b2e;border-color:#9c3b2e;" href="/admin/consiglio_delete.php?id=<?= (int)$m['id'] ?>" onclick="return confirm('Eliminare questo membro?');">Elimina</a>
            </td>
          </tr>
        <?php endforeach; ?>
        <?php if (empty($consiglio)): ?><tr><td colspan="4" style="color:#8a8267;">Nessun membro inserito.</td></tr><?php endif; ?>
      </tbody>
    </table>
  </div>

  <!-- ===== Statuto e regolamento ===== -->
  <div class="admin-card" style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:1rem;">
    <div><div class="eyebrow">Chi siamo</div><h2>Statuto e regolamento</h2></div>
    <a href="/admin/statuto_form.php" class="btn btn-primary">Modifica documenti →</a>
  </div>

  <!-- ===== Bacheca soci ===== -->
  <div class="admin-card" style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:1rem;">
    <div>
      <div class="eyebrow">Bacheca interna</div>
      <h2><?= $nSoci ?> soci abilitati · <?= $nMessaggi ?> messaggi</h2>
    </div>
    <a href="/admin/soci.php" class="btn btn-primary">Gestisci account soci →</a>
  </div>

  <!-- ===== Galleria riservata ai soci ===== -->
  <div class="admin-card" style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:1rem;">
    <div>
      <div class="eyebrow">Riservata ai soci</div>
      <h2>Galleria fotografica (<?= $nFotoGalleriaSoci ?>)</h2>
    </div>
    <a href="/admin/galleria_soci.php" class="btn btn-primary">Gestisci galleria soci →</a>
  </div>
</section>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
