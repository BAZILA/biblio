<?php
/**
 * Rôles et permissions : liste des rôles, grille des droits par module, duplication, suppression.
 * Garde-fous : le rôle Administrateur (tous les droits) n'est ni modifiable ni supprimable,
 * et l'on ne peut accorder que des droits que l'on possède soi-même.
 */

$mesDroits = role_de()['permissions'] ?? [];
$roleParId = fn(int $id) => row('SELECT * FROM roles WHERE id = ?' . (is_systeme() ? '' : ' AND systeme = 0'), [$id]) ?? admin_introuvable('Rôle');
/** Permissions effectives d'un rôle, telles qu'on les montre (sans les masquées) */
$droitsDuRole = fn(array $r) => $r['systeme'] ? toutes_permissions() : ($r['tous_droits'] ? permissions_visibles() : array_values(array_intersect(permissions_du_role((int) $r['id']), permissions_visibles())));

if ($action === 'supprimer') {
    exiger_post();
    exiger('roles.supprimer');
    $r = $roleParId($id);
    $nb = (int) val("SELECT COUNT(*) FROM utilisateurs WHERE role_id = ? AND role = 'personnel'", [$id]);
    if ($r['tous_droits']) {
        flash('erreur', 'Ce rôle ne peut pas être supprimé.');
    } elseif ($nb) {
        flash('erreur', "Ce rôle est attribué à " . pluriel($nb, 'compte') . ' : attribuez-leur un autre rôle avant de le supprimer.');
        redirect(admin_url('roles/modifier/' . $id));
    } else {
        q('DELETE FROM roles WHERE id = ?', [$id]);
        flash('succes', "Rôle « {$r['nom']} » supprimé.");
    }
    redirect(admin_url('roles'));
}

if ($action === 'dupliquer') {
    exiger_post();
    exiger('roles.ajouter');
    $r = $roleParId($id);
    $nom = 'Copie de ' . $r['nom'];
    for ($i = 2; val('SELECT 1 FROM roles WHERE nom = ?', [$nom]); $i++) {
        $nom = 'Copie de ' . $r['nom'] . " ($i)";
    }
    // La copie ne reprend que les droits que l'on possède soi-même
    $perms = $droitsDuRole($r);
    $perms = array_values(array_intersect($perms, tous_droits() ? permissions_visibles() : $mesDroits));
    db()->beginTransaction();
    $nouveau = insert('roles', ['nom' => mb_substr($nom, 0, 100), 'description' => $r['description']]);
    foreach ($perms as $p) {
        insert('role_permissions', ['role_id' => $nouveau, 'permission' => $p]);
    }
    db()->commit();
    flash('succes', "Rôle dupliqué : ajustez ses droits puis enregistrez.");
    redirect(admin_url('roles/modifier/' . $nouveau));
}

if ($action === 'nouveau' || $action === 'modifier') {
    exiger($action === 'nouveau' ? 'roles.ajouter' : 'roles.voir');
    $r = $action === 'modifier' ? $roleParId($id) : ['id' => 0, 'nom' => '', 'description' => '', 'tous_droits' => 0, 'systeme' => 0];
    $perms = $r['id'] ? $droitsDuRole($r) : [];
    $lectureSeule = $r['tous_droits'] || ($action === 'modifier' && !peut('roles.modifier'));
    $erreurs = [];

    if (is_post()) {
        csrf_verify();
        exiger($action === 'nouveau' ? 'roles.ajouter' : 'roles.modifier');
        if ($r['tous_droits']) {
            flash('erreur', 'Ce rôle a toujours tous les droits : il ne se modifie pas.');
            redirect(admin_url('roles/modifier/' . $id));
        }
        $r['nom'] = post_str('nom', '');
        $r['description'] = post_str('description', '');
        $cochees = is_array($_POST['permissions'] ?? null) ? $_POST['permissions'] : [];
        $voulues = normaliser_permissions($cochees);
        if (!tous_droits()) {
            // Pas d'élévation de privilèges : les droits que je n'ai pas restent tels qu'ils étaient sur ce rôle
            $horsDePortee = array_diff($perms, $mesDroits);
            $voulues = array_values(array_unique(array_merge(array_intersect($voulues, $mesDroits), $horsDePortee)));
        }
        // Les permissions masquées déjà accordées sont conservées en réserve : elles reviennent si le Système les rend visibles
        if ($r['id']) {
            $voulues = array_values(array_unique(array_merge($voulues, array_intersect(permissions_du_role((int) $r['id']), permissions_masquees()))));
        }
        sort($voulues);
        if ($r['nom'] === '') {
            $erreurs['nom'] = 'Donnez un nom au rôle.';
        } elseif (val('SELECT 1 FROM roles WHERE nom = ? AND id <> ?', [$r['nom'], (int) $r['id']])) {
            $erreurs['nom'] = 'Un autre rôle porte déjà ce nom.';
        }
        if (!$erreurs) {
            db()->beginTransaction();
            $data = ['nom' => mb_substr($r['nom'], 0, 100), 'description' => mb_substr($r['description'], 0, 255)];
            if ($r['id']) {
                update('roles', (int) $r['id'], $data);
            } else {
                $r['id'] = insert('roles', $data);
            }
            q('DELETE FROM role_permissions WHERE role_id = ?', [$r['id']]);
            foreach ($voulues as $p) {
                insert('role_permissions', ['role_id' => $r['id'], 'permission' => $p]);
            }
            db()->commit();
            flash('succes', "Rôle « {$r['nom']} » enregistré : " . pluriel(count(array_intersect($voulues, permissions_visibles())), 'permission') . '.');
            redirect(admin_url('roles/modifier/' . $r['id']));
        }
        $perms = array_values(array_intersect($voulues, permissions_visibles()));
    }

    admin_page('role_form', [
        'r' => $r,
        'perms' => $perms,
        'erreurs' => $erreurs,
        'lectureSeule' => $lectureSeule,
        'mesDroits' => tous_droits() ? permissions_visibles() : $mesDroits,
        'visibles' => $r['systeme'] ? toutes_permissions() : permissions_visibles(),
        'comptes' => $r['id'] ? rows("SELECT id, nom, prenom, email, actif FROM utilisateurs WHERE role_id = ? AND role = 'personnel' ORDER BY nom", [$r['id']]) : [],
    ], $r['id'] ? 'Rôle : ' . $r['nom'] : 'Nouveau rôle', 'roles');
}

admin_page('roles', [
    'roles' => array_map(fn($r) => $r + ['nb_permissions' => count($droitsDuRole($r))],
        rows("SELECT r.*, (SELECT COUNT(*) FROM utilisateurs u WHERE u.role_id = r.id AND u.role = 'personnel') AS nb_comptes
              FROM roles r" . (is_systeme() ? '' : ' WHERE r.systeme = 0') . ' ORDER BY r.systeme DESC, r.tous_droits DESC, r.nom')),
    'total' => count(permissions_visibles()),
], 'Rôles et permissions', 'roles');
