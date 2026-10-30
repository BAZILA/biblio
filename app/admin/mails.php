<?php
/** Journal des e-mails aux lecteurs, envoi de test et lancement manuel des rappels. */

if ($action === 'test') {
    exiger_post();
    exiger('mails.envoyer');
    $a = post_str('destinataire');
    if (!filter_var($a, FILTER_VALIDATE_EMAIL)) {
        flash('erreur', 'Adresse e-mail invalide.');
    } else {
        $ok = envoyer_mail($a, 'Test d’envoi — ' . reglage('site_nom'), "Ceci est un message de test envoyé depuis l’administration de la bibliothèque.", 'test');
        $dernier = row('SELECT * FROM mails ORDER BY id DESC LIMIT 1');
        flash($ok ? 'succes' : ($dernier['statut'] === 'journal' ? 'info' : 'erreur'),
            $ok ? "Message envoyé à $a." : ($dernier['statut'] === 'journal' ? 'L’envoi est désactivé (Réglages) : le message a seulement été consigné.' : 'Échec : ' . $dernier['erreur']));
    }
    redirect(admin_url('mails'));
}

if ($action === 'taches') {
    exiger_post();
    exiger('mails.envoyer');
    $b = taches_quotidiennes(true);
    flash('succes', "Rappels envoyés : {$b['rappels']} · relances de retard : {$b['relances']} · réservations expirées : {$b['reservations_expirees']}.");
    redirect(admin_url('mails'));
}

if ($action === 'voir') {
    admin_page('mail_voir', ['m' => row('SELECT * FROM mails WHERE id = ?', [$id]) ?? admin_introuvable('E-mail')], 'E-mail', 'mails');
}

$pg = pagination_admin((int) val('SELECT COUNT(*) FROM mails'), 40);
admin_page('mails', [
    'liste' => rows("SELECT * FROM mails ORDER BY id DESC LIMIT {$pg['parPage']} OFFSET {$pg['offset']}"),
    'pg' => $pg,
], 'E-mails envoyés', 'mails');
