<?php
/** Connexion, déconnexion et « Mon compte » du personnel. */

if ($section === 'deconnexion') {
    if (is_post()) {
        csrf_verify();
        deconnexion();
    }
    redirect(admin_url('connexion'));
}

if ($section === 'connexion') {
    if (is_personnel()) {
        redirect(admin_url());
    }
    $erreur = '';
    $email = '';
    $retour = (post_str('retour') ?: get_str('retour'));
    if (is_post()) {
        csrf_verify();
        $email = post_str('email', '');
        $res = tenter_connexion($email, post_str('mot_de_passe', '', false));
        if (is_array($res) && in_array($res['role'], ROLES_PERSONNEL, true)) {
            redirect(retour_sur($retour, admin_url()));
        }
        if (is_array($res)) {
            deconnexion(); // un lecteur n'a rien à faire ici
            $erreur = 'Ce compte n’a pas accès à l’administration.';
        } else {
            $erreur = $res;
        }
    }
    echo render(ROOT_DIR . '/views/admin/connexion.php', compact('erreur', 'email', 'retour'));
    exit;
}

// Mon compte : nom, e-mail, mot de passe
$erreurs = [];
if (is_post()) {
    csrf_verify();
    $nom = post_str('nom', '');
    $prenom = post_str('prenom', '');
    $email = mb_strtolower(post_str('email'));
    $nouveau = post_str('nouveau', '', false);
    if ($nom === '') {
        $erreurs['nom'] = 'Champ requis.';
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $erreurs['email'] = 'Adresse e-mail invalide.';
    } elseif (val('SELECT 1 FROM utilisateurs WHERE email = ? AND id <> ?', [$email, $moi['id']])) {
        $erreurs['email'] = 'Adresse déjà utilisée par un autre compte.';
    }
    if (!password_verify(post_str('actuel', '', false), $moi['mot_de_passe'])) {
        $erreurs['actuel'] = 'Mot de passe actuel incorrect.';
    }
    if ($nouveau !== '' && mb_strlen($nouveau) < 10) {
        $erreurs['nouveau'] = 'Au moins 10 caractères pour un compte du personnel.';
    }
    if (!$erreurs) {
        $data = ['nom' => $nom, 'prenom' => $prenom, 'email' => $email];
        if ($nouveau !== '') {
            $data['mot_de_passe'] = password_hash($nouveau, PASSWORD_DEFAULT);
        }
        update('utilisateurs', (int) $moi['id'], $data);
        flash('succes', 'Votre compte a été mis à jour.');
        redirect(admin_url('mon-compte'));
    }
    $moi = array_merge($moi, compact('nom', 'prenom', 'email'));
}
admin_page('mon_compte', ['moi' => $moi, 'erreurs' => $erreurs], 'Mon compte', '');
