<?php
/** Réglages généraux : identité du site, coordonnées, règles de prêt. */

$groupes = [
    'Site' => [
        'site_nom'         => ['Nom du site', 'text'],
        'site_description' => ['Description (moteurs de recherche)', 'textarea'],
        'contact_email'    => ['E-mail de contact', 'email'],
        'par_page'         => ['Ouvrages par page dans le catalogue', 'number', 1],
    ],
    'Page contact' => [
        'contact_nom'       => ['Nom affiché', 'text'],
        'contact_role'      => ['Sous-titre', 'text'],
        'contact_adresse'   => ['Adresse', 'text'],
        'contact_ville'     => ['Ville', 'text'],
        'contact_pays'      => ['Pays', 'text'],
        'contact_telephone' => ['Téléphone', 'tel'],
    ],
    'Règles de prêt' => [
        'pret_duree_jours'        => ['Durée d’un prêt (jours)', 'number', 1],
        'pret_max_prolongations'  => ['Prolongations autorisées par prêt', 'number'],
        'pret_max_etudiant'       => ['Prêts simultanés — étudiant', 'number'],
        'pret_max_enseignant'     => ['Prêts simultanés — enseignant', 'number'],
        'pret_max_personnel'      => ['Prêts simultanés — personnel', 'number'],
        'pret_max_externe'        => ['Prêts simultanés — lecteur externe', 'number'],
        'reservation_delai_jours' => ['Délai de retrait d’une réservation (jours)', 'number', 1],
    ],
];

if (is_post()) {
    csrf_verify();
    foreach ($groupes as $champs) {
        foreach ($champs as $cle => $def) {
            $v = post_str($cle, '');
            if ($def[1] === 'number') {
                $v = (string) max($def[2] ?? 0, (int) $v); // minimum par champ (ex. au moins 1 ouvrage par page)
            }
            q('REPLACE INTO reglages (cle, valeur) VALUES (?, ?)', [$cle, $v]);
        }
    }
    flash('succes', 'Réglages enregistrés.');
    redirect(admin_url('reglages'));
}

admin_page('reglages', ['groupes' => $groupes], 'Réglages', 'reglages');
