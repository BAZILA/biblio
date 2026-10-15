<?php
/** Réservations : exemplaires à mettre de côté, file d'attente, historique. */

if ($action === 'annuler') {
    exiger_post();
    try {
        annuler_reservation($id);
        flash('succes', 'Réservation annulée.');
    } catch (RuntimeException $ex) {
        flash('erreur', $ex->getMessage());
    }
    redirect(admin_url('reservations'));
}

$filtre = get_str('filtre', 'actives');
$conds = [
    'actives'    => "r.statut IN ('disponible','en_attente')",
    'historique' => "r.statut IN ('honoree','annulee','expiree')",
];
$where = $conds[$filtre] ?? $conds['actives'];
$liste = reservations($where, [], $filtre === 'historique'
    ? 'r.demandee_le DESC LIMIT 200'
    : "FIELD(r.statut, 'disponible', 'en_attente'), r.expire_le, r.demandee_le");

admin_page('reservations', compact('filtre', 'liste'), 'Réservations', 'reservations');
