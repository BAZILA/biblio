<?php
/**
 * Prêts : liste (en cours, en retard, rendus), retour avec constat, prolongation,
 * et traitement des demandes de prolongation faites par les lecteurs (prets/demandes).
 */

if ($action === 'rendre') {
    exiger_post();
    exiger('prets.retour');
    try {
        $devenir = enregistrer_retour($id, constat_retour_post((int) $moi['id']));
        flash('succes', 'Retour enregistré. ' . DEVENIR_EXEMPLAIRE[$devenir]);
    } catch (RuntimeException $ex) {
        flash('erreur', $ex->getMessage());
    }
    redirect(retour_sur(post_str('retour', ''), admin_url('prets')));
}

if ($action === 'prolonger') {
    exiger_post();
    exiger('prets.prolonger');
    if (isset($_POST['forcer'])) {
        exiger('prets.forcer');
    }
    try {
        flash('succes', 'Prêt prolongé jusqu’au ' . date_fr(prolonger_pret($id, post_str('date') ?: null, isset($_POST['forcer']))) . '.');
    } catch (RuntimeException $ex) {
        flash('erreur', $ex->getMessage());
    }
    redirect(retour_sur(post_str('retour', ''), admin_url('prets')));
}

// Décision sur une demande de prolongation
if ($action === 'demande') {
    exiger_post();
    exiger('demandes.traiter');
    $accepter = post_str('decision') === 'accepter';
    // Accepter malgré une règle (maximum, file d'attente…) revient à forcer la prolongation
    $pretDemande = row('SELECT p.* FROM demandes_prolongation dp JOIN prets p ON p.id = dp.pret_id WHERE dp.id = ?', [$id]);
    if ($accepter && $pretDemande && obstacles_prolongation($pretDemande)) {
        exiger('prets.forcer');
    }
    try {
        $date = traiter_demande($id, $accepter, (int) $moi['id'], post_str('reponse', ''), post_str('date') ?: null);
        flash('succes', $accepter ? 'Prolongation accordée jusqu’au ' . date_fr($date) . '. Le lecteur est prévenu.' : 'Demande refusée. Le lecteur est prévenu.');
    } catch (RuntimeException $ex) {
        flash('erreur', $ex->getMessage());
    }
    redirect(admin_url('prets/demandes'));
}

if ($action === 'demandes') {
    exiger('demandes.voir');
    $traitees = get_str('vue') === 'traitees';
    $liste = demandes_prolongation($traitees ? "dp.statut <> 'en_attente'" : "dp.statut = 'en_attente'", [],
        $traitees ? 'dp.traitee_le DESC LIMIT 100' : 'dp.demandee_le');
    foreach ($liste as &$d) {
        $d['obstacles'] = $traitees ? [] : obstacles_prolongation($d);
        $d['proposee'] = echeance_prolongee($d);
    }
    unset($d);
    admin_page('demandes', compact('liste', 'traitees'), 'Demandes de prolongation', 'demandes');
}

exiger('prets.voir');
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
                    JOIN utilisateurs u ON u.id = p.lecteur_id WHERE $where" . filtre_site('o.site_id'), $params);
$pg = pagination_admin($total, 30);
$ordre = $filtre === 'rendus' ? 'p.date_retour DESC, p.id DESC' : ($filtre === 'tous' ? 'p.id DESC' : 'p.date_retour_prevue, p.id');
$liste = prets($where, $params, $ordre, $pg['parPage'], $pg['offset']);

admin_page('prets', compact('filtre', 'q', 'liste', 'pg'), 'Prêts', 'prets');
