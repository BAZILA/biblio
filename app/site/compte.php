<?php
/**
 * Espace lecteur : /index.php/compte/...
 * Inclus depuis index.php ($segments disponibles).
 */

$action = $segments[1] ?? '';
$gabarit = ['actif' => 'compte', 'classeHtml' => 'view-compte'];

/**
 * Met l'inscription en attente d'un code envoyé par e-mail : rien n'est créé tant que le lecteur
 * n'a pas recopié le code, ce qui prouve que son adresse fonctionne.
 */
function inscription_en_attente(array $attente): void
{
    // Vérification désactivée dans les Réglages : le compte (ou la demande) est créé tout de suite
    if (reglage('verification_email', '1') !== '1') {
        finaliser_inscription($attente);
    }
    $code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
    $_SESSION['inscription'] = $attente;
    $_SESSION['inscription']['code'] = $code;
    $_SESSION['inscription']['expire'] = time() + 60 * max(5, reglage_int('code_duree_minutes', 15));
    $_SESSION['inscription']['essais'] = 0;
    mail_code_verification($attente['email'], $attente['prenom'] ?? '', $code);
    flash('info', 'Un code à 6 chiffres vient d’être envoyé à ' . $attente['email'] . '.');
    redirect(url('compte/verification'));
}

/** Cree le compte (ou la demande) une fois l'adresse verifiee. */
function finaliser_inscription(array $a): void
{
    unset($_SESSION['inscription']);
    if (($a['profil'] ?? '') === 'etudiant') {
        $donnees = $a['donnees'] + ['charte_acceptee_le' => $a['charte']];
        if ($a['fiche_id']) {
            update('utilisateurs', (int) $a['fiche_id'], $donnees);
            $id = (int) $a['fiche_id'];
        } else {
            $id = insert('utilisateurs', $a['creation'] + $donnees);
        }
        q('DELETE FROM tentatives_connexion WHERE email = ?', [$a['cle_essai']]);
        mail_compte_etudiant_cree(row('SELECT * FROM utilisateurs WHERE id = ?', [$id]));
        session_regenerate_id(true);
        $_SESSION['user_id'] = $id;
        q('UPDATE utilisateurs SET derniere_connexion = NOW() WHERE id = ?', [$id]);
        flash('succes', 'Bienvenue ! Votre adresse est vérifiée et votre compte lecteur est créé. '
            . 'Votre identifiant de connexion est votre adresse e-mail : ' . $a['email'] . '.');
        redirect(url('compte'));
    }
    insert('utilisateurs', $a['creation'] + ['charte_acceptee_le' => $a['charte']]);
    flash('succes', 'Votre adresse est vérifiée et votre demande est enregistrée. Votre compte sera activé par la bibliothèque '
        . 'après vérification : vous recevrez alors un e-mail, et votre identifiant de connexion sera votre adresse e-mail.');
    redirect(url('compte/connexion'));
}

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
        // Deux parcours : étudiant de la liste de l'université (compte créé tout de suite, identité vérifiée ; sa faculté vient
        // de la liste, il complète son parcours) ou enseignant / personnel / visiteur (demande validée ensuite par la bibliothèque).
        $profil = (post_str('profil') ?: get_str('profil')) === 'autre' ? 'autre' : 'etudiant';
        $listeEtudiants = (bool) val('SELECT 1 FROM etudiants WHERE actif = 1 LIMIT 1');
        $saisie = ['id' => 0, 'prenom' => '', 'nom' => '', 'email' => '', 'categorie' => 'enseignant', 'matricule' => '', 'telephone' => '',
            'faculte' => '', 'parcours' => '', 'numero_suivi' => '', 'date_naissance' => '', 'lieu_naissance' => ''];
        $erreurs = [];
        if (is_post()) {
            csrf_verify();
            $mdp = post_str('mot_de_passe', '', false);
            if (mb_strlen($mdp) < 8) {
                $erreurs['mot_de_passe'] = 'Au moins 8 caractères.';
            } elseif ($mdp !== ($_POST['mot_de_passe2'] ?? '')) {
                $erreurs['mot_de_passe2'] = 'Les deux mots de passe ne correspondent pas.';
            }
            $robot = ($_POST['site_web'] ?? '') !== '';
            // Charte du lecteur : a lire et accepter avant toute creation de compte
            $charte = reglage('charte_fichier') !== '' ? date('Y-m-d H:i:s') : null;
            if (reglage('charte_fichier') !== '' && !isset($_POST['charte'])) {
                $erreurs['charte'] = 'Lisez la charte du lecteur et cochez la case pour continuer.';
            }

            if ($profil === 'etudiant') {
                foreach (['nom', 'prenom', 'numero_suivi', 'date_naissance', 'lieu_naissance', 'parcours', 'email', 'telephone'] as $k) {
                    $saisie[$k] = mb_substr(post_str($k, ''), 0, $k === 'email' ? 190 : 150);
                    if ($saisie[$k] === '' && $k !== 'telephone') {
                        $erreurs[$k] = 'Champ requis.';
                    }
                }
                $saisie['email'] = mb_strtolower($saisie['email']);
                if ($saisie['email'] !== '' && !filter_var($saisie['email'], FILTER_VALIDATE_EMAIL)) {
                    $erreurs['email'] = 'Adresse e-mail invalide.';
                }
                if ($saisie['date_naissance'] !== '' && !date_naissance_iso($saisie['date_naissance'])) {
                    $erreurs['date_naissance'] = 'Date invalide.';
                }
                if ($saisie['numero_suivi'] !== '' && !suivi_valide($saisie['numero_suivi'])) {
                    $erreurs['numero_suivi'] = 'Le n° de suivi ne contient que des chiffres.';
                }
                // Limite les essais d'identité (même règle que la connexion : par n° de suivi et par adresse IP)
                $cleEssai = 'inscription:' . normaliser_suivi($saisie['numero_suivi']);
                $ip = $_SERVER['REMOTE_ADDR'] ?? '';
                if (!$erreurs && ((int) val('SELECT COUNT(*) FROM tentatives_connexion WHERE email = ? AND le > NOW() - INTERVAL 15 MINUTE', [$cleEssai]) >= ECHECS_MAX_EMAIL
                    || (int) val("SELECT COUNT(*) FROM tentatives_connexion WHERE ip = ? AND email LIKE 'inscription:%' AND le > NOW() - INTERVAL 15 MINUTE", [$ip]) >= ECHECS_MAX_IP)) {
                    $erreurs['identite'] = 'Trop d’essais. Réessayez dans un quart d’heure, ou présentez-vous à l’accueil de la bibliothèque.';
                }
                if (!$erreurs) {
                    $etudiant = verifier_etudiant($saisie);
                    if (is_string($etudiant)) {
                        $erreurs['identite'] = $etudiant;
                        insert('tentatives_connexion', ['email' => mb_substr($cleEssai, 0, 190), 'ip' => mb_substr($ip, 0, 45)]);
                    }
                }
                if (!$erreurs) {
                    $fiche = lecteur_par_suivi($etudiant['numero_suivi']); // fiche créée au guichet lors d'un prêt, s'il y en a une
                    if ($fiche && $fiche['mot_de_passe'] !== '') {
                        $erreurs['identite'] = 'Un compte existe déjà pour ce n° de suivi' . ($fiche['email'] ? ' (identifiant : ' . masquer_email($fiche['email']) . ')' : '')
                            . '. Connectez-vous, ou adressez-vous à l’accueil si vous avez oublié votre mot de passe.';
                    } elseif ($fiche && !$fiche['actif']) {
                        $erreurs['identite'] = 'Votre fiche lecteur a été désactivée par la bibliothèque : présentez-vous à l’accueil.';
                    } elseif (val('SELECT 1 FROM utilisateurs WHERE email = ? AND id <> ?', [$saisie['email'], (int) ($fiche['id'] ?? 0)])) {
                        $erreurs['email'] = 'Un autre compte utilise déjà cette adresse.';
                    }
                }
                if (!$erreurs) {
                    if ($robot) {
                        redirect(url('compte/connexion'));
                    }
                    inscription_en_attente([
                        'profil' => 'etudiant',
                        'email' => $saisie['email'],
                        'prenom' => $saisie['prenom'],
                        'fiche_id' => (int) ($fiche['id'] ?? 0),
                        'cle_essai' => $cleEssai,
                        'charte' => $charte,
                        'creation' => lecteur_depuis_etudiant($etudiant),
                        'donnees' => ['email' => $saisie['email'], 'mot_de_passe' => password_hash($mdp, PASSWORD_DEFAULT), 'actif' => 1, 'en_attente' => 0,
                            'faculte' => $etudiant['faculte'], 'parcours' => $saisie['parcours'], 'telephone' => $saisie['telephone']],
                    ]);
                }
            } else {
                // Enseignant, personnel ou visiteur : tous les champs obligatoires, e-mail compris (les étudiants ont leur propre parcours)
                $saisie = lecteur_depuis_post($saisie);
                if (!isset(CATEGORIES_HORS_ETUDIANT[$saisie['categorie']])) {
                    $erreurs['categorie'] = 'Choisissez une catégorie.';
                }
                $erreurs += valider_lecteur($saisie, true);
                if (!$erreurs) {
                    if ($robot) {
                        redirect(url('compte/connexion'));
                    }
                    inscription_en_attente([
                        'profil' => 'autre',
                        'email' => $saisie['email'],
                        'prenom' => $saisie['prenom'],
                        'charte' => $charte,
                        'creation' => lecteur_donnees($saisie) + [
                            'role' => 'lecteur', 'actif' => 0, 'en_attente' => 1, 'mot_de_passe' => password_hash($mdp, PASSWORD_DEFAULT),
                        ],
                    ]);
                }
            }
        }
        page('compte/inscription', compact('saisie', 'erreurs', 'profil', 'listeEtudiants'), $gabarit + ['titre' => 'Créer un compte lecteur']);

    case 'verification':
        // Le compte n'existe pas encore : il attend le code à 6 chiffres envoyé à l'adresse saisie
        $attente = $_SESSION['inscription'] ?? null;
        if (!$attente) {
            flash('info', 'Reprenez votre inscription : la demande précédente a expiré.');
            redirect(url('compte/inscription'));
        }
        $erreur = '';
        if (is_post()) {
            csrf_verify();
            if (post_str('renvoyer') !== '') {
                inscription_en_attente($attente); // nouveau code, nouvelle validité
            }
            $code = preg_replace('/[^0-9]/', '', post_str('code', ''));
            $_SESSION['inscription']['essais'] = (int) $attente['essais'] + 1;
            if ((int) $_SESSION['inscription']['essais'] > 6) {
                unset($_SESSION['inscription']);
                flash('erreur', 'Trop de codes erronés. Recommencez votre inscription, ou présentez-vous à la bibliothèque.');
                redirect(url('compte/inscription'));
            } elseif (time() > (int) $attente['expire']) {
                $erreur = 'Ce code a expiré. Demandez-en un nouveau.';
            } elseif ($code === '' || !hash_equals((string) $attente['code'], $code)) {
                $erreur = 'Code incorrect. Vérifiez l’e-mail reçu, sans espace.';
            } else {
                finaliser_inscription($attente);
            }
        }
        page('compte/verification', compact('attente', 'erreur'), $gabarit + ['titre' => 'Vérifier votre adresse e-mail']);

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
    case 'prolongation':
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
                // Le lecteur ne prolonge pas lui-même : il en fait la demande, la bibliothèque accepte ou refuse
                demander_prolongation((int) ($segments[2] ?? 0), (int) $u['id'], post_str('motif', ''));
                flash('succes', 'Demande de prolongation envoyée. La bibliothèque vous répondra ici' . ($u['email'] ? ' et par e-mail' : '') . '.');
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
        // Dernière demande de prolongation de chaque prêt en cours (pour afficher son état)
        $demandes = [];
        foreach (demandes_prolongation('p.lecteur_id = ? AND p.date_retour IS NULL', [$u['id']], 'dp.demandee_le, dp.id') as $d) {
            $demandes[$d['pret_id']] = $d;
        }
        page('compte/tableau', [
            'u' => $u,
            'demandes' => $demandes,
            'prets' => prets('p.lecteur_id = ? AND p.date_retour IS NULL', [$u['id']], 'p.date_retour_prevue', null, 0, true),
            'reservations' => reservations("r.lecteur_id = ? AND r.statut IN ('en_attente','disponible')", [$u['id']], 'r.demandee_le', true),
            'historique' => prets('p.lecteur_id = ? AND p.date_retour IS NOT NULL', [$u['id']], 'p.date_retour DESC', 20, 0, true),
        ], $gabarit + ['titre' => 'Mon espace lecteur']);

    default:
        introuvable();
}
