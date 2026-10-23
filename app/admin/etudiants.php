<?php
/**
 * Liste des étudiants de l'université (chargée par import) : consultation, lien avec les fiches lecteurs,
 * désactivation / réactivation (cases à cocher). Un rechargement de la liste ne retire jamais personne.
 */

exiger('etudiants.voir');

// Désactiver / réactiver les étudiants cochés, ou tous ceux qui sont désactivés
if ($action === 'activation') {
    exiger_post();
    exiger('etudiants.activer');
    $activer = post_str('valeur') === '1';
    $ids = isset($_POST['tous_desactives'])
        ? array_column(rows('SELECT id FROM etudiants WHERE actif = 0'), 'id')
        : (array) ($_POST['ids'] ?? []);
    $n = changer_activation_etudiants($ids, $activer);
    flash($n ? 'succes' : 'info', $n
        ? pluriel($n, 'étudiant ' . ($activer ? 'réactivé' : 'désactivé'), 'étudiants ' . ($activer ? 'réactivés' : 'désactivés')) . '.'
        : 'Aucun étudiant à ' . ($activer ? 'réactiver' : 'désactiver') . ' dans la sélection.');
    redirect(retour_sur(post_str('retour'), admin_url('etudiants')));
}

$filtre = get_str('filtre', 'tous');
$q = get_str('q', '');
$fiche = "SELECT 1 FROM utilisateurs u WHERE u.role = 'lecteur' AND u.matricule = e.numero_suivi";
$where = [
    'sans_fiche' => "e.actif = 1 AND NOT EXISTS ($fiche)",
    'guichet'    => "EXISTS ($fiche AND u.mot_de_passe = '')",
    'compte'     => "EXISTS ($fiche AND u.mot_de_passe <> '')",
    'desactives' => 'e.actif = 0',
][$filtre] ?? '1';
$params = [];
foreach (array_filter(preg_split('/\s+/u', $q) ?: []) as $mot) {
    $where .= ' AND (e.nom LIKE ? OR e.prenom LIKE ? OR e.numero_suivi LIKE ? OR e.lieu_naissance LIKE ?)';
    array_push($params, ...array_fill(0, 4, "%$mot%"));
}
$pg = pagination_admin((int) val("SELECT COUNT(*) FROM etudiants e WHERE $where", $params), 50);
$liste = rows("SELECT e.*, u.id AS lecteur_id, u.mot_de_passe <> '' AS compte_en_ligne, u.actif AS lecteur_actif, u.faculte, u.parcours
               FROM etudiants e LEFT JOIN utilisateurs u ON u.role = 'lecteur' AND u.matricule = e.numero_suivi
               WHERE $where ORDER BY e.nom, e.prenom LIMIT {$pg['parPage']} OFFSET {$pg['offset']}", $params);
$totaux = row('SELECT COUNT(*) AS total, SUM(actif = 0) AS desactives, MAX(COALESCE(modifie_le, importe_le)) AS maj FROM etudiants');

admin_page('etudiants', compact('filtre', 'q', 'liste', 'pg', 'totaux'), 'Étudiants de l’université', 'etudiants');
