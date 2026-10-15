<?php
/** Gestion des lecteurs : liste, fiche, création / modification, validation des inscriptions. */

$lecteurParId = fn(int $id) => row("SELECT * FROM utilisateurs WHERE id = ? AND role = 'lecteur'", [$id]) ?? admin_introuvable('Lecteur');

if ($action === 'activer' || $action === 'desactiver') {
    exiger_post();
    $l = $lecteurParId($id);
    q('UPDATE utilisateurs SET actif = ?, en_attente = 0 WHERE id = ?', [$action === 'activer' ? 1 : 0, $id]);
    if ($action === 'activer' && $l['en_attente']) {
        mail_compte_valide($l);
    }
    flash('succes', nom_complet($l) . ($action === 'activer' ? ' peut maintenant emprunter et se connecter.' : ' a été désactivé.'));
    redirect(retour_sur(post_str('retour', ''), admin_url('lecteurs/voir/' . $id)));
}

if ($action === 'supprimer') {
    exiger_post();
    $l = $lecteurParId($id);
    if (val('SELECT COUNT(*) FROM prets WHERE lecteur_id = ?', [$id])) {
        flash('erreur', 'Ce lecteur a un historique de prêts : désactivez son compte plutôt que de le supprimer.');
        redirect(admin_url('lecteurs/voir/' . $id));
    }
    q('DELETE FROM utilisateurs WHERE id = ?', [$id]);
    flash('succes', 'Lecteur « ' . nom_complet($l) . ' » supprimé.');
    redirect(admin_url('lecteurs'));
}

// Réinitialisation du mot de passe : un mot de passe provisoire est affiché une fois et envoyé par e-mail
if ($action === 'mot-de-passe') {
    exiger_post();
    $l = $lecteurParId($id);
    if (!$l['email']) {
        flash('erreur', 'Ce lecteur n’a pas d’adresse e-mail : ajoutez-en une pour lui ouvrir un accès en ligne.');
        redirect(admin_url('lecteurs/modifier/' . $id));
    }
    $mdp = mot_de_passe_provisoire();
    q('UPDATE utilisateurs SET mot_de_passe = ? WHERE id = ?', [password_hash($mdp, PASSWORD_DEFAULT), $id]);
    q('DELETE FROM tentatives_connexion WHERE email = ?', [$l['email']]);
    mail_mot_de_passe($l, $mdp);
    $_SESSION['mdp_provisoire'] = ['id' => $id, 'mdp' => $mdp];
    flash('succes', 'Mot de passe réinitialisé' . (reglage('mail_mode') === 'smtp' ? ' et envoyé par e-mail à ' . $l['email'] : '') . '.');
    redirect(admin_url('lecteurs/voir/' . $id));
}

// Création rapide au guichet : lecteur sans accès en ligne, qui peut emprunter immédiatement
if ($action === 'rapide') {
    exiger_post();
    $l = lecteur_depuis_post(['id' => 0]);
    $erreurs = valider_lecteur($l, false);
    if ($erreurs) {
        $_SESSION['lecteur_rapide'] = ['saisie' => $l, 'erreurs' => $erreurs];
        flash('erreur', 'Fiche incomplète : ' . implode(' ', array_unique($erreurs)));
        redirect(admin_url('guichet', ['nouveau_lecteur' => 1] + (post_str('ouvrage') !== '' ? ['ouvrage' => post_str('ouvrage')] : [])));
    }
    $nouveau = insert('utilisateurs', lecteur_donnees($l) + ['role' => 'lecteur', 'actif' => 1]);
    flash('succes', 'Lecteur « ' . nom_complet($l) . ' » créé : vous pouvez enregistrer son prêt.');
    redirect(admin_url('guichet', ['lecteur' => $nouveau] + (post_str('ouvrage') !== '' ? ['ouvrage' => post_str('ouvrage')] : [])));
}

