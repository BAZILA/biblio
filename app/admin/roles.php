<?php
/**
 * Rôles et permissions : liste des rôles, grille des droits par module, duplication, suppression.
 * Garde-fous : le rôle Administrateur (tous les droits) n'est modifiable que par le compte Système et n'est pas supprimable ;
 * l'on ne peut accorder que des droits que l'on possède soi-même. Le compte Système peut aussi masquer un rôle (et ses comptes).
 */

$mesDroits = role_de()['permissions'] ?? [];
$roleParId = fn(int $id) => row('SELECT * FROM roles r WHERE r.id = ? AND ' . roles_visibles_sql(), [$id]) ?? admin_introuvable('Rôle');
/** Permissions effectives d'un rôle, telles qu'on les montre (sans les masquées) */
$droitsDuRole = fn(array $r) => $r['systeme'] ? toutes_permissions() : ($r['tous_droits'] ? permissions_visibles() : array_values(array_intersect(permissions_du_role((int) $r['id']), permissions_visibles())));

if ($action === 'supprimer') {
    exiger_post();
    exiger('roles.supprimer');
    $r = $roleParId($id);
    $nb = (int) val("SELECT COUNT(*) FROM utilisateurs WHERE role_id = ? AND role = 'personnel'", [$id]);
    // Le compte Système peut supprimer tout rôle sauf le rôle Système, même l'Administrateur ou un rôle attribué :
    // ses comptes passent alors au rôle de remplacement choisi
    $remplacant = is_systeme() ? row('SELECT * FROM roles WHERE id = ? AND systeme = 0 AND id <> ?', [(int) post_str('remplacant'), $id]) : null;
    if ($r['systeme']) {
        flash('erreur', 'Le rôle Système ne peut pas être supprimé.');
    } elseif ($r['tous_droits'] && !is_systeme()) {
        flash('erreur', 'Ce rôle ne peut pas être supprimé.');
    } elseif ($nb && !$remplacant) {
        flash('erreur', "Ce rôle est attribué à " . pluriel($nb, 'compte') . ' : ' . (is_systeme()
            ? 'choisissez le rôle de remplacement de ces comptes.' : 'attribuez-leur un autre rôle avant de le supprimer.'));
        redirect(admin_url('roles/modifier/' . $id));
    } else {
        db()->beginTransaction();
        if ($nb) {
            q("UPDATE utilisateurs SET role_id = ? WHERE role_id = ? AND role = 'personnel'", [$remplacant['id'], $id]);
        }
        q('DELETE FROM roles WHERE id = ?', [$id]); // ses permissions suivent (clé étrangère)
        db()->commit();
        flash('succes', "Rôle « {$r['nom']} » supprimé." . ($nb ? ' ' . pluriel($nb, 'compte passé', 'comptes passés') . " au rôle « {$remplacant['nom']} »." : ''));
        if ($r['tous_droits'] && !val('SELECT COUNT(*) FROM roles WHERE tous_droits = 1 AND systeme = 0')) {
            flash('info', 'Plus aucun rôle n’a « tous les droits » : l’établissement n’a plus d’administrateur complet. Vous pouvez cocher « Tous les droits » sur un autre rôle.');
        }
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
    $r = $action === 'modifier' ? $roleParId($id) : ['id' => 0, 'nom' => '', 'description' => '', 'tous_droits' => 0, 'systeme' => 0, 'masque' => 0];
    $perms = $r['id'] ? $droitsDuRole($r) : [];
    // Le rôle Système ne se modifie jamais ; un rôle « tous les droits » (Administrateur) seulement par le compte Système
    $lectureSeule = $r['systeme'] || ($r['tous_droits'] && !is_systeme()) || ($action === 'modifier' && !peut('roles.modifier'));
    $erreurs = [];

    if (is_post()) {
        csrf_verify();
        exiger($action === 'nouveau' ? 'roles.ajouter' : 'roles.modifier');
        if ($r['systeme'] || ($r['tous_droits'] && !is_systeme())) {
            flash('erreur', 'Ce rôle a toujours tous les droits : il ne se modifie pas.');
            redirect(admin_url('roles/modifier/' . $id));
        }
        // Réservé au compte Système : « tous les droits » et rôle masqué aux autres comptes
        if (is_systeme()) {
            $r['tous_droits'] = isset($_POST['tous_droits']) ? 1 : 0;
            $r['masque'] = isset($_POST['masque']) && !$r['tous_droits'] ? 1 : 0;
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
            $data = ['nom' => mb_substr($r['nom'], 0, 100), 'description' => mb_substr($r['description'], 0, 255)]
                + (is_systeme() ? ['tous_droits' => $r['tous_droits'], 'masque' => $r['masque']] : []);
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
            flash('succes', "Rôle « {$r['nom']} » enregistré : " . ($r['tous_droits'] ? 'tous les droits' : pluriel(count(array_intersect($voulues, permissions_visibles())), 'permission'))
                . ($r['masque'] ? ' — masqué aux autres comptes' : '') . '.');
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
        'remplacants' => is_systeme() && $r['id'] ? rows('SELECT id, nom, tous_droits FROM roles WHERE systeme = 0 AND id <> ? ORDER BY tous_droits DESC, nom', [$r['id']]) : [],
    ], $r['id'] ? 'Rôle : ' . $r['nom'] : 'Nouveau rôle', 'roles');
}

admin_page('roles', [
    'roles' => array_map(fn($r) => $r + ['nb_permissions' => count($droitsDuRole($r))],
        rows("SELECT r.*, (SELECT COUNT(*) FROM utilisateurs u WHERE u.role_id = r.id AND u.role = 'personnel') AS nb_comptes
              FROM roles r WHERE " . roles_visibles_sql() . ' ORDER BY r.systeme DESC, r.tous_droits DESC, r.masque, r.nom')),
    'total' => count(permissions_visibles()),
], 'Rôles et permissions', 'roles');
