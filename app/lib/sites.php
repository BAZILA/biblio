<?php
/**
 * Centres documentaires : bibliothèque centrale, bibliothèques de faculté, centres de service.
 *
 * Chaque ouvrage appartient au centre qui l'a enregistré. Un agent ne travaille que sur son centre ;
 * « sites.tous » (bibliothèque centrale) lève la limite, « sites.catalogue » donne le catalogue des
 * autres centres en lecture seule, pour renseigner un lecteur sur une autre localisation.
 */

const CAMPUS = ['nord' => 'Campus Nord', 'sud' => 'Campus Sud', 'autre' => 'Hors campus'];

/** Tous les centres, par identifiant. */
function sites(bool $actifsSeulement = false): array
{
    static $cache = null;
    if ($cache === null) {
        $cache = [];
        foreach (rows('SELECT * FROM sites ORDER BY ordre, nom') as $s) {
            $cache[(int) $s['id']] = $s;
        }
    }
    return $actifsSeulement ? array_filter($cache, fn($s) => (int) $s['actif'] === 1) : $cache;
}

/** [id => nom] pour les listes déroulantes. */
function sites_options(bool $actifsSeulement = true): array
{
    return array_map(fn($s) => $s['nom'], sites($actifsSeulement));
}

/** Nom d'un centre, éventuellement suivi de son campus : « Bibliothèque de la FSS — Campus Nord ». */
function nom_site(?int $id, bool $avecCampus = false): string
{
    $s = sites()[(int) $id] ?? null;
    if (!$s) {
        return '';
    }
    return $s['nom'] . ($avecCampus && $s['campus'] !== 'autre' ? ' — ' . CAMPUS[$s['campus']] : '');
}

/** Centre de rattachement de l'agent connecté ; 0 = il travaille sur tous les centres. */
function mon_site(): int
{
    static $id = null;
    if ($id === null) {
        $u = user();
        $id = (!$u || $u['role'] !== 'personnel' || peut('sites.tous')) ? 0 : (int) ($u['site_id'] ?? 0);
    }
    return $id;
}

/**
 * Condition SQL limitant une requête au centre de l'agent (chaîne vide s'il les voit tous).
 * $colonne désigne la colonne site_id de l'ouvrage concerné (« o.site_id »).
 */
function filtre_site(string $colonne = 'o.site_id'): string
{
    $id = mon_site();
    return $id ? " AND $colonne = $id" : '';
}

/** Condition SQL limitant des prêts au centre de l'agent ($alias = table prets ou son alias). */
function filtre_site_prets(string $alias = 'p'): string
{
    $id = mon_site();
    return $id ? " AND EXISTS (SELECT 1 FROM exemplaires _e JOIN ouvrages _o ON _o.id = _e.ouvrage_id
                               WHERE _e.id = $alias.exemplaire_id AND _o.site_id = $id)" : '';
}

/** Condition SQL limitant des exemplaires au centre de l'agent ($alias = table exemplaires ou son alias). */
function filtre_site_exemplaires(string $alias = 'e'): string
{
    $id = mon_site();
    return $id ? " AND EXISTS (SELECT 1 FROM ouvrages _o WHERE _o.id = $alias.ouvrage_id AND _o.site_id = $id)" : '';
}

/** Vrai si l'agent peut consulter le catalogue des autres centres (sans y toucher). */
function catalogue_general(): bool
{
    return mon_site() === 0 || peut('sites.catalogue');
}

/** Vrai si l'agent peut modifier ce qui appartient à ce centre. */
function site_modifiable(?int $siteId): bool
{
    return mon_site() === 0 || (int) $siteId === mon_site();
}

/** Centre à inscrire sur un ouvrage créé maintenant : celui de l'agent, sinon la bibliothèque centrale. */
function site_par_defaut(): ?int
{
    if (mon_site()) {
        return mon_site();
    }
    $u = user();
    return (int) ($u['site_id'] ?? 0) ?: ((int) val('SELECT id FROM sites WHERE central = 1 AND actif = 1 ORDER BY id LIMIT 1') ?: null);
}
