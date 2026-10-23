<?php
/**
 * Comptes du personnel : chacun reçoit un rôle (Rôles et permissions).
 * Garde-fous : on n'attribue qu'un rôle dont on possède soi-même tous les droits ; on ne touche pas
 * à un compte plus puissant que soi ; on ne peut ni changer son propre rôle ni se désactiver ;
 * il reste toujours au moins un administrateur actif.
 */

/** Rôles que l'utilisateur connecté a le droit d'attribuer. */
function roles_attribuables(): array
{
    $mesDroits = role_de()['permissions'] ?? [];
    $roles = [];
    // Le rôle Système n'est attribuable (et visible) que par le compte Système
    foreach (rows('SELECT * FROM roles' . (is_systeme() ? '' : ' WHERE systeme = 0') . ' ORDER BY systeme DESC, tous_droits DESC, nom') as $r) {
        $droits = array_intersect(permissions_du_role((int) $r['id']), permissions_visibles());
        if (tous_droits() || (!$r['tous_droits'] && !array_diff($droits, $mesDroits))) {
            $roles[$r['id']] = $r;
        }
    }
    return $roles;
}

$attribuables = roles_attribuables();

if ($action === 'nouveau' || $action === 'modifier') {
    exiger($action === 'nouveau' ? 'personnel.ajouter' : 'personnel.modifier');
    $p = $action === 'modifier'
        ? (row("SELECT u.* FROM utilisateurs u LEFT JOIN roles r ON r.id = u.role_id WHERE u.id = ? AND u.role = 'personnel'"
              . (is_systeme() ? '' : ' AND COALESCE(r.systeme, 0) = 0'), [$id]) ?? admin_introuvable('Compte'))
        : ['id' => 0, 'prenom' => '', 'nom' => '', 'email' => '', 'role_id' => (int) (array_key_last($attribuables) ?? 0), 'actif' => 1];
    $estMoi = (int) $p['id'] === (int) $moi['id'];
    // Un compte dont le rôle dépasse mes droits n'est modifiable que par un administrateur (sauf le mien, pour mes coordonnées)
    if ($p['id'] && !$estMoi && $p['role_id'] && !isset($attribuables[$p['role_id']])) {
        http_response_code(403);
        admin_page('message', ['message' => 'Ce compte a un rôle plus étendu que le vôtre : seul un administrateur peut le modifier.'], 'Accès refusé', 'personnel');
    }
    $erreurs = [];
    if (is_post()) {
        csrf_verify();
        foreach (['prenom', 'nom', 'email'] as $k) {
            $p[$k] = post_str($k, '');
        }
        $p['email'] = mb_strtolower($p['email']);
        $ancienRole = (int) $p['role_id'];
        $ancienActif = (int) $p['actif'];
        if (!$estMoi) {
            $p['role_id'] = (int) post_str('role_id', '0');
            // Désactiver ou réactiver relève du droit « desactiver » : sans lui, l'état actuel est conservé (actif pour un nouveau compte)
            $p['actif'] = peut('personnel.desactiver') ? (isset($_POST['actif']) ? 1 : 0) : (int) $p['actif'];
        }
        $mdp = post_str('mot_de_passe', '', false);

        if ($p['nom'] === '') {
            $erreurs['nom'] = 'Champ requis.';
        }
        if (!filter_var($p['email'], FILTER_VALIDATE_EMAIL)) {
            $erreurs['email'] = 'Adresse e-mail invalide.';
        } elseif (val('SELECT 1 FROM utilisateurs WHERE email = ? AND id <> ?', [$p['email'], $p['id']])) {
            $erreurs['email'] = 'Adresse déjà utilisée.';
        }
        if (!$estMoi && !isset($attribuables[$p['role_id']])) {
            $erreurs['role_id'] = 'Choisissez un rôle que vous pouvez attribuer.';
        }
        // Toujours au moins un administrateur actif
        // (le compte Système ne compte pas : l'établissement doit toujours garder son propre administrateur)
        $etaitAdmin = $p['id'] && $ancienActif && (int) val('SELECT tous_droits AND NOT systeme FROM roles WHERE id = ?', [$ancienRole]);
        $resteAdmin = $p['actif'] && (int) val('SELECT tous_droits AND NOT systeme FROM roles WHERE id = ?', [$p['role_id']]);
        if ($etaitAdmin && !$resteAdmin && !val("SELECT COUNT(*) FROM utilisateurs u JOIN roles r ON r.id = u.role_id
                WHERE u.role = 'personnel' AND u.actif = 1 AND r.tous_droits = 1 AND r.systeme = 0 AND u.id <> ?", [$p['id']])) {
            $erreurs['role_id'] = 'C’est le dernier administrateur actif : nommez-en un autre avant de changer ce compte.';
        }
        if (!$p['id'] && $mdp === '') {
            $erreurs['mot_de_passe'] = 'Définissez un mot de passe initial.';
        } elseif ($mdp !== '' && mb_strlen($mdp) < 10) {
            $erreurs['mot_de_passe'] = 'Au moins 10 caractères.';
        }
        if (!$erreurs) {
            $data = ['prenom' => $p['prenom'], 'nom' => $p['nom'], 'email' => $p['email'], 'role' => 'personnel',
                'role_id' => $p['role_id'], 'actif' => $p['actif']];
            if ($mdp !== '') {
                $data['mot_de_passe'] = password_hash($mdp, PASSWORD_DEFAULT);
            }
            if ($p['id']) {
                update('utilisateurs', (int) $p['id'], $data);
            } else {
                insert('utilisateurs', $data);
            }
            flash('succes', 'Compte enregistré.');
            redirect(admin_url('personnel'));
        }
    }
    admin_page('personnel_form', ['p' => $p, 'erreurs' => $erreurs, 'roles' => $attribuables, 'estMoi' => $estMoi,
        'roleActuel' => $p['role_id'] ? row('SELECT * FROM roles WHERE id = ?', [$p['role_id']]) : null],
        $p['id'] ? 'Modifier un compte' : 'Nouveau compte', 'personnel');
}

admin_page('personnel', [
    'liste' => rows("SELECT u.*, r.nom AS role_nom, r.tous_droits, r.systeme FROM utilisateurs u LEFT JOIN roles r ON r.id = u.role_id
                     WHERE u.role = 'personnel'" . (is_systeme() ? '' : ' AND COALESCE(r.systeme, 0) = 0') . ' ORDER BY r.systeme DESC, r.tous_droits DESC, r.nom, u.nom'),
    'attribuables' => $attribuables,
], 'Comptes du personnel', 'personnel');