if ($action === 'nouveau' || $action === 'modifier') {
    $l = $action === 'modifier' ? $lecteurParId($id) : [
        'id' => 0, 'prenom' => '', 'nom' => '', 'email' => '', 'matricule' => '', 'categorie' => 'etudiant', 'telephone' => '',
        'faculte' => '', 'departement' => '', 'actif' => 1, 'en_attente' => 0, 'mot_de_passe' => '',
    ];
    $l['email'] = (string) $l['email'];
    $l['matricule'] = (string) $l['matricule'];
    $accesEnLigne = $l['mot_de_passe'] !== '';
    $erreurs = [];
    if (is_post()) {
        csrf_verify();
        $l = lecteur_depuis_post($l);
        $l['actif'] = isset($_POST['actif']) ? 1 : 0;
        $accesEnLigne = isset($_POST['acces_en_ligne']);
        $erreurs = valider_lecteur($l, $accesEnLigne);

        if (!$erreurs) {
            $data = lecteur_donnees($l) + ['actif' => $l['actif'], 'en_attente' => $l['actif'] ? 0 : (int) $l['en_attente']];
            $mdp = null;
            if (!$accesEnLigne) {
                $data['mot_de_passe'] = ''; // plus d'accès à l'espace lecteur
            } elseif ($l['mot_de_passe'] === '') {
                $mdp = mot_de_passe_provisoire(); // ouverture de l'accès en ligne
                $data['mot_de_passe'] = password_hash($mdp, PASSWORD_DEFAULT);
            }
            if ($l['id']) {
                update('utilisateurs', (int) $l['id'], $data);
                flash('succes', 'Fiche du lecteur enregistrée.');
            } else {
                $l['id'] = insert('utilisateurs', $data + ['role' => 'lecteur']);
                flash('succes', 'Lecteur créé.');
            }
            if ($mdp !== null) {
                mail_mot_de_passe(lecteur_donnees($l), $mdp);
                $_SESSION['mdp_provisoire'] = ['id' => (int) $l['id'], 'mdp' => $mdp];
            }
            redirect(admin_url('lecteurs/voir/' . $l['id']));
        }
    }
    admin_page('lecteur_form', ['l' => $l, 'erreurs' => $erreurs, 'accesEnLigne' => $accesEnLigne], $l['id'] ? 'Modifier un lecteur' : 'Nouveau lecteur', 'lecteurs');
}

if ($action === 'voir') {
    $l = $lecteurParId($id);
    // Mot de passe provisoire : affiché une seule fois, juste après sa création
    $mdpProvisoire = ($_SESSION['mdp_provisoire']['id'] ?? 0) === $id ? $_SESSION['mdp_provisoire']['mdp'] : null;
    unset($_SESSION['mdp_provisoire']);
    admin_page('lecteur_voir', [
        'l' => $l,
        'mdpProvisoire' => $mdpProvisoire,
        'enCours' => prets('p.lecteur_id = ? AND p.date_retour IS NULL', [$id], 'p.date_retour_prevue'),
        'reservations' => reservations("r.lecteur_id = ? AND r.statut IN ('en_attente','disponible')", [$id]),
        'historique' => prets('p.lecteur_id = ? AND p.date_retour IS NOT NULL', [$id], 'p.date_retour DESC', 50),
    ], nom_complet($l), 'lecteurs');
}

// Liste
$filtre = get_str('filtre', 'tous');
$q = get_str('q', '');
$where = "u.role = 'lecteur'";
$where .= [
    'actifs'    => ' AND u.actif = 1',
    'a_valider' => ' AND u.en_attente = 1',
    'inactifs'  => ' AND u.actif = 0',
    'retard'    => ' AND EXISTS (SELECT 1 FROM prets p WHERE p.lecteur_id = u.id AND p.date_retour IS NULL AND p.date_retour_prevue < CURDATE())',
][$filtre] ?? '';
$params = [];
if ($q !== '') {
    $where .= " AND (CONCAT(u.prenom, ' ', u.nom) LIKE ? OR CONCAT(u.nom, ' ', u.prenom) LIKE ? OR u.email LIKE ? OR u.matricule LIKE ?)";
    $params = array_fill(0, 4, "%$q%");
}
$pg = pagination_admin((int) val("SELECT COUNT(*) FROM utilisateurs u WHERE $where", $params), 30);
$liste = rows("SELECT u.*,
                 (SELECT COUNT(*) FROM prets p WHERE p.lecteur_id = u.id AND p.date_retour IS NULL) AS nb_prets,
                 (SELECT COUNT(*) FROM prets p WHERE p.lecteur_id = u.id AND p.date_retour IS NULL AND p.date_retour_prevue < CURDATE()) AS nb_retards
               FROM utilisateurs u WHERE $where ORDER BY u.nom, u.prenom LIMIT {$pg['parPage']} OFFSET {$pg['offset']}", $params);

admin_page('lecteurs', compact('filtre', 'q', 'liste', 'pg'), 'Lecteurs', 'lecteurs');
