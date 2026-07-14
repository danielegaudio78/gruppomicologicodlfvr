# Sottobosco — Circolo Micologico (versione PHP)

Sito in PHP + SQLite, senza dipendenze esterne da installare. Homepage con
carosello fotografico rotante, database specie con schede tecniche e PDF,
corso di micologia annuale, calendario eventi, legislazione regionale,
pagine di micologia e micotossicologia, riviste del circolo, bacheca
interna tra soci, area amministratore che gestisce tutto quanto sopra.

## Requisiti del server

- PHP 8.0 o superiore, con estensione **pdo_sqlite** e **fileinfo** (presenti
  di serie sulla quasi totalità degli hosting condivisi: cPanel, Plesk, ecc.)
- Apache con **mod_rewrite/mod_authz_core** disponibile e **AllowOverride All**
  attivo sulla cartella del sito, perché la protezione delle cartelle
  `uploads/`, `db/` e `includes/` è affidata ai file `.htaccess`.
  → Se il sito è su **nginx** questi `.htaccess` NON vengono letti: usa il
  file `nginx.conf.example` incluso in questa cartella come base per
  configurare il blocco `server{}` del tuo hosting.
- Cartelle `db/`, `uploads/foto/` e `uploads/pdf/` scrivibili dal server
  (permessi tipici 755 o 775 a seconda dell'hosting).

## Installazione

1. Carica tutto il contenuto di questa cartella nella root del tuo hosting
   (es. `public_html/`), mantenendo la struttura delle sottocartelle.
2. Apri `https://tuosito.it/install.php`: crea qui il primo account
   amministratore (nome utente + password).
3. **Elimina subito il file `install.php`** dal server: è utilizzabile una
   sola volta e non permette di ricreare un secondo account, ma è comunque
   buona norma rimuoverlo.
4. Accedi da `https://tuosito.it/admin/login.php` e inizia a caricare le
   specie del database.
5. Se vuoi personalizzare nome del circolo, indirizzo, email e link social,
   modifica le costanti in cima a `includes/config.php`.

Il database SQLite (`db/circolo.sqlite`) viene creato automaticamente al
primo accesso, con 4 specie e 3 eventi dimostrativi già inseriti (senza
foto: le aggiungerai tu dal pannello admin).

## Il carosello della homepage

Le fotografie che ruotano in home **non sono legate al database dei
funghi**: sono gestite a parte da `/admin/home_slides.php`, dove puoi
caricarne più di una insieme, riordinarle (frecce su/giù) ed eliminarle.
Se non ne carichi nessuna, la homepage mostra un messaggio di benvenuto al
posto del carosello.

Le immagini vengono mostrate **centrate** e ritagliate automaticamente per
riempire tutta la larghezza dello schermo (la stessa tecnica del sito preso
come riferimento). Dimensioni consigliate per la fotografia da caricare:

- **Orientamento orizzontale (paesaggio)**, non verticale.
- **Almeno 1920×1080 px** (formato 16:9); idealmente **2400×1350 px** o
  poco più, per restare nitide anche su monitor grandi o ad alta densità.
- Evita di superare i **4000 px di larghezza**: non aggiunge qualità
  visibile e appesantisce solo il caricamento.
- Peso del file: JPEG di qualità 80–85% resta di norma sotto i 2 MB;
  il limite massimo impostato è **10 MB** per foto (costante `MAX_SLIDE_MB`
  in `includes/config.php`, modificabile).
- Tieni il soggetto principale verso il **centro** dell'inquadratura,
  senza elementi importanti troppo vicini al bordo superiore (coperto
  dal menu) o a quello inferiore (scurito dal testo in sovrimpressione).

## Il database delle specie (foto separate)

Ogni specie ha una propria galleria di fotografie indipendente da quella
del carosello, caricata da `/admin/specie_form.php`: stesse indicazioni di
formato (JPG/PNG/WEBP), ma qui va bene anche l'orientamento verticale,
dato che le foto di scheda vengono mostrate in un riquadro 4:3 e non a
piena pagina.


## Come funziona l'area amministratore

- Login su `/admin/login.php`, protetto da password con hash sicuro
  (`password_hash`) e da un blocco temporaneo dopo tentativi ripetuti.
- Da `/admin/dashboard.php` l'amministratore crea/modifica le specie: nome
  comune, nome scientifico, commestibilità, scheda tecnica (habitat,
  periodo, cappello, gambo, imenio, spore, note), **più fotografie per ogni
  specie** (caricabili anche tutte insieme) e **un PDF** con la scheda
  tecnica completa.
- Dallo stesso pannello si gestiscono anche tutte le nuove sezioni
  descritte sotto: corso, calendario/eventi, legislazione, micologia,
  micotossicologia, riviste e account soci.
- Solo l'amministratore può scrivere contenuti sul sito pubblico: non
  esiste un'area di upload aperta a chiunque.

## Corso di micologia annuale

Pagina pubblica `/corso.php`, gestita da `/admin/corso_form.php`. Ogni
lezione ha un titolo, una data, una descrizione e **due modi alternativi
(o entrambi insieme) per collegare il materiale**: un PDF caricato
direttamente sul sito, oppure un link a una cartella condivisa esterna
(SharePoint, Google Drive, ecc.) — utile quando il materiale di una
lezione è composto da più file o è troppo pesante per un singolo PDF.

## Calendario

`/calendario.php` mostra a **vista mensile a griglia** (con frecce
mese precedente/successivo) esattamente gli stessi appuntamenti già
gestiti in `/admin/eventi_form.php` per la pagina `/eventi.php`: non c'è
un secondo calendario da tenere aggiornato a parte, i due sono la stessa
fonte dati mostrata in due modi diversi (timeline ed elenco vs. griglia).

## Legislazione regionale

`/legislazione.php`, gestita da `/admin/legislazione_form.php`: una scheda
per regione con testo di sintesi, PDF del regolamento e/o link alla fonte
ufficiale. Puoi aggiungerne quante ne servono (anche più schede per la
stessa regione, ad es. per aggiornamenti annuali).

## Micologia e Micotossicologia

- `/micologia.php` (gestita da `/admin/micologia_form.php`) contiene due
  sezioni di testo libero già predisposte — ciclo riproduttivo e sistemi
  nutrizionali — che l'amministratore scrive e modifica come testo
  semplice (va a capo da solo, non serve alcun codice).
- `/micotossicologia.php` (gestita da `/admin/tossine_form.php`) è invece
  un elenco di schede, una per sindrome da avvelenamento (latenza, funghi
  coinvolti, sintomi, gravità, PDF opzionale), con un promemoria di
  sicurezza sempre visibile in cima alla pagina.

## Riviste

`/riviste.php`, gestita da `/admin/pubblicazioni_form.php`: elenco delle
pubblicazioni periodiche del circolo, ciascuna con data, descrizione e PDF.

## Bacheca interna tra soci (chat)

Sistema di accesso **separato da quello dell'amministratore**:

- Solo l'amministratore può creare o eliminare un account socio, da
  `/admin/soci.php` (nome, username, password iniziale). Non esiste
  auto-registrazione: è una scelta di sicurezza, per evitare che chiunque
  possa iscriversi da solo alla bacheca interna.
- Il socio accede da `/soci/login.php` e trova in `/soci/bacheca.php` una
  bacheca di messaggi condivisa tra tutti i soci collegati, che si
  aggiorna da sola ogni 6 secondi (senza bisogno di ricaricare la pagina)
  tramite l'endpoint `/soci/bacheca_dati.php`.
- Non è una chat "in tempo reale" con websocket: è un aggiornamento
  periodico (polling), la soluzione più semplice e robusta da ospitare su
  un hosting PHP condiviso qualunque, con un ritardo massimo percepibile
  di pochi secondi tra un messaggio e l'altro.
- **Limite attuale:** l'amministratore non ha ancora, da questa versione,
  un pulsante per eliminare un messaggio scritto da un socio (utile per
  moderazione). Se ti serve, è un'aggiunta rapida da fare in un passo
  successivo.

## Protezione delle fotografie e dei PDF — cosa è realistico aspettarsi

Ho messo in campo le protezioni standard usate dai siti professionali:

- le foto e i PDF **non hanno un URL diretto**: sono serviti da
  `serve_image.php` e `serve_pdf.php`, che leggono il file dal database e
  lo trasmettono senza mai rivelare il percorso reale; le cartelle
  `uploads/foto` e `uploads/pdf` sono inoltre bloccate a livello di server;
- i file vengono inviati con `Content-Disposition: inline` e
  `Cache-Control: no-store`, così il browser non propone subito il
  salvataggio né lo mette in cache;
- tasto destro e trascinamento sono disabilitati sulle immagini del sito;
- le foto principali (carosello in home, copertine del database, foto
  grande nella scheda specie) mostrano una **filigrana visibile** ripetuta
  con il nome del circolo (generata automaticamente in `includes/header.php`
  a partire dalla costante `NOME_CIRCOLO`): chiunque salvi comunque
  l'immagine ottiene un file marchiato, riconoscibile come proprietà del
  circolo.

Detto onestamente: **nessuna di queste tecniche può impedire in modo
assoluto** che una persona salvi un contenuto mostrato nel proprio browser
— con uno screenshot, gli strumenti sviluppatore, o (per i PDF) il tasto
"salva" del lettore integrato. Nessun sito web, incluso quello preso come
riferimento, può garantirlo davvero. Il prossimo passo tipico, se un giorno
vorrete un livello di protezione più alto, è tenere online solo versioni a
bassa risoluzione delle foto e conservare gli originali ad alta qualità
soltanto lato amministratore.

## Chi siamo: consiglio direttivo e statuto

Il menu "Chi siamo" ora è un menu a tendina con tre voci:

- **Il circolo** (`/index.php#chi-siamo`) — presentazione generale, invariata.
- **Consiglio direttivo** (`/consiglio-direttivo.php`, gestita da
  `/admin/consiglio_form.php`): una scheda per ogni membro, con nome,
  carica, breve biografia e fotografia opzionale.
- **Statuto e regolamento** (`/statuto.php`, gestita da
  `/admin/statuto_form.php`): due documenti distinti (Statuto e Regolamento
  interno), ciascuno con testo libero e/o PDF ufficiale caricabile — se
  presente, il PDF viene proposto in evidenza in cima al testo.

## Eventi: immagine e allegato

Ogni evento — sia nella timeline (`/eventi.php`) sia nella scheda gestita da
`/admin/eventi_form.php` — può avere:

- **un'immagine** (JPG, PNG o WEBP), mostrata nella pagina pubblica;
- **un allegato di qualunque tipo** (PDF, Word, Excel, zip, ecc.), proposto
  come download vero e proprio con il suo nome file originale, non con un
  nome casuale — a differenza dei PDF di scheda usati altrove nel sito,
  qui l'obiettivo è che il socio scarichi il documento, non che lo legga
  solo a schermo.

Entrambi sono facoltativi e sostituibili: caricandone uno nuovo, il
precedente viene rimosso automaticamente anche dal disco; è anche
possibile rimuoverli senza sostituirli con l'apposita casella "Rimuovi".

## Banner cookie e privacy

Il sito mostra a ogni visitatore, alla prima visita, un banner con due
pulsanti dello stesso peso visivo — **Accetta** e **Solo necessari** —
niente pulsante preselezionato o più evidente dell'altro, come richiesto
dalle linee guida del Garante Privacy. La scelta viene salvata in un
cookie (`consenso_cookie`, 180 giorni) e può essere cambiata in qualunque
momento dal link **Gestisci cookie** nel footer di ogni pagina.

Oggi il sito usa solo cookie tecnici (la sessione di accesso soci/admin e
il cookie che ricorda la scelta stessa nel banner): non c'è nulla da
attivare dopo il consenso. Lo script è comunque pronto per governare
eventuali strumenti futuri (es. Google Analytics): prima di caricarli,
basta controllare `window.consenso.consentito('statistiche')` in
`assets/js/cookie-consent.js` — se restituisce `false`, lo script non va
caricato.

La pagina `/privacy-cookie.php` spiega quali cookie sono in uso, la loro
durata e come gestirli; è collegata dal footer di ogni pagina. **Se in
futuro aggiungerai strumenti di terze parti (analytics, mappe embeddate,
video, ecc.), aggiorna sia questa pagina sia la lista delle categorie nel
banner**, perché a quel punto servirà un consenso specifico prima di
attivarli, non più genericamente "accetta tutto".

## Indicizzazione da Google e download da parte dei visitatori

Sono due obiettivi in parte in tensione, ed è giusto saperlo prima di
configurare il sito: perché un'immagine sia indicizzabile da Google
Immagini, Googlebot deve poterla scaricare — non è possibile lasciarla
passare a Google e bloccarla a chiunque altro, perché "riconoscere Google"
in modo affidabile non esiste (chiunque può far finta di esserlo) e
mostrare contenuti diversi a Google rispetto ai visitatori reali
("cloaking") viola le linee guida di Google ed espone a penalizzazioni.

Quello che il sito fa concretamente:

- **`robots.txt` e gli script che servono le immagini** (`serve_image.php`,
  `serve_slide.php`, `serve_consiglio_foto.php`, `serve_evento_immagine.php`)
  sono pubblici e indicizzabili: non serve più login né altro per vederli,
  esattamente come qualunque immagine di un sito normale.
- **`sitemap-immagini.php`** genera automaticamente una sitemap XML nel
  formato "Image sitemap" che Google usa per scoprire più in fretta tutte
  le fotografie del sito (specie, consiglio direttivo, eventi), collegata
  in `robots.txt`. Puoi anche inviarla manualmente da Google Search
  Console. **Prima di andare online, aggiorna la costante `URL_SITO` in
  `includes/config.php`** con il dominio reale: senza quella, la sitemap
  genera indirizzi con un dominio segnaposto.
- Gli header di cache delle immagini sono ora `public, max-age=86400`
  invece di `no-store`: questo aiuta l'indicizzazione e le prestazioni,
  ma **non è mai stato l'header a impedire il download** — un utente può
  sempre salvare un'immagine mostrata dal proprio browser, con o senza
  quell'header.
- **Quello che scoraggia davvero il download occasionale resta invariato**:
  la filigrana visibile con il nome del circolo su tutte le foto pubbliche,
  e il blocco di tasto destro/trascinamento. Sono limiti "di attrito", non
  assoluti — vale lo stesso avvertimento onesto di prima: chi vuole salvare
  un'immagine mostrata in un browser può sempre farlo con uno screenshot.

In pratica: **le immagini indicizzate da Google Immagini porteranno
comunque la filigrana**, perché è la stessa immagine mostrata sul sito.
È il compromesso più realistico tra "farsi trovare su Google" e "rendere
la copia non autorizzata riconoscibile come proprietà del circolo".

## Ottimizzazione SEO

Interventi fatti per l'indicizzazione sui motori di ricerca:

- **Meta description unica per ogni pagina** (invece di una sola ripetuta
  ovunque, che Google penalizza come contenuto duplicato). Ogni pagina
  imposta `$metaDescription` prima di includere `includes/header.php`;
  le schede specie (`specie.php`) la generano **automaticamente** dal nome,
  nome scientifico, habitat e periodo di ciascun fungo, quindi ogni fungo
  ha una descrizione diversa senza che tu debba scriverle a mano.
- **Tag Open Graph e Twitter Card** su ogni pagina (titolo, descrizione,
  immagine, URL): quando un link al sito viene condiviso su Facebook,
  WhatsApp, LinkedIn ecc. mostra un'anteprima curata invece di un link nudo.
  Le schede specie usano come immagine la prima foto della galleria.
- **URL canonico** su ogni pagina, per evitare che Google interpreti
  varianti dello stesso URL come contenuti duplicati.
- **Dati strutturati (JSON-LD)**: un blocco "Organization" con nome,
  indirizzo e social del circolo su ogni pagina; un blocco "Event" per
  ciascun appuntamento in `/eventi.php` (può far comparire gli eventi
  direttamente nei risultati di ricerca Google, con data e luogo); un
  blocco "Article" per ogni scheda specie.
- **Due sitemap XML**, entrambe generate automaticamente dal database
  (non richiedono manutenzione): `sitemap.php` per le pagine e
  `sitemap-immagini.php` per le foto, entrambe collegate in `robots.txt`
  — puoi anche inviarle manualmente da Google Search Console.
- **`loading="lazy"`** sulle immagini sotto la piega (schede del database,
  miniature della galleria, foto del consiglio): velocizza il caricamento
  della pagina, un fattore che Google considera nel posizionamento.
  La prima foto di ogni scheda specie resta a caricamento immediato,
  perché è tipicamente il primo contenuto visibile della pagina.
- **Pagina 404 personalizzata** (`404.php`, collegata in `.htaccess` e nel
  file `nginx.conf.example`) coerente con il resto del sito, invece della
  pagina di errore generica del server.
- **`robots.txt`** ora blocca anche `/soci/` dall'indicizzazione (area di
  login, non contenuto utile in un risultato di ricerca).

