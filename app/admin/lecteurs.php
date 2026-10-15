<?php
/** Gestion des lecteurs : liste, fiche, création / modification, validation des inscriptions. */

$lecteurParId = fn(int $id) => row("SELECT * FROM utilisateurs WHERE id = ? AND role = 'lecteur'", [$id]) ?? admin_introuvable('Lecteur');

if ($action === 'activer' || $action === 'desactiver') {
    exiger_post();
    $l = $lecteurParId($id);
    q('UPDATE utilisateurs SET actif = ?, en_attente = 0 WHERE id = ?', [$action === 'activer' ? 1 : 0, $id]);
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

if ($action === 'nouveau' || $action === 'modifier') {
    $l = $action === 'modifier' ? $lecteurParId($id) : [
        'id' => 0, 'prenom' => '', 'nom' => '', 'email' => '', 'matricule' => '', 'categorie' => 'etudiant', 'telephone' => '', 'actif' => 1,
    ];
    $erreurs = [];
    if (is_post()) {
        csrf_verify();
        foreach (['prenom', 'nom', 'email', 'matricule', 'categorie', 'telephone'] as $k) {
            $l[$k] = post_str($k, '');
        }
        $l['email'] = mb_strtolower($l['email']);
        $l['actif'] = isset($_POST['actif']) ? 1 : 0;
        $mdp = post_str('mot_de_passe', '', false);

        if ($l['nom'] === '') {
            $erreurs['nom'] = 'Champ requis.';
        }
        if (!filter_var($l['email'], FILTER_VALIDATE_EMAIL)) {
            $erreurs['email'] = 'Adresse e-mail invalide.';
        } elseif (val('SELECT 1 FROM utilisateurs WHERE email = ? AND id <> ?', [$l['email'], $l['id']])) {
            $erreurs['email'] = 'Un autre compte utilise déjà cette adresse.';
        }
        if ($l['matricule'] !== '' && val('SELECT 1 FROM utilisateurs WHERE matricule = ? AND id <> ?', [$l['matricule'], $l['id']])) {
            $erreurs['matricule'] = 'Matricule déjà attribué.';
        }
        if (!isset(LIBELLES_CATEGORIE[$l['categorie']])) {
            $erreurs['categorie'] = 'Catégorie invalide.';
        }
        if ($mdp !== '' && mb_strlen($mdp) < 8) {
            $erreurs['mot_de_passe'] = 'Au moins 8 caractères.';
        }

        if (!$erreurs) {
            $data = [
                'prenom' => $l['prenom'], 'nom' => $l['nom'], 'email' => $l['email'],
                'matricule' => $l['matricule'] !== '' ? $l['matricule'] : null,
                'categorie' => $l['categorie'], 'telephone' => $l['telephone'], 'actif' => $l['actif'],
                'en_attente' => $l['actif'] ? 0 : (int) ($l['en_attente'] ?? 0),
            ];
            if ($mdp !== '') {
                $data['mot_de_passe'] = password_hash($mdp, PASSWORD_DEFAULT);
            }
            if ($l['id']) {
                update('utilisateurs', (int) $l['id'], $data);
                flash('succes', 'Fiche du lecteur enregistrée.');
            } else {
                $l['id'] = insert('utilisateurs', $data + ['role' => 'lecteur']);
                flash('succes', 'Lecteur créé.' . ($mdp === '' ? ' Sans mot de passe, il ne peut pas encore se connecter à son espace en ligne.' : ''));
            }
            redirect(admin_url('lecteurs/voir/' . $l['id']));
        }
    }
    admin_page('lecteur_form', ['l' => $l, 'erreurs' => $erreurs], $l['id'] ? 'Modifier un lecteur' : 'Nouveau lecteur', 'lecteurs');
}

if ($action === 'voir') {
    $l = $lecteurParId($id);
    admin_page('lecteur_voir', [
        'l' => $l,
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
