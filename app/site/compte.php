<?php
/**
 * Espace lecteur : /index.php/compte/...
 * Inclus depuis index.php ($segments disponibles).
 */

$action = $segments[1] ?? '';
$gabarit = ['actif' => 'compte', 'classeHtml' => 'view-compte'];

// Le personnel gère tout depuis l'administration
if (is_personnel() && !in_array($action, ['deconnexion'], true)) {
    redirect(admin_url());
}

switch ($action) {

    case 'connexion':
        if (user()) {
            redirect(url('compte'));
        }
        $email = '';
        $erreur = '';
        $retour = (post_str('retour') ?: get_str('retour'));
        if (is_post()) {
            csrf_verify();
            $email = post_str('email', '');
            $res = tenter_connexion($email, post_str('mot_de_passe', '', false));
            if (is_array($res)) {
                if (in_array($res['role'], ROLES_PERSONNEL, true)) {
                    redirect(admin_url());
                }
                redirect(retour_sur($retour, url('compte')));
            }
            $erreur = $res;
        }
        page('compte/connexion', compact('email', 'erreur', 'retour'), $gabarit + ['titre' => 'Connexion — Espace lecteur']);

    case 'inscription':
        $saisie = ['prenom' => '', 'nom' => '', 'email' => '', 'categorie' => 'etudiant', 'matricule' => '', 'telephone' => ''];
        $erreurs = [];
        if (is_post()) {
            csrf_verify();
            foreach ($saisie as $k => $_) {
                $saisie[$k] = post_str($k, '');
            }
            $saisie['email'] = mb_strtolower($saisie['email']);
            $mdp = post_str('mot_de_passe', '', false);
            foreach (['prenom', 'nom', 'email'] as $k) {
                if ($saisie[$k] === '') {
                    $erreurs[$k] = 'Champ requis.';
                }
            }
            if ($saisie['email'] !== '' && !filter_var($saisie['email'], FILTER_VALIDATE_EMAIL)) {
                $erreurs['email'] = 'Adresse e-mail invalide.';
            } elseif (val('SELECT 1 FROM utilisateurs WHERE email = ?', [$saisie['email']])) {
                $erreurs['email'] = 'Un compte existe déjà avec cette adresse.';
            }
            if (!isset(LIBELLES_CATEGORIE[$saisie['categorie']])) {
                $erreurs['categorie'] = 'Catégorie invalide.';
            }
            if ($saisie['matricule'] !== '' && val('SELECT 1 FROM utilisateurs WHERE matricule = ?', [$saisie['matricule']])) {
                $erreurs['matricule'] = 'Ce matricule est déjà associé à un compte.';
            }
            if (mb_strlen($mdp) < 8) {
                $erreurs['mot_de_passe'] = 'Au moins 8 caractères.';
            } elseif ($mdp !== ($_POST['mot_de_passe2'] ?? '')) {
                $erreurs['mot_de_passe2'] = 'Les deux mots de passe ne correspondent pas.';
            }
            if (!$erreurs) {
                if (($_POST['site_web'] ?? '') === '') {
                    insert('utilisateurs', [
                        'role' => 'lecteur', 'actif' => 0, 'en_attente' => 1,
                        'prenom' => $saisie['prenom'], 'nom' => $saisie['nom'], 'email' => $saisie['email'],
                        'categorie' => $saisie['categorie'], 'matricule' => $saisie['matricule'] ?: null,
                        'telephone' => $saisie['telephone'], 'mot_de_passe' => password_hash($mdp, PASSWORD_DEFAULT),
                    ]);
                }
                flash('succes', 'Votre demande est enregistrée. Votre compte sera activé par la bibliothèque après vérification ; vous pourrez alors vous connecter.');
                redirect(url('compte/connexion'));
            }
        }
        page('compte/inscription', compact('saisie', 'erreurs'), $gabarit + ['titre' => 'Créer un compte lecteur']);

    case 'deconnexion':
        if (is_post()) {
            csrf_verify();
            deconnexion();
            flash('info', 'Vous êtes déconnecté.');
        }
        redirect(url());

    case 'reserver':
        $u = exiger_lecteur();
        if (!is_post()) {
            redirect(url('compte'));
        }
        csrf_verify();
        $ouvrage = ouvrage_par_id((int) ($_POST['ouvrage_id'] ?? 0));
        try {
            $r = reserver((int) ($_POST['ouvrage_id'] ?? 0), (int) $u['id']);
            flash('succes', $r['statut'] === 'disponible'
                ? 'Réservation confirmée : un exemplaire vous attend à l’accueil jusqu’au ' . date_fr($r['expire_le']) . '.'
                : 'Vous êtes inscrit dans la file d’attente. L’exemplaire vous sera mis de côté dès son retour.');
        } catch (RuntimeException $ex) {
            flash('erreur', $ex->getMessage());
        }
        redirect($ouvrage ? $ouvrage['url'] : url('compte'));

    case 'annuler':
    case 'prolonger':
        $u = exiger_lecteur();
        if (!is_post()) {
            redirect(url('compte'));
        }
        csrf_verify();
        try {
            if ($action === 'annuler') {
                annuler_reservation((int) ($segments[2] ?? 0), (int) $u['id']);
                flash('succes', 'Réservation annulée.');
            } else {
                $date = prolonger_pret((int) ($segments[2] ?? 0), (int) $u['id']);
                flash('succes', 'Prêt prolongé jusqu’au ' . date_fr($date) . '.');
            }
        } catch (RuntimeException $ex) {
            flash('erreur', $ex->getMessage());
        }
        redirect(url('compte'));

    case 'mot-de-passe':
        $u = exiger_lecteur();
        if (is_post()) {
            csrf_verify();
            $nouveau = post_str('nouveau', '', false);
            if (!password_verify(post_str('actuel', '', false), $u['mot_de_passe'])) {
                flash('erreur', 'Mot de passe actuel incorrect.');
            } elseif (mb_strlen($nouveau) < 8) {
                flash('erreur', 'Le nouveau mot de passe doit contenir au moins 8 caractères.');
            } else {
                q('UPDATE utilisateurs SET mot_de_passe = ? WHERE id = ?', [password_hash($nouveau, PASSWORD_DEFAULT), $u['id']]);
                flash('succes', 'Mot de passe modifié.');
            }
        }
        redirect(url('compte'));

    case '':
        $u = exiger_lecteur();
        expirer_reservations();
        page('compte/tableau', [
            'u' => $u,
            'prets' => prets('p.lecteur_id = ? AND p.date_retour IS NULL', [$u['id']], 'p.date_retour_prevue'),
            'reservations' => reservations("r.lecteur_id = ? AND r.statut IN ('en_attente','disponible')", [$u['id']]),
            'historique' => prets('p.lecteur_id = ? AND p.date_retour IS NOT NULL', [$u['id']], 'p.date_retour DESC', 20),
        ], $gabarit + ['titre' => 'Mon espace lecteur']);

    default:
        introuvable();
}
