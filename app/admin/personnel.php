<?php
/** Comptes du personnel (administrateurs et bibliothécaires) — réservé aux administrateurs. */

exiger_admin();
const LIBELLES_ROLE = ['bibliothecaire' => 'Bibliothécaire', 'admin' => 'Administrateur'];

if ($action === 'nouveau' || $action === 'modifier') {
    $p = $action === 'modifier'
        ? (row("SELECT * FROM utilisateurs WHERE id = ? AND role IN ('admin','bibliothecaire')", [$id]) ?? admin_introuvable('Compte'))
        : ['id' => 0, 'prenom' => '', 'nom' => '', 'email' => '', 'role' => 'bibliothecaire', 'actif' => 1];
    $erreurs = [];
    if (is_post()) {
        csrf_verify();
        foreach (['prenom', 'nom', 'email', 'role'] as $k) {
            $p[$k] = post_str($k, '');
        }
        $p['email'] = mb_strtolower($p['email']);
        $p['actif'] = isset($_POST['actif']) ? 1 : 0;
        $mdp = post_str('mot_de_passe', '', false);
        $estMoi = (int) $p['id'] === (int) $moi['id'];

        if ($p['nom'] === '') {
            $erreurs['nom'] = 'Champ requis.';
        }
        if (!filter_var($p['email'], FILTER_VALIDATE_EMAIL)) {
            $erreurs['email'] = 'Adresse e-mail invalide.';
        } elseif (val('SELECT 1 FROM utilisateurs WHERE email = ? AND id <> ?', [$p['email'], $p['id']])) {
            $erreurs['email'] = 'Adresse déjà utilisée.';
        }
        if (!isset(LIBELLES_ROLE[$p['role']])) {
            $erreurs['role'] = 'Rôle invalide.';
        }
        if ($estMoi && ($p['role'] !== 'admin' || !$p['actif'])) {
            $erreurs['role'] = 'Vous ne pouvez pas retirer vos propres droits d’administrateur.';
        }
        if (!$p['id'] && $mdp === '') {
            $erreurs['mot_de_passe'] = 'Définissez un mot de passe initial.';
        } elseif ($mdp !== '' && mb_strlen($mdp) < 10) {
            $erreurs['mot_de_passe'] = 'Au moins 10 caractères.';
        }
        if (!$erreurs) {
            $data = ['prenom' => $p['prenom'], 'nom' => $p['nom'], 'email' => $p['email'], 'role' => $p['role'], 'actif' => $p['actif']];
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
    admin_page('personnel_form', ['p' => $p, 'erreurs' => $erreurs], $p['id'] ? 'Modifier un compte' : 'Nouveau compte', 'personnel');
}

admin_page('personnel', [
    'liste' => rows("SELECT * FROM utilisateurs WHERE role IN ('admin','bibliothecaire') ORDER BY role, nom"),
], 'Comptes du personnel', 'personnel');
