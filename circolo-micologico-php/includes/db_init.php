<?php
/**
 * Crea lo schema del database al primo avvio e inserisce alcuni dati
 * dimostrativi, così il sito è subito navigabile.
 * Non contiene account amministratore: quello si crea con install.php.
 */

function inizializza_database(PDO $pdo): void
{
    $pdo->exec("
        CREATE TABLE admin (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            username TEXT UNIQUE NOT NULL,
            password_hash TEXT NOT NULL,
            creato_il TEXT DEFAULT CURRENT_TIMESTAMP
        );

        CREATE TABLE specie (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            nome_comune TEXT NOT NULL,
            nome_scientifico TEXT NOT NULL,
            nomi_alternativi TEXT,
            commestibilita TEXT NOT NULL CHECK(commestibilita IN ('si','no','verifica')),
            gruppo TEXT,
            habitat TEXT,
            periodo TEXT,
            cappello TEXT,
            gambo TEXT,
            imenio TEXT,
            spore TEXT,
            note TEXT,
            pdf_path TEXT,
            in_evidenza INTEGER NOT NULL DEFAULT 0,
            creato_il TEXT DEFAULT CURRENT_TIMESTAMP
        );

        CREATE TABLE foto (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            specie_id INTEGER NOT NULL,
            path TEXT NOT NULL,
            didascalia TEXT,
            ordine INTEGER NOT NULL DEFAULT 0,
            FOREIGN KEY (specie_id) REFERENCES specie(id) ON DELETE CASCADE
        );

        CREATE TABLE eventi (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            titolo TEXT NOT NULL,
            data_evento TEXT NOT NULL,
            tag TEXT,
            descrizione TEXT,
            immagine_path TEXT,
            allegato_path TEXT,
            allegato_nome_originale TEXT
        );

        CREATE TABLE home_slide (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            path TEXT NOT NULL,
            ordine INTEGER NOT NULL DEFAULT 0,
            creato_il TEXT DEFAULT CURRENT_TIMESTAMP
        );

        CREATE TABLE lezioni_corso (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            titolo TEXT NOT NULL,
            data_lezione TEXT,
            descrizione TEXT,
            pdf_path TEXT,
            link_esterno TEXT,
            ordine INTEGER NOT NULL DEFAULT 0,
            creato_il TEXT DEFAULT CURRENT_TIMESTAMP
        );

        CREATE TABLE legislazione (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            regione TEXT NOT NULL,
            titolo TEXT NOT NULL,
            testo TEXT,
            pdf_path TEXT,
            link_esterno TEXT,
            aggiornato_il TEXT DEFAULT CURRENT_TIMESTAMP
        );

        CREATE TABLE tossine (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            nome_sindrome TEXT NOT NULL,
            tempo_latenza TEXT,
            funghi_coinvolti TEXT,
            sintomi TEXT,
            gravita TEXT,
            note TEXT,
            pdf_path TEXT,
            creato_il TEXT DEFAULT CURRENT_TIMESTAMP
        );

        CREATE TABLE pubblicazioni (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            titolo TEXT NOT NULL,
            data_pubblicazione TEXT,
            descrizione TEXT,
            pdf_path TEXT,
            creato_il TEXT DEFAULT CURRENT_TIMESTAMP
        );

        CREATE TABLE pagine_cms (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            slug TEXT UNIQUE NOT NULL,
            titolo TEXT,
            contenuto TEXT
        );

        CREATE TABLE soci (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            nome TEXT NOT NULL,
            username TEXT UNIQUE NOT NULL,
            password_hash TEXT NOT NULL,
            creato_il TEXT DEFAULT CURRENT_TIMESTAMP
        );

        CREATE TABLE messaggi_bacheca (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            socio_id INTEGER NOT NULL,
            testo TEXT NOT NULL,
            creato_il TEXT DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (socio_id) REFERENCES soci(id) ON DELETE CASCADE
        );

        CREATE TABLE consiglio (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            nome TEXT NOT NULL,
            ruolo TEXT,
            bio TEXT,
            foto_path TEXT,
            ordine INTEGER NOT NULL DEFAULT 0,
            creato_il TEXT DEFAULT CURRENT_TIMESTAMP
        );

        CREATE TABLE documento_sociale (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            slug TEXT UNIQUE NOT NULL CHECK(slug IN ('statuto','regolamento')),
            titolo TEXT,
            testo TEXT,
            pdf_path TEXT,
            aggiornato_il TEXT DEFAULT CURRENT_TIMESTAMP
        );

        CREATE TABLE ricetta (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            titolo TEXT NOT NULL,
            categoria TEXT,
            funghi_utilizzati TEXT,
            tempo_preparazione TEXT,
            difficolta TEXT,
            ingredienti TEXT,
            procedimento TEXT,
            abbinamento_vino TEXT,
            foto_path TEXT,
            ordine INTEGER NOT NULL DEFAULT 0,
            creato_il TEXT DEFAULT CURRENT_TIMESTAMP
        );

        CREATE TABLE pagina_chi_siamo (
            id INTEGER PRIMARY KEY CHECK (id = 1),
            testo TEXT,
            foto_path TEXT,
            soci_numero TEXT,
            soci_etichetta TEXT,
            anni_numero TEXT,
            anni_etichetta TEXT
        );

        CREATE TABLE galleria_soci (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            path TEXT NOT NULL,
            didascalia TEXT,
            ordine INTEGER NOT NULL DEFAULT 0,
            creato_il TEXT DEFAULT CURRENT_TIMESTAMP
        );

        CREATE TABLE confronto (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            titolo TEXT,
            specie_buona_id INTEGER NOT NULL,
            specie_cattiva_id INTEGER NOT NULL,
            note_confronto TEXT,
            ordine INTEGER NOT NULL DEFAULT 0,
            creato_il TEXT DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (specie_buona_id) REFERENCES specie(id) ON DELETE CASCADE,
            FOREIGN KEY (specie_cattiva_id) REFERENCES specie(id) ON DELETE CASCADE
        );
    ");

    // --- Specie dimostrative (senza foto reali: l'amministratore le caricherà da /admin) ---
    $stmt = $pdo->prepare("INSERT INTO specie
        (nome_comune, nome_scientifico, nomi_alternativi, commestibilita, gruppo, habitat, periodo, cappello, gambo, imenio, spore, note, in_evidenza)
        VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?)");

    $specie = [
        ['Porcino', 'Boletus edulis', 'Ceppatello, Boleto', 'si', 'Boleti', 'Boschi di latifoglie e conifere, terreno acido', 'Estate – autunno',
         'Bruno-nocciola, emisferico poi convesso, 8–25 cm', 'Clavato, reticolato in alto, biancastro',
         'Tubuli bianchi poi verdognoli', 'Bruno-olivastre', 'Uno dei boleti più ricercati, odore gradevole di nocciola.', 1],
        ['Finferlo', 'Cantharellus cibarius', 'Gallinaccio, Galletto', 'si', 'Aphyllophorales', 'Boschi misti, muschio, terreno umido', 'Giugno – novembre',
         'Giallo uovo, imbutiforme, margine irregolare, 3–10 cm', 'Concolore al cappello, pieno',
         'Pliche decorrenti, non vere lamelle', 'Crema pallido', 'Profumo caratteristico di albicocca.', 1],
        ['Chiodino', 'Armillaria mellea', 'Famigliola buona', 'verifica', 'Tricholoma', 'Ceppi e radici di latifoglie, in gruppi numerosi', 'Autunno',
         'Miele-bruno, squamoso al centro, 3–12 cm', 'Fibroso con anello membranoso',
         'Lamelle biancastre poi brunastre', 'Bianche', 'Commestibile solo ben cotto, tossico crudo.', 0],
        ['Amanita muscaria', 'Amanita muscaria', 'Ovolo malefico', 'no', 'Amanita', 'Boschi di betulle e conifere', 'Estate – autunno',
         'Rosso vivo con verruche bianche, 8–20 cm', 'Bianco, bulboso alla base con volva',
         'Lamelle bianche libere', 'Bianche', 'Tossica e psicoattiva: da non raccogliere.', 1],
    ];
    foreach ($specie as $s) {
        $stmt->execute($s);
    }

    // --- Confronto dimostrativo: usa gli id delle specie appena inserite ---
    $idPorcino = (int) $pdo->query("SELECT id FROM specie WHERE nome_comune = 'Porcino'")->fetchColumn();
    $idAmanita = (int) $pdo->query("SELECT id FROM specie WHERE nome_comune = 'Amanita muscaria'")->fetchColumn();
    if ($idPorcino && $idAmanita) {
        $pdo->prepare("INSERT INTO confronto (titolo, specie_buona_id, specie_cattiva_id, note_confronto, ordine) VALUES (?,?,?,?,?)")
            ->execute([
                'Porcino vs Amanita muscaria (esempio dimostrativo)',
                $idPorcino, $idAmanita,
                "Questo è un confronto segnaposto a scopo dimostrativo: nella realtà il Porcino non si confonde facilmente con l'Amanita muscaria, molto più riconoscibile. Sostituiscilo dal pannello amministratore con un vero confronto tra specie simili (es. Boletus edulis e Boletus satanas, oppure Agaricus campestris e le Amanite bianche tossiche), spiegando qui i punti chiave per distinguerle: colore delle lamelle/tubuli, presenza della volva, reazione al taglio, habitat.",
                0,
            ]);
    }

    // --- Eventi dimostrativi ---
    $stmt = $pdo->prepare("INSERT INTO eventi (titolo, data_evento, tag, descrizione) VALUES (?,?,?,?)");
    $eventi = [
        ['Uscita micologica al Cansiglio', '2026-09-20', 'Uscita', 'Raccolta guidata con determinazione in campo, ritrovo ore 8:00.'],
        ['Corso base di riconoscimento funghi', '2026-10-04', 'Corso', 'Tre serate teoriche con il comitato scientifico, aula sociale ore 20:30.'],
        ['Mostra micologica di sede', '2026-11-08', 'Mostra', 'Esposizione delle specie raccolte durante la stagione, ingresso libero.'],
    ];
    foreach ($eventi as $e) {
        $stmt->execute($e);
    }

    // --- Corso di micologia: struttura dimostrativa, senza PDF reali ---
    $stmt = $pdo->prepare("INSERT INTO lezioni_corso (titolo, data_lezione, descrizione, ordine) VALUES (?,?,?,?)");
    $lezioni = [
        ['Lezione 1 — Morfologia dei funghi', '2026-10-04', 'Struttura del carpoforo, cappello, gambo, imenio.', 0],
        ['Lezione 2 — Le famiglie principali', '2026-10-11', 'Boletacee, Amanitacee, Russulacee e Agaricacee a confronto.', 1],
        ['Lezione 3 — Funghi tossici e sosia pericolosi', '2026-10-18', 'Le confusioni più frequenti tra specie commestibili e velenose.', 2],
    ];
    foreach ($lezioni as $l) { $stmt->execute($l); }

    // --- Legislazione regionale: esempio ---
    $stmt = $pdo->prepare("INSERT INTO legislazione (regione, titolo, testo) VALUES (?,?,?)");
    $stmt->execute(['Veneto', 'Regolamento raccolta funghi epigei', 'La raccolta è consentita nei giorni e con i quantitativi stabiliti dal regolamento regionale; è necessario il tesserino rilasciato dai Comuni o dalle ASL di competenza. Sostituisci questo testo con il testo normativo aggiornato della tua regione.']);

    // --- Micologia: pagina divulgativa (due sezioni editabili dall'admin) ---
    $stmt = $pdo->prepare("INSERT INTO pagine_cms (slug, titolo, contenuto) VALUES (?,?,?)");
    $stmt->execute(['micologia_ciclo', 'Il ciclo riproduttivo dei funghi',
        "Il fungo che vediamo nel bosco (il carpoforo) è solo l'organo riproduttivo di un organismo che vive per lo più nel terreno o nel legno sotto forma di micelio, una rete di filamenti (ife). Quando le condizioni di umidità e temperatura sono favorevoli, il micelio produce il carpoforo, che rilascia le spore. Le spore, disperse dal vento o dagli animali, germinano generando nuovo micelio e chiudendo il ciclo.\n\nSostituisci questo testo dal pannello amministratore con i contenuti definitivi del circolo."]);
    $stmt->execute(['micologia_nutrizione', 'I sistemi nutrizionali',
        "I funghi si nutrono per assorbimento e si dividono in tre grandi gruppi in base al modo in cui ottengono le sostanze nutritive: saprofiti (decompongono materia organica morta, come foglie e legno), simbionti micorrizici (vivono in associazione mutualistica con le radici degli alberi) e parassiti (si nutrono a spese di un organismo vivente, spesso danneggiandolo).\n\nSostituisci questo testo dal pannello amministratore con i contenuti definitivi del circolo."]);

    // --- Micotossicologia: esempio di sindrome ---
    $stmt = $pdo->prepare("INSERT INTO tossine (nome_sindrome, tempo_latenza, funghi_coinvolti, sintomi, gravita, note) VALUES (?,?,?,?,?,?)");
    $stmt->execute(['Sindrome falloidea', 'Lunga latenza (6–24 ore)', 'Amanita phalloides, Amanita verna, Lepiota tossiche',
        'Nausea, vomito, diarrea profusa dopo molte ore dal pasto, seguiti da un falso miglioramento e poi da danno epatico.',
        'Molto grave, può essere mortale',
        'La lunga latenza è ciò che la rende particolarmente pericolosa: al comparire dei sintomi il veleno è già in circolo. In caso di sospetto, recarsi immediatamente al pronto soccorso portando, se possibile, un campione del fungo consumato.']);

    // --- Riviste: esempio ---
    $stmt = $pdo->prepare("INSERT INTO pubblicazioni (titolo, data_pubblicazione, descrizione) VALUES (?,?,?)");
    $stmt->execute(['Sottobosco Notizie — n.1', '2026-01-15', 'Numero di apertura stagione: calendario uscite e schede delle specie primaverili.']);

    // --- Consiglio direttivo: esempio (senza foto reali) ---
    $stmt = $pdo->prepare("INSERT INTO consiglio (nome, ruolo, bio, ordine) VALUES (?,?,?,?)");
    $consiglio = [
        ['Nome Cognome', 'Presidente', 'Sostituisci con una breve biografia dal pannello amministratore.', 0],
        ['Nome Cognome', 'Vicepresidente', 'Sostituisci con una breve biografia dal pannello amministratore.', 1],
        ['Nome Cognome', 'Segretario', 'Sostituisci con una breve biografia dal pannello amministratore.', 2],
    ];
    foreach ($consiglio as $c) { $stmt->execute($c); }

    // --- Statuto e regolamento: testo segnaposto, l'amministratore carica il PDF definitivo ---
    $stmt = $pdo->prepare("INSERT INTO documento_sociale (slug, titolo, testo) VALUES (?,?,?)");
    $stmt->execute(['statuto', 'Statuto del circolo',
        "Questo è un testo segnaposto. Sostituiscilo dal pannello amministratore con il testo integrale dello statuto, oppure carica direttamente il PDF ufficiale: se presente, il PDF verrà proposto in cima alla pagina."]);
    $stmt->execute(['regolamento', 'Regolamento interno',
        "Questo è un testo segnaposto. Sostituiscilo dal pannello amministratore con il testo integrale del regolamento interno, oppure carica direttamente il PDF ufficiale."]);

    // --- In cucina: ricetta dimostrativa (senza foto reale) ---
    $stmt = $pdo->prepare("INSERT INTO ricetta
        (titolo, categoria, funghi_utilizzati, tempo_preparazione, difficolta, ingredienti, procedimento, abbinamento_vino, ordine)
        VALUES (?,?,?,?,?,?,?,?,?)");
    $stmt->execute([
        'Tagliatelle ai porcini', 'Primo piatto', 'Porcino (Boletus edulis)', '35 minuti', 'Facile',
        "400 g di tagliatelle fresche\n300 g di porcini freschi (o 30 g secchi ammollati)\n2 spicchi d'aglio\nPrezzemolo fresco\nOlio extravergine d'oliva\nSale e pepe q.b.",
        "Pulisci i porcini con un panno umido, evitando di lavarli sotto l'acqua. Tagliali a fette non troppo sottili.\n\nIn una padella larga, scalda l'olio con l'aglio in camicia, poi rimuovilo appena dorato.\n\nAggiungi i porcini e fai saltare a fuoco vivo per 5-6 minuti, salando solo a fine cottura.\n\nCuoci le tagliatelle in abbondante acqua salata, scolale al dente e saltale in padella con i porcini per un minuto, aggiungendo un mestolo di acqua di cottura se necessario.\n\nCompleta con prezzemolo fresco tritato e una macinata di pepe.",
        'Un bianco strutturato come un Verdicchio dei Castelli di Jesi Riserva, oppure un rosso leggero e poco tannico come un Pinot Nero.',
        0,
    ]);

    // --- Chi siamo: contenuto iniziale, allineato al testo storico della home ---
    $stmt = $pdo->prepare("INSERT INTO pagina_chi_siamo (id, testo, soci_numero, soci_etichetta, anni_numero, anni_etichetta) VALUES (1,?,?,?,?,?)");
    $stmt->execute([
        "Sottobosco nasce come punto d'incontro tra chi cammina nei boschi da una vita e chi si affaccia oggi alla micologia. Organizziamo uscite guidate, corsi di riconoscimento e teniamo un archivio fotografico e scientifico curato dal comitato scientifico del circolo.",
        '210+', 'Soci attivi', '38', 'Anni di attività',
    ]);
}

/**
 * Applica piccole migrazioni allo schema di un database già esistente
 * (creato con una versione precedente del sito), senza toccare i dati
 * già presenti. Va chiamata ad ogni richiesta: i controlli sono innocui
 * e velocissimi se non c'è nulla da fare.
 */
function aggiorna_database(PDO $pdo): void
{
    $tabelle = $pdo->query("SELECT name FROM sqlite_master WHERE type='table'")
        ->fetchAll(PDO::FETCH_COLUMN);

    if (!in_array('home_slide', $tabelle, true)) {
        $pdo->exec("
            CREATE TABLE home_slide (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                path TEXT NOT NULL,
                ordine INTEGER NOT NULL DEFAULT 0,
                creato_il TEXT DEFAULT CURRENT_TIMESTAMP
            );
        ");
    }

    if (!in_array('lezioni_corso', $tabelle, true)) {
        $pdo->exec("
            CREATE TABLE lezioni_corso (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                titolo TEXT NOT NULL,
                data_lezione TEXT,
                descrizione TEXT,
                pdf_path TEXT,
                link_esterno TEXT,
                ordine INTEGER NOT NULL DEFAULT 0,
                creato_il TEXT DEFAULT CURRENT_TIMESTAMP
            );
        ");
    }

    if (!in_array('legislazione', $tabelle, true)) {
        $pdo->exec("
            CREATE TABLE legislazione (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                regione TEXT NOT NULL,
                titolo TEXT NOT NULL,
                testo TEXT,
                pdf_path TEXT,
                link_esterno TEXT,
                aggiornato_il TEXT DEFAULT CURRENT_TIMESTAMP
            );
        ");
    }

    if (!in_array('tossine', $tabelle, true)) {
        $pdo->exec("
            CREATE TABLE tossine (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                nome_sindrome TEXT NOT NULL,
                tempo_latenza TEXT,
                funghi_coinvolti TEXT,
                sintomi TEXT,
                gravita TEXT,
                note TEXT,
                pdf_path TEXT,
                creato_il TEXT DEFAULT CURRENT_TIMESTAMP
            );
        ");
    }

    if (!in_array('pubblicazioni', $tabelle, true)) {
        $pdo->exec("
            CREATE TABLE pubblicazioni (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                titolo TEXT NOT NULL,
                data_pubblicazione TEXT,
                descrizione TEXT,
                pdf_path TEXT,
                creato_il TEXT DEFAULT CURRENT_TIMESTAMP
            );
        ");
    }

    if (!in_array('pagine_cms', $tabelle, true)) {
        $pdo->exec("
            CREATE TABLE pagine_cms (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                slug TEXT UNIQUE NOT NULL,
                titolo TEXT,
                contenuto TEXT
            );
        ");
        $stmt = $pdo->prepare("INSERT INTO pagine_cms (slug, titolo, contenuto) VALUES (?,?,?)");
        $stmt->execute(['micologia_ciclo', 'Il ciclo riproduttivo dei funghi', 'Testo da inserire dal pannello amministratore.']);
        $stmt->execute(['micologia_nutrizione', 'I sistemi nutrizionali', 'Testo da inserire dal pannello amministratore.']);
    }

    if (!in_array('soci', $tabelle, true)) {
        $pdo->exec("
            CREATE TABLE soci (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                nome TEXT NOT NULL,
                username TEXT UNIQUE NOT NULL,
                password_hash TEXT NOT NULL,
                creato_il TEXT DEFAULT CURRENT_TIMESTAMP
            );
        ");
    }

    if (!in_array('messaggi_bacheca', $tabelle, true)) {
        $pdo->exec("
            CREATE TABLE messaggi_bacheca (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                socio_id INTEGER NOT NULL,
                testo TEXT NOT NULL,
                creato_il TEXT DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (socio_id) REFERENCES soci(id) ON DELETE CASCADE
            );
        ");
    }

    if (!in_array('consiglio', $tabelle, true)) {
        $pdo->exec("
            CREATE TABLE consiglio (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                nome TEXT NOT NULL,
                ruolo TEXT,
                bio TEXT,
                foto_path TEXT,
                ordine INTEGER NOT NULL DEFAULT 0,
                creato_il TEXT DEFAULT CURRENT_TIMESTAMP
            );
        ");
    }

    if (!in_array('documento_sociale', $tabelle, true)) {
        $pdo->exec("
            CREATE TABLE documento_sociale (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                slug TEXT UNIQUE NOT NULL CHECK(slug IN ('statuto','regolamento')),
                titolo TEXT,
                testo TEXT,
                pdf_path TEXT,
                aggiornato_il TEXT DEFAULT CURRENT_TIMESTAMP
            );
        ");
        $stmt = $pdo->prepare("INSERT INTO documento_sociale (slug, titolo, testo) VALUES (?,?,?)");
        $stmt->execute(['statuto', 'Statuto del circolo', 'Testo da inserire dal pannello amministratore, oppure carica direttamente il PDF ufficiale.']);
        $stmt->execute(['regolamento', 'Regolamento interno', 'Testo da inserire dal pannello amministratore, oppure carica direttamente il PDF ufficiale.']);
    }

    if (!in_array('ricetta', $tabelle, true)) {
        $pdo->exec("
            CREATE TABLE ricetta (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                titolo TEXT NOT NULL,
                categoria TEXT,
                funghi_utilizzati TEXT,
                tempo_preparazione TEXT,
                difficolta TEXT,
                ingredienti TEXT,
                procedimento TEXT,
                abbinamento_vino TEXT,
                foto_path TEXT,
                ordine INTEGER NOT NULL DEFAULT 0,
                creato_il TEXT DEFAULT CURRENT_TIMESTAMP
            );
        ");
    }

    if (!in_array('pagina_chi_siamo', $tabelle, true)) {
        $pdo->exec("
            CREATE TABLE pagina_chi_siamo (
                id INTEGER PRIMARY KEY CHECK (id = 1),
                testo TEXT,
                foto_path TEXT,
                soci_numero TEXT,
                soci_etichetta TEXT,
                anni_numero TEXT,
                anni_etichetta TEXT
            );
        ");
        // Il testo di partenza è lo stesso che era scritto direttamente in
        // index.php prima di questa versione: chi aggiorna un sito già
        // online continua a vedere lo stesso contenuto, semplicemente ora
        // modificabile dal pannello invece che dal codice.
        $stmt = $pdo->prepare("INSERT INTO pagina_chi_siamo (id, testo, soci_numero, soci_etichetta, anni_numero, anni_etichetta) VALUES (1,?,?,?,?,?)");
        $stmt->execute([
            "Sottobosco nasce come punto d'incontro tra chi cammina nei boschi da una vita e chi si affaccia oggi alla micologia. Organizziamo uscite guidate, corsi di riconoscimento e teniamo un archivio fotografico e scientifico curato dal comitato scientifico del circolo.",
            '210+', 'Soci attivi', '38', 'Anni di attività',
        ]);
    }

    if (!in_array('confronto', $tabelle, true)) {
        $pdo->exec("
            CREATE TABLE confronto (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                titolo TEXT,
                specie_buona_id INTEGER NOT NULL,
                specie_cattiva_id INTEGER NOT NULL,
                note_confronto TEXT,
                ordine INTEGER NOT NULL DEFAULT 0,
                creato_il TEXT DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (specie_buona_id) REFERENCES specie(id) ON DELETE CASCADE,
                FOREIGN KEY (specie_cattiva_id) REFERENCES specie(id) ON DELETE CASCADE
            );
        ");
    }

    if (!in_array('galleria_soci', $tabelle, true)) {
        $pdo->exec("
            CREATE TABLE galleria_soci (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                path TEXT NOT NULL,
                didascalia TEXT,
                ordine INTEGER NOT NULL DEFAULT 0,
                creato_il TEXT DEFAULT CURRENT_TIMESTAMP
            );
        ");
    }

    // Colonna "gruppo" (classificazione tassonomica) aggiunta in un secondo
    // momento alla tabella specie: non tocca le specie già inserite, che
    // restano semplicemente senza gruppo finché non le modifichi.
    $colonneSpecie = $pdo->query("PRAGMA table_info(specie)")->fetchAll(PDO::FETCH_COLUMN, 1);
    if (!in_array('gruppo', $colonneSpecie, true)) {
        $pdo->exec('ALTER TABLE specie ADD COLUMN gruppo TEXT');
    }
    if (!in_array('nomi_alternativi', $colonneSpecie, true)) {
        $pdo->exec('ALTER TABLE specie ADD COLUMN nomi_alternativi TEXT');
    }

    // Colonne aggiunte in un secondo momento alla tabella eventi (immagine e
    // allegato): si verifica con PRAGMA table_info e si aggiunge solo se manca,
    // senza toccare gli eventi già inseriti.
    $colonneEventi = $pdo->query("PRAGMA table_info(eventi)")->fetchAll(PDO::FETCH_COLUMN, 1);
    if (!in_array('immagine_path', $colonneEventi, true)) {
        $pdo->exec('ALTER TABLE eventi ADD COLUMN immagine_path TEXT');
    }
    if (!in_array('allegato_path', $colonneEventi, true)) {
        $pdo->exec('ALTER TABLE eventi ADD COLUMN allegato_path TEXT');
    }
    if (!in_array('allegato_nome_originale', $colonneEventi, true)) {
        $pdo->exec('ALTER TABLE eventi ADD COLUMN allegato_nome_originale TEXT');
    }
}
