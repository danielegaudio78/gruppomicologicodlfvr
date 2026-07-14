<?php
/** Scappa una stringa per l'output HTML in modo sintetico. */
function h(?string $s): string
{
    return htmlspecialchars($s ?? '', ENT_QUOTES, 'UTF-8');
}

/** True se un amministratore ha effettuato l'accesso. */
function is_admin(): bool
{
    return !empty($_SESSION['admin_id']);
}

/** Blocca l'accesso alla pagina se non si è amministratori. */
function require_admin(): void
{
    if (!is_admin()) {
        header('Location: /admin/login.php');
        exit;
    }
}

/** True se un socio ha effettuato l'accesso alla bacheca. */
function is_socio(): bool
{
    return !empty($_SESSION['socio_id']);
}

/** Blocca l'accesso alla pagina se non si è soci autenticati. */
function require_socio(): void
{
    if (!is_socio()) {
        header('Location: /soci/login.php');
        exit;
    }
}

/** True se la persona è autenticata come socio OPPURE come amministratore. */
function is_membro(): bool
{
    return is_socio() || is_admin();
}

/**
 * Blocca l'accesso se non si è né soci né amministratore. Usata per i
 * contenuti riservati ai soci (galleria, materiale del corso): in questo
 * modo anche l'amministratore può sempre vederli, senza dover avere
 * anche un account socio separato.
 */
function require_membro(): void
{
    if (!is_membro()) {
        header('Location: /soci/login.php');
        exit;
    }
}

/** Etichetta leggibile per la commestibilità. */
function etichetta_commestibilita(string $c): string
{
    return match ($c) {
        'si' => 'Commestibile',
        'no' => 'Non commestibile',
        default => 'Da verificare con esperto',
    };
}

/** Classe CSS del badge in base alla commestibilità. */
function classe_commestibilita(string $c): string
{
    return match ($c) {
        'si' => 'badge-si',
        'no' => 'badge-no',
        default => 'badge-verifica',
    };
}

/**
 * Lunghezza di una stringa: usa mb_strlen se l'estensione mbstring è
 * disponibile (quasi sempre, ma non garantita su ogni hosting), altrimenti
 * ricade su strlen per non generare un errore fatale.
 */
function lunghezza_testo(string $s): int
{
    return function_exists('mb_strlen') ? mb_strlen($s, 'UTF-8') : strlen($s);
}

/**
 * Elenco fisso dei gruppi tassonomici usati per classificare le specie,
 * condiviso tra il form admin e il filtro del database pubblico così va
 * aggiornato in un solo punto.
 */
function gruppi_tassonomici(): array
{
    return [
        'Boleti', 'Amanita', 'Lepiota sensu lato', 'Russula', 'Lactarius',
        'Agaricus', 'Cortinarius', 'Tricholoma', 'Ascomiceti', 'Aphyllophorales',
    ];
}

/** Taglia una stringa a una lunghezza massima, con lo stesso fallback. */
function taglia_testo(string $s, int $max): string
{
    if (function_exists('mb_substr')) {
        return mb_substr($s, 0, $max, 'UTF-8');
    }
    return substr($s, 0, $max);
}

/** Riassume un testo a una lunghezza massima, aggiungendo un suffisso solo se lo taglia davvero. */
function riassumi_testo(string $s, int $max, string $suffisso = '…'): string
{
    if (lunghezza_testo($s) <= $max) return $s;
    return rtrim(taglia_testo($s, $max)) . $suffisso;
}
function data_it(?string $iso): string
{
    if (!$iso) return '';
    $mesi = ['', 'gen', 'feb', 'mar', 'apr', 'mag', 'giu', 'lug', 'ago', 'set', 'ott', 'nov', 'dic'];
    $t = strtotime($iso);
    if (!$t) return h($iso);
    return date('d', $t) . ' ' . $mesi[(int)date('n', $t)] . ' ' . date('Y', $t);
}

/** Formatta una data/ora SQL (YYYY-MM-DD HH:MM:SS) in formato italiano leggibile. */
function data_ora_it(?string $sql): string
{
    if (!$sql) return '';
    $t = strtotime($sql);
    if (!$t) return h($sql);
    return data_it(date('Y-m-d', $t)) . ' alle ' . date('H:i', $t);
}

/**
 * Genera un nome file casuale e sicuro mantenendo l'estensione originale,
 * per evitare collisioni e problemi di path traversal.
 */
function nome_file_sicuro(string $nomeOriginale): string
{
    $ext = strtolower(pathinfo($nomeOriginale, PATHINFO_EXTENSION));
    $ext = preg_replace('/[^a-z0-9]/', '', $ext);
    return bin2hex(random_bytes(16)) . '.' . $ext;
}