**Un passo che resta da fare tu**: tutte queste funzioni usano la costante
`URL_SITO` in `includes/config.php` per generare indirizzi assoluti —
aggiornala con il dominio reale prima di andare online, altrimenti canonical,
Open Graph e sitemap punteranno al dominio segnaposto.

## In cucina (ricette)

Nuova sezione `/cucina.php` (elenco) e `/ricetta.php?id=X` (dettaglio),
gestita da `/admin/ricetta_form.php`: titolo, categoria, funghi utilizzati,
tempo di preparazione, difficoltà, ingredienti, procedimento, **abbinamento
vino** e una fotografia. Ogni ricetta ha anche dati strutturati "Recipe"
(Schema.org): se Google li riconosce, può mostrare la ricetta con foto,
ingredienti e tempo di preparazione direttamente nei risultati di ricerca.
La sezione è collegata sia nel menu principale sia nelle due sitemap XML.

## Chi siamo, ora editabile da admin

Il testo e la fotografia della sezione "Chi siamo" in homepage non sono
più scritti nel codice: si modificano da `/admin/chi_siamo_form.php`
(testo libero, numero soci ed etichetta, anni di attività ed etichetta,
fotografia). Il numero di specie schedate resta invece calcolato
automaticamente dal database, per restare sempre corretto senza doverlo
aggiornare a mano. Se non carichi nessuna fotografia, la sezione mostra
semplicemente il testo senza immagine, senza lasciare spazi vuoti.

