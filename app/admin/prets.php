<?php
/** Liste des prêts (en cours, en retard, rendus) et actions retour / prolongation. */

if ($action === 'rendre' || $action === 'prolonger') {
    exiger_post();
    try {
        if ($action === 'rendre') {
            enregistrer_retour($id);
            flash('succes', 'Retour enregistré.');
        } else {
            flash('succes', 'Prêt prolongé jusqu’au ' . date_fr(prolonger_pret($id)) . '.');
        }
    } catch (RuntimeException $ex) {
        flash('erreur', $ex->getMessage());
    }
    redirect(retour_sur(post_str('retour', ''), admin_url('prets')));
}

$filtre = get_str('filtre', 'tous');
$q = get_str('q', '');
$conds = [
    'en_cours' => 'p.date_retour IS NULL',
    'retard'   => 'p.date_retour IS NULL AND p.date_retour_prevue < CURDATE()',
    'rendus'   => 'p.date_retour IS NOT NULL',
    'tous'     => '1',
];
$where = $conds[$filtre] ?? $conds['tous'];
$params = [];
if ($q !== '') {
    $where .= " AND (o.titre LIKE ? OR e.code LIKE ? OR CONCAT(u.prenom, ' ', u.nom) LIKE ? OR u.matricule LIKE ?)";
    $params = array_fill(0, 4, "%$q%");
}
$total = (int) val("SELECT COUNT(*) FROM prets p JOIN exemplaires e ON e.id = p.exemplaire_id JOIN ouvrages o ON o.id = e.ouvrage_id
                    JOIN utilisateurs u ON u.id = p.lecteur_id WHERE $where", $params);
$pg = pagination_admin($total, 30);
$ordre = $filtre === 'rendus' ? 'p.date_retour DESC, p.id DESC' : ($filtre === 'tous' ? 'p.id DESC' : 'p.date_retour_prevue, p.id');
$liste = prets($where, $params, $ordre, $pg['parPage'], $pg['offset']);

admin_page('prets', compact('filtre', 'q', 'liste', 'pg'), 'Prêts', 'prets');
