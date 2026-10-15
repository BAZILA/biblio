<?php
/** Réglages généraux : identité du site, coordonnées, règles de prêt, e-mails, couverture par défaut. */

// Images réglables et leur valeur d'origine (« revenir à l'image d'origine »)
const IMAGES_PAR_DEFAUT = ['couverture_defaut' => '', 'photo_apropos' => 'images/biblio/apropos-rayonnages.jpg'];

// [libellé, type, minimum ou options]
$groupes = [
    'Site' => [
        'site_nom'         => ['Nom du site', 'text'],
        'site_description' => ['Description (moteurs de recherche)', 'textarea'],
        'contact_email'    => ['E-mail de contact', 'email'],
        'site_adresse'     => ['Adresse publique du site (liens dans les e-mails)', 'url'],
        'par_page'         => ['Ouvrages par page dans le catalogue', 'number', 1],
        'couverture_defaut' => ['Couverture par défaut (ouvrages sans image)', 'image'],
        'photo_apropos'    => ['Photo « À propos » de la page d’accueil', 'image'],
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
        'pret_max_prolongations'  => ['Prolongations autorisées par prêt', 'number', 0],
        'pret_max_etudiant'       => ['Prêts simultanés — étudiant', 'number', 0],
        'pret_max_enseignant'     => ['Prêts simultanés — enseignant', 'number', 0],
        'pret_max_personnel'      => ['Prêts simultanés — personnel', 'number', 0],
        'pret_max_externe'        => ['Prêts simultanés — lecteur externe', 'number', 0],
        'reservation_delai_jours' => ['Délai de retrait d’une réservation (jours)', 'number', 1],
    ],
    'E-mails aux lecteurs' => [
        'mail_mode'              => ['Envoi des e-mails', 'select', ['journal' => 'Désactivé (messages seulement consignés dans le journal)', 'smtp' => 'Activé, par serveur SMTP']],
        'rappel_jours_avant'     => ['Rappel envoyé … jours avant l’échéance', 'number', 0],
        'relance_tous_les_jours' => ['Relance des retards tous les … jours', 'number', 1],
        'mail_expediteur'        => ['Adresse d’expédition', 'email'],
        'mail_expediteur_nom'    => ['Nom de l’expéditeur', 'text'],
        'smtp_hote'              => ['Serveur SMTP', 'text'],
        'smtp_port'              => ['Port', 'number', 1],
        'smtp_securite'          => ['Sécurité', 'select', ['tls' => 'STARTTLS (port 587)', 'ssl' => 'SSL/TLS (port 465)', 'aucune' => 'Aucune (déconseillé)']],
        'smtp_utilisateur'       => ['Identifiant SMTP', 'text'],
        'smtp_mot_de_passe'      => ['Mot de passe SMTP', 'password'],
    ],
];

if (is_post()) {
    csrf_verify();
    $erreur = null;
    foreach ($groupes as $champs) {
        foreach ($champs as $cle => $def) {
            [$libelle, $type] = $def;
            if ($type === 'image') {
                try {
                    $chemin = televerser_image($cle, 'biblio', str_replace('_', '-', $cle));
                    if ($chemin) {
                        q('REPLACE INTO reglages (cle, valeur) VALUES (?, ?)', [$cle, $chemin]);
                    } elseif (isset($_POST[$cle . '_retirer'])) {
                        q('REPLACE INTO reglages (cle, valeur) VALUES (?, ?)', [$cle, IMAGES_PAR_DEFAUT[$cle] ?? '']);
                    }
                } catch (RuntimeException $ex) {
                    $erreur = $libelle . ' : ' . $ex->getMessage();
                }
                continue;
            }
            if (!array_key_exists($cle, $_POST)) {
                continue; // champ absent de l'envoi : réglage inchangé
            }
            $v = post_str($cle, '', $type !== 'password');
            if ($type === 'password' && $v === '') {
                continue; // vide = on garde le mot de passe déjà enregistré
            }
            if ($type === 'number') {
                $v = (string) max($def[2] ?? 0, (int) $v);
            }
            if ($type === 'select' && !isset($def[2][$v])) {
                continue;
            }
            q('REPLACE INTO reglages (cle, valeur) VALUES (?, ?)', [$cle, $v]);
        }
    }
    flash($erreur ? 'erreur' : 'succes', $erreur ?? 'Réglages enregistrés.');
    redirect(admin_url('reglages'));
}

admin_page('reglages', ['groupes' => $groupes], 'Réglages', 'reglages');