## Mappa nel modulo contatti

Il footer di ogni pagina mostra ora una mappa di Google Maps con la
posizione del circolo, generata automaticamente dall'indirizzo in
`INDIRIZZO_CIRCOLO` (`includes/config.php`) — **non richiede una chiave
API** né un account Google Cloud, funziona con lo stesso indirizzo che hai
già configurato. Se preferisci un pin più preciso di quanto un indirizzo
testuale garantisca, puoi sostituire l'URL dell'iframe in
`includes/footer.php` con coordinate `latitudine,longitudine` al posto
del testo dell'indirizzo.

## Gruppo tassonomico nel database funghi

Oltre alla commestibilità (che resta invariata: è l'informazione di
sicurezza), ogni specie ha ora un **gruppo tassonomico** assegnabile da
`/admin/specie_form.php`, con un elenco fisso di dieci voci: Boleti,
Amanita, Lepiota sensu lato, Russula, Lactarius, Agaricus, Cortinarius,
Tricholoma, Ascomiceti, Aphyllophorales. È un menu a tendina, non un campo
libero: per aggiungere o cambiare le voci disponibili, modifica l'elenco
nella funzione `gruppi_tassonomici()` in `includes/functions.php` — è
l'unico punto da aggiornare, sia il form admin sia il filtro pubblico lo
leggono da lì.

