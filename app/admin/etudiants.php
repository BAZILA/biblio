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

// Ajouter un étudiant absent de la liste, ou corriger une fiche : il pourra alors créer son compte en ligne
if ($action === 'nouveau' || $action === 'modifier') {
    exiger($action === 'nouveau' ? 'etudiants.ajouter' : 'etudiants.modifier');
    $ancien = $action === 'modifier' ? (row('SELECT * FROM etudiants WHERE id = ?', [$id]) ?? admin_introuvable('Étudiant')) : null;
    $e = $ancien ?? ['id' => 0, 'numero_suivi' => '', 'nom' => '', 'prenom' => '', 'date_naissance' => '', 'lieu_naissance' => '', 'faculte' => '', 'actif' => 1];
    $erreurs = [];
    if (is_post()) {
        csrf_verify();
        foreach (CHAMPS_ETUDIANT as $k) {
            $e[$k] = mb_substr(preg_replace('/\s+/u', ' ', post_str($k, '')), 0, $k === 'numero_suivi' ? 50 : 150);
        }
        $e['numero_suivi'] = normaliser_suivi($e['numero_suivi']);
        $erreurs = valider_etudiant($e);
        if (!$erreurs) {
            $donnees = array_intersect_key($e, array_flip(CHAMPS_ETUDIANT));
            $donnees['date_naissance'] = date_naissance_iso($e['date_naissance']);
            if ($ancien) {
                update('etudiants', (int) $ancien['id'], $donnees + ['modifie_le' => date('Y-m-d H:i:s')]);
                // Changement de n° de suivi : la fiche lecteur liée suit
                if ($ancien['numero_suivi'] !== $e['numero_suivi']) {
                    q("UPDATE utilisateurs SET matricule = ? WHERE role = 'lecteur' AND matricule = ?", [$e['numero_suivi'], $ancien['numero_suivi']]);
                }
                synchroniser_fiche_etudiant($e['numero_suivi'], $e);
                flash('succes', 'Fiche de ' . $e['prenom'] . ' ' . $e['nom'] . ' (n° de suivi ' . $e['numero_suivi'] . ') enregistrée.');
            } else {
                insert('etudiants', $donnees);
                flash('succes', $e['prenom'] . ' ' . $e['nom'] . ' (n° de suivi ' . $e['numero_suivi'] . ') ajouté à la liste : il peut maintenant créer son compte en ligne.');
            }
            redirect(admin_url('etudiants', ['q' => $e['numero_suivi']]));
        }
    }
    $fiche = $ancien ? lecteur_par_suivi($ancien['numero_suivi']) : null;
    admin_page('etudiant_form', ['e' => $e, 'erreurs' => $erreurs, 'fiche' => $fiche], $ancien ? 'Modifier un étudiant' : 'Ajouter un étudiant', 'etudiants');
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
    $where .= ' AND (e.nom LIKE ? OR e.prenom LIKE ? OR e.numero_suivi LIKE ? OR e.lieu_naissance LIKE ? OR e.faculte LIKE ?)';
    array_push($params, ...array_fill(0, 5, "%$mot%"));
}
$pg = pagination_admin((int) val("SELECT COUNT(*) FROM etudiants e WHERE $where", $params), 50);
$liste = rows("SELECT e.*, u.id AS lecteur_id, u.mot_de_passe <> '' AS compte_en_ligne, u.actif AS lecteur_actif, u.parcours
               FROM etudiants e LEFT JOIN utilisateurs u ON u.role = 'lecteur' AND u.matricule = e.numero_suivi
               WHERE $where ORDER BY e.nom, e.prenom LIMIT {$pg['parPage']} OFFSET {$pg['offset']}", $params);
$totaux = row('SELECT COUNT(*) AS total, SUM(actif = 0) AS desactives, MAX(COALESCE(modifie_le, importe_le)) AS maj FROM etudiants');

admin_page('etudiants', compact('filtre', 'q', 'liste', 'pg', 'totaux'), 'Étudiants de l’université', 'etudiants');
