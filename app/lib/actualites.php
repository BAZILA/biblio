<?php
/**
 * Actualités et évènements : page Actualités du site, page propre à chacun, et diapositives de l'accueil.
 * Le diaporama de l'accueil enchaîne les diapositives fixes (bienvenue…) puis les actualités et évènements
 * cochés « au diaporama », les plus récents d'abord.
 */

const TYPES_ACTUALITE = ['actualite' => 'Actualité', 'evenement' => 'Évènement'];

/** Effets d'entrée des diapositives. */
const ANIMATIONS_DIAPO = [
    'zoom'        => 'Zoom lent (effet Ken Burns)',
    'panoramique' => 'Panoramique (l’image glisse doucement)',
    'fondu'       => 'Fondu enchaîné',
    'glissement'  => 'Glissement latéral',
    'rideau'      => 'Rideau (ouverture depuis le centre)',
    'flou'        => 'Mise au point (du flou au net)',
];

const MOIS_COURTS = ['', 'janv.', 'févr.', 'mars', 'avr.', 'mai', 'juin', 'juil.', 'août', 'sept.', 'oct.', 'nov.', 'déc.'];

function actualite_url(array $a): string
{
    return url('actualites/' . $a['slug']);
}

/** Publiées et dont la date de publication est passée (une actualité peut être programmée). */
const ACTUALITE_VISIBLE = 'a.publie = 1 AND a.date_publication <= NOW()';

function actualites(string $where = '1', array $params = [], string $ordre = 'a.date_publication DESC, a.id DESC', ?int $limite = null, int $offset = 0): array
{
    $sql = 'SELECT a.* FROM actualites a WHERE ' . ACTUALITE_VISIBLE . " AND ($where) ORDER BY $ordre";
    if ($limite !== null) {
        $sql .= ' LIMIT ' . (int) $limite . ' OFFSET ' . (int) $offset;
    }
    return rows($sql, $params);
}

/** Évènements à venir (ou en cours), du plus proche au plus lointain. */
function evenements_a_venir(int $limite = 4): array
{
    return actualites("a.type = 'evenement' AND COALESCE(a.fin, a.debut, a.date_publication) >= CURDATE()", [], 'COALESCE(a.debut, a.date_publication), a.id', $limite);
}

/** « 12 nov. 2026 à 15 h », ou « du 12 au 14 nov. 2026 ». */
function date_evenement(array $a): string
{
    if (!$a['debut']) {
        return '';
    }
    $d = strtotime($a['debut']);
    $jour = fn(int $t, bool $annee = true) => (int) date('j', $t) . (date('j', $t) === '1' ? 'er' : '') . ' ' . MOIS_COURTS[(int) date('n', $t)] . ($annee ? ' ' . date('Y', $t) : '');
    $heure = date('H:i', $d) !== '00:00' ? ' à ' . date('G', $d) . ' h' . (date('i', $d) !== '00' ? ' ' . date('i', $d) : '') : '';
    if ($a['fin'] && date('Y-m-d', strtotime($a['fin'])) !== date('Y-m-d', $d)) {
        $f = strtotime($a['fin']);
        return 'du ' . $jour($d, date('Y', $d) !== date('Y', $f)) . ' au ' . $jour($f);
    }
    return $jour($d) . $heure;
}

/** Date d'une actualité en clair (« 12 nov. 2026 »). */
function date_courte(string $date): string
{
    $t = strtotime($date);
    return (int) date('j', $t) . ' ' . MOIS_COURTS[(int) date('n', $t)] . ' ' . date('Y', $t);
}

/**
 * Diapositives de l'accueil, dans l'ordre : diapositives fixes publiées, puis actualités et évènements « au diaporama ».
 * Chaque diapositive : titre, texte, image, video, animation, boutons [[libellé, lien, principal?]], etiquette.
 */
function diapositives_accueil(): array
{
    $liste = [];
    foreach (rows('SELECT * FROM slides WHERE publie = 1 ORDER BY ordre, id') as $s) {
        $liste[] = [
            'titre' => $s['titre'], 'texte' => (string) $s['texte'], 'image' => $s['image'], 'video' => $s['video'] ?? '',
            'animation' => $s['animation'] ?? 'zoom', 'etiquette' => '',
            'boutons' => $s['bouton'] !== '' ? [[$s['bouton'], lien_diapositive($s['lien']), true]] : [],
        ];
    }
    $max = reglage_int('diaporama_actualites_max', 6);
    foreach ($max > 0 ? actualites('a.au_diaporama = 1', [], 'a.date_publication DESC, a.id DESC', $max) : [] as $a) {
        $etiquette = TYPES_ACTUALITE[$a['type']] . ($a['type'] === 'evenement' && $a['debut'] ? ' · ' . date_evenement($a) : ' · ' . date_courte($a['date_publication']));
        $liste[] = [
            'titre' => $a['titre'], 'texte' => $a['chapo'], 'image' => $a['image'], 'video' => $a['video'], 'animation' => $a['animation'],
            'etiquette' => $etiquette . ($a['type'] === 'evenement' && $a['lieu'] !== '' ? ' · ' . $a['lieu'] : ''),
            'boutons' => [['En savoir plus', actualite_url($a), true], ['Toutes les actualités', url('actualites'), false]],
        ];
    }
    return $liste;
}

/** Lien d'un bouton de diaporama : chemin interne (« catalogue ») ou URL complète. */
function lien_diapositive(string $lien): string
{
    return preg_match('#^(https?:)?//|^/|^\#|^mailto:#', $lien) ? $lien : url($lien);
}

/** Type MIME d'une vidéo pour la balise <source>. */
function type_video(string $chemin): string
{
    return str_ends_with(strtolower($chemin), '.webm') ? 'video/webm' : 'video/mp4';
}