Sul database pubblico (`/database.php`) il gruppo è disponibile come
ulteriore filtro, accanto a quelli già presenti per la commestibilità, e
compare come etichetta su ogni scheda e nella pagina di dettaglio della
specie. È facoltativo: le specie già inserite restano semplicemente senza
gruppo finché non le modifichi.

## Contenuti riservati ai soci: galleria fotografica e materiale del corso

Due contenuti sono ora visibili solo a chi ha eseguito l'accesso come
socio (o come amministratore):

- **Galleria fotografica soci** (`/soci/galleria.php`, gestita da
  `/admin/galleria_soci.php`): fotografie caricate dall'amministratore,
  con upload multiplo, riordino ed eliminazione — stesso meccanismo già
  usato per il carosello della home. La differenza è che qui la
  protezione non è solo "non c'è un link pubblico": lo script che serve
  le foto (`serve_galleria_soci_foto.php`) **rifiuta la richiesta con un
  403** a chiunque non abbia una sessione socio o amministratore valida,
  anche conoscendo l'indirizzo diretto della foto. Le foto di questa
  galleria non compaiono nelle sitemap né in nessuna pagina pubblica.
- **Materiale del corso di micologia** (`/corso.php`): il programma delle
  lezioni resta pubblico (titolo, data, descrizione), ma il PDF e il link
  alla cartella condivisa sono visibili e scaricabili solo dopo l'accesso;
  un visitatore non collegato vede invece un invito ad accedere. La stessa
  regola vale anche accedendo direttamente all'indirizzo del PDF.

