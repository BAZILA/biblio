<?php
/** Lecture du catalogue : domaines, types, auteurs, ouvrages et disponibilité. */

/** Colonnes et jointures communes à toutes les listes d'ouvrages. */
const OUVRAGE_SELECT = '
    SELECT o.*, d.nom AS domaine_nom, d.slug AS domaine_slug,
           t.nom AS type_nom, t.slug AS type_slug,
           c.nom AS collection_nom
    FROM ouvrages o
    JOIN domaines d ON d.id = o.domaine_id
    LEFT JOIN types t ON t.id = o.type_id
    LEFT JOIN collections c ON c.id = o.collection_id';

function domaines(): array
{
    return rows('
        SELECT d.*, (SELECT COUNT(*) FROM ouvrages o WHERE o.domaine_id = d.id AND o.publie = 1) AS nb
        FROM domaines d ORDER BY d.ordre, d.nom');
}

function domaine_par_slug(string $slug): ?array
{
    return row('SELECT * FROM domaines WHERE slug = ?', [$slug]);
}

function types(): array
{
    return rows('
        SELECT t.*, (SELECT COUNT(*) FROM ouvrages o WHERE o.type_id = t.id AND o.publie = 1) AS nb
        FROM types t ORDER BY t.ordre, t.nom');
}

function type_par_slug(string $slug): ?array
{
    return row('SELECT * FROM types WHERE slug = ?', [$slug]);
}

/** Auteurs ayant au moins un ouvrage publié, avec la liste de leurs titres. */
function auteurs(string $filtre = ''): array
{
    $params = [];
    $where = '';
    if ($filtre !== '') {
        $where = 'WHERE a.nom LIKE ?';
        $params[] = '%' . $filtre . '%';
    }
    return rows("
        SELECT a.*, COUNT(o.id) AS nb,
               GROUP_CONCAT(o.titre ORDER BY o.titre SEPARATOR ', ') AS titres
        FROM auteurs a
        JOIN ouvrage_auteur oa ON oa.auteur_id = a.id
        JOIN ouvrages o ON o.id = oa.ouvrage_id AND o.publie = 1
        $where
        GROUP BY a.id ORDER BY a.nom", $params);
}

function auteur_par_slug(string $slug): ?array
{
    return row('SELECT * FROM auteurs WHERE slug = ?', [$slug]);
}

/**
 * Liste d'ouvrages publiés, enrichis (auteurs, disponibilité).
 * $where est un fragment SQL sur l'alias « o » (ex. « o.domaine_id = ? »).
 */
function ouvrages(string $where = '1', array $params = [], string $ordre = 'o.titre', ?int $limite = null, int $offset = 0): array
{
    $sql = OUVRAGE_SELECT . " WHERE o.publie = 1 AND ($where) ORDER BY $ordre";
    if ($limite !== null) {
        $sql .= ' LIMIT ' . (int) $limite . ' OFFSET ' . (int) $offset;
    }
    return enrichir(rows($sql, $params));
}

function compter_ouvrages(string $where = '1', array $params = []): int
{
    return (int) val("SELECT COUNT(*) FROM ouvrages o WHERE o.publie = 1 AND ($where)", $params);
}

function ouvrage_par_slugs(string $domaineSlug, string $slug, bool $publieSeulement = true): ?array
{
    $o = row(OUVRAGE_SELECT . ' WHERE d.slug = ? AND o.slug = ?' . ($publieSeulement ? ' AND o.publie = 1' : ''), [$domaineSlug, $slug]);
    return $o ? enrichir([$o])[0] : null;
}

function ouvrage_par_id(int $id): ?array
{
    $o = row(OUVRAGE_SELECT . ' WHERE o.id = ?', [$id]);
    return $o ? enrichir([$o])[0] : null;
}

/** Ajoute à chaque ouvrage : auteurs (tableau), nb d'exemplaires, nb disponibles. */
function enrichir(array $ouvrages): array
{
    if (!$ouvrages) {
        return [];
    }
    $ids = array_column($ouvrages, 'id');
    $in  = implode(',', array_fill(0, count($ids), '?'));

    $auteurs = [];
    foreach (rows("SELECT oa.ouvrage_id, a.id, a.nom, a.slug FROM ouvrage_auteur oa JOIN auteurs a ON a.id = oa.auteur_id
                   WHERE oa.ouvrage_id IN ($in) ORDER BY oa.ordre, a.nom", $ids) as $r) {
        $auteurs[$r['ouvrage_id']][] = $r;
    }
    $stock = [];
    foreach (rows("SELECT ouvrage_id, SUM(statut <> 'retire') AS total, SUM(statut = 'disponible') AS dispo
                   FROM exemplaires WHERE ouvrage_id IN ($in) GROUP BY ouvrage_id", $ids) as $r) {
        $stock[$r['ouvrage_id']] = $r;
    }

    foreach ($ouvrages as &$o) {
        $o['auteurs']     = $auteurs[$o['id']] ?? [];
        $o['auteurs_txt'] = implode(', ', array_column($o['auteurs'], 'nom'));
        $o['nb_ex']       = (int) ($stock[$o['id']]['total'] ?? 0);
        $o['nb_dispo']    = (int) ($stock[$o['id']]['dispo'] ?? 0);
        $o['url']         = ouvrage_url($o);
    }
    return $ouvrages;
}

/**
 * URL de la couverture d'un ouvrage ; sans couverture : l'image par défaut choisie dans les réglages,
 * sinon l'illustration fournie avec le site.
 */
function couverture(?string $image): string
{
    if ($image) {
        return media($image);
    }
    $defaut = reglage('couverture_defaut');
    return $defaut !== '' ? media($defaut) : asset('site/couverture-defaut.svg');
}

function ouvrage_url(array $o): string
{
    return url('domaines/' . $o['domaine_slug'] . '/' . $o['slug']);
}

/** Libellé de disponibilité affiché sur le site (mêmes valeurs que l'ancien champ Joomla). */
function disponibilite(array $o): string
{
    if ($o['consultation_sur_place']) {
        return 'Consultation sur place';
    }
    if ($o['nb_dispo'] > 0) {
        return 'Disponible';
    }
    return $o['nb_ex'] > 0 ? 'Emprunté' : 'Indisponible';
}

/** Ouvrages précédent / suivant dans le même domaine (ordre alphabétique). */
function ouvrages_voisins(array $o): array
{
    $prec = row('SELECT o.slug, d.slug AS domaine_slug FROM ouvrages o JOIN domaines d ON d.id = o.domaine_id
                 WHERE o.domaine_id = ? AND o.publie = 1 AND o.titre < ? ORDER BY o.titre DESC LIMIT 1', [$o['domaine_id'], $o['titre']]);
    $suiv = row('SELECT o.slug, d.slug AS domaine_slug FROM ouvrages o JOIN domaines d ON d.id = o.domaine_id
                 WHERE o.domaine_id = ? AND o.publie = 1 AND o.titre > ? ORDER BY o.titre LIMIT 1', [$o['domaine_id'], $o['titre']]);
    return [$prec ? ouvrage_url($prec) : null, $suiv ? ouvrage_url($suiv) : null];
}

/**
 * Recherche plein texte simple : chaque mot doit apparaître dans le titre, le sous-titre,
 * le résumé, l'éditeur, la collection, la cote, l'ISBN, l'ISSN ou le nom d'un auteur.
 */
function rechercher(string $q): array
{
    $mots = array_filter(preg_split('/\s+/u', trim($q)) ?: [], fn($m) => mb_strlen($m) >= 2);
    if (!$mots) {
        return ['ouvrages' => [], 'auteurs' => []];
    }

    $conds = [];
    $params = [];
    foreach ($mots as $m) {
        $like = '%' . $m . '%';
        $conds[] = '(o.titre LIKE ? OR o.sous_titre LIKE ? OR o.resume LIKE ? OR o.editeur LIKE ? OR o.cote LIKE ? OR o.isbn LIKE ? OR o.issn LIKE ?
                    OR EXISTS (SELECT 1 FROM collections c2 WHERE c2.id = o.collection_id AND c2.nom LIKE ?)
                    OR EXISTS (SELECT 1 FROM ouvrage_auteur oa JOIN auteurs a ON a.id = oa.auteur_id
                               WHERE oa.ouvrage_id = o.id AND a.nom LIKE ?))';
        array_push($params, ...array_fill(0, 9, $like));
    }
    $ouvrages = ouvrages(implode(' AND ', $conds), $params);

    // Auteurs : par leur nom ou par le titre d'un de leurs ouvrages (comme l'index des auteurs de l'ancien site)
    $condsA = [];
    $paramsA = [];
    foreach ($mots as $m) {
        $condsA[] = '(a.nom LIKE ? OR EXISTS (SELECT 1 FROM ouvrage_auteur oa2 JOIN ouvrages o2 ON o2.id = oa2.ouvrage_id AND o2.publie = 1
                                            WHERE oa2.auteur_id = a.id AND o2.titre LIKE ?))';
        array_push($paramsA, '%' . $m . '%', '%' . $m . '%');
    }
    $auteurs = rows('
        SELECT a.*, COUNT(o.id) AS nb, GROUP_CONCAT(o.titre ORDER BY o.titre SEPARATOR \', \') AS titres
        FROM auteurs a JOIN ouvrage_auteur oa ON oa.auteur_id = a.id
        JOIN ouvrages o ON o.id = oa.ouvrage_id AND o.publie = 1
        WHERE ' . implode(' AND ', $condsA) . ' GROUP BY a.id ORDER BY a.nom', $paramsA);

    return ['ouvrages' => $ouvrages, 'auteurs' => $auteurs];
}

/** Chiffres clés de l'accueil. */
function chiffres_cles(): array
{
    return [
        'ouvrages' => (int) val('SELECT COUNT(*) FROM ouvrages WHERE publie = 1'),
        'domaines' => (int) val('SELECT COUNT(*) FROM domaines'),
        'auteurs'  => (int) val('SELECT COUNT(DISTINCT oa.auteur_id) FROM ouvrage_auteur oa JOIN ouvrages o ON o.id = oa.ouvrage_id AND o.publie = 1'),
        'types'    => (int) val('SELECT COUNT(*) FROM types'),
    ];
}

/**
 * Relie un ouvrage à une liste de noms d'auteurs (crée les auteurs manquants).
 * Utilisé par l'admin et par la migration.
 */
function lier_auteurs(int $ouvrageId, array $noms): void
{
    q('DELETE FROM ouvrage_auteur WHERE ouvrage_id = ?', [$ouvrageId]);
    $ordre = 0;
    foreach ($noms as $nom) {
        $nom = trim($nom);
        if ($nom === '') {
            continue;
        }
        $id = val('SELECT id FROM auteurs WHERE nom = ?', [$nom]);
        if (!$id) {
            $slug = 'auteur-' . slugify($nom);
            $base = $slug;
            for ($i = 2; val('SELECT 1 FROM auteurs WHERE slug = ?', [$slug]); $i++) {
                $slug = $base . '-' . $i;
            }
            $id = insert('auteurs', ['nom' => $nom, 'slug' => $slug]);
        }
        q('INSERT IGNORE INTO ouvrage_auteur (ouvrage_id, auteur_id, ordre) VALUES (?, ?, ?)', [$ouvrageId, $id, $ordre++]);
    }
}