Se in futuro vorrai rendere riservato allo stesso modo un altro tipo di
PDF (es. le riviste), basta aggiungere `'riservato' => true` alla voce
corrispondente nell'array `$mappa` di `serve_pdf.php`: il controllo di
accesso è già lì, si applica a qualunque tipo tu marchi in quel modo.

## Database funghi: filtro per genere e ricerca corretta

Su `/database.php` non ci sono più i pulsanti "commestibili / non commestibili
/ da verificare": la commestibilità resta visibile come etichetta su ogni
scheda (è comunque un'informazione di sicurezza), ma non filtra più
l'elenco. Al suo posto, il **gruppo tassonomico è ora un pulsante per
ciascuno dei dieci generi** (non più un menu a tendina): cliccandone uno
mostra fotografia e scheda tecnica di tutti i funghi di quel genere,
espandibile direttamente in pagina con "Scheda tecnica ▾" senza dover
aprire la scheda completa (che resta comunque disponibile, con galleria
multipla e PDF).

La ricerca ora funziona su **nome comune, nome scientifico e nomi
alternativi/sinonimi** insieme, ignorando maiuscole/minuscole e accenti.
Il campo "Nomi alternativi" si compila da `/admin/specie_form.php`: è
pensato per sinonimi dialettali o nomi con cui i soci potrebbero cercare
lo stesso fungo (es. "Ceppatello" per il Porcino) — compare nella ricerca
e nella scheda tecnica, non come nome principale della specie.

## Fungo buono, fungo cattivo (confronti)

Nuova sezione `/confronti.php` (elenco) e `/confronto.php` (dettaglio),
gestita da `/admin/confronto_form.php`: metti a confronto due schede già
presenti nel database funghi — tipicamente una specie commestibile e un
suo "sosia" tossico — mostrandole affiancate con foto e scheda tecnica,
più un testo libero "Come distinguerli" che scrivi tu una volta sola per
ogni coppia. Non serve inserire di nuovo foto o dati tecnici: arrivano
automaticamente dalle schede specie già esistenti, tu scegli solo quali
due confrontare.

Se elimini una specie che fa parte di un confronto, il confronto viene
rimosso automaticamente insieme ad essa (non resta un confronto "rotto"
con una scheda mancante).

## Struttura del progetto

```
index.php                Homepage con carosello
database.php             Elenco specie con filtri e ricerca
specie.php               Scheda di dettaglio (galleria + PDF)
eventi.php               Pagina eventi (timeline)
calendario.php           Stessi eventi, vista a griglia mensile
consiglio-direttivo.php  Membri del consiglio direttivo
statuto.php              Statuto e regolamento interno
cucina.php               Elenco ricette "In cucina"
ricetta.php              Dettaglio di una ricetta (con dati strutturati Recipe)
confronti.php            Elenco dei confronti "fungo buono, fungo cattivo"
confronto.php            Dettaglio di un confronto (due schede affiancate)
privacy-cookie.php        Informativa privacy e cookie
corso.php                Corso di micologia annuale (lezioni: PDF o link)
legislazione.php         Legislazione regionale (schede per regione)
micologia.php            Ciclo riproduttivo e sistemi nutrizionali (testo)
micotossicologia.php     Sindromi da avvelenamento (schede + PDF)
riviste.php              Pubblicazioni periodiche del circolo (PDF)
soci/login.php           Accesso soci (separato dall'amministratore)
soci/logout.php          Uscita socio
soci/bacheca.php         Bacheca interna tra soci (chat con polling)
soci/bacheca_dati.php    Endpoint JSON usato dalla bacheca
soci/galleria.php        Galleria fotografica riservata ai soci
serve_galleria_soci_foto.php   Distribuzione protetta (403 se non collegati) delle foto riservate
install.php              Setup del primo account admin (da eliminare dopo l'uso)
serve_image.php          Distribuzione protetta delle foto specie
serve_pdf.php            Distribuzione protetta dei PDF (tutte le sezioni)
serve_slide.php          Distribuzione protetta delle foto del carosello home
serve_consiglio_foto.php Distribuzione protetta delle foto del consiglio
serve_evento_immagine.php Distribuzione protetta delle immagini evento
serve_evento_allegato.php Download protetto degli allegati evento
includes/                Configurazione, funzioni, header/footer condivisi
admin/                   Pannello riservato: specie, carosello, eventi, corso,
                         legislazione, micologia, micotossicologia, riviste, soci
assets/                  CSS e JavaScript
uploads/foto, uploads/pdf, uploads/slide,
uploads/documenti/{corso,legislazione,riviste,tossine}
                         Cartelle dei file caricati (bloccate all'accesso diretto)
db/                      Database SQLite (creato/aggiornato automaticamente)
nginx.conf.example       Configurazione di riferimento per hosting su nginx
favicon.svg              Icona del sito
robots.txt               Esclude admin/uploads/db dall'indicizzazione
sitemap-immagini.php     Sitemap XML delle immagini per Google (aggiorna URL_SITO prima di usarla)
sitemap.php              Sitemap XML delle pagine per Google (aggiorna URL_SITO prima di usarla)
404.php                  Pagina di errore personalizzata
```

## Personalizzazioni rapide

- Colori, font e stile: `assets/css/style.css` (variabili in cima al file).
- Testi della homepage: `index.php`.
- Limiti massimi di upload: costanti `MAX_FOTO_MB` / `MAX_PDF_MB` in
  `includes/config.php`, più i valori `upload_max_filesize` / `post_max_size`
  nel file `.htaccess` principale (o nel pannello PHP del provider, se
  l'hosting non consente di sovrascriverli da `.htaccess`).
