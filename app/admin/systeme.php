<?php
/**
 * Page du compte Système (super administrateur) : masquer des permissions à tous les autres comptes,
 * Administrateur compris. Une permission masquée disparaît des menus, des grilles de rôles et des droits effectifs ;
 * masquer « voir » masque toute la rubrique. Le routeur renvoie une page introuvable à tout autre compte.
 */

if (is_post()) {
    csrf_verify();
    $cochees = is_array($_POST['masquees'] ?? null) ? array_map('strval', $_POST['masquees']) : [];
    $masquees = array_values(array_intersect(array_unique($cochees), toutes_permissions()));
    foreach ($masquees as $p) {
        [$module, $act] = explode('.', $p, 2);
        if ($act === 'voir') { // rubrique invisible : toutes ses actions le sont aussi
            foreach (toutes_permissions() as $q) {
                if (str_starts_with($q, "$module.")) {
                    $masquees[] = $q;
                }
            }
        }
    }
    $masquees = array_values(array_unique($masquees));
    sort($masquees);
    q('REPLACE INTO reglages (cle, valeur) VALUES (?, ?)', ['permissions_masquees', implode(',', $masquees)]);
    flash('succes', $masquees ? pluriel(count($masquees), 'permission masquée', 'permissions masquées') . ' aux autres comptes.' : 'Aucune permission masquée.');
    redirect(admin_url('systeme'));
}

// Combien de rôles (hors Système) disposent de chaque permission : utile avant de la masquer
$parPermission = [];
foreach (rows('SELECT rp.permission, COUNT(*) AS nb FROM role_permissions rp JOIN roles r ON r.id = rp.role_id WHERE r.systeme = 0 GROUP BY rp.permission') as $l) {
    $parPermission[$l['permission']] = (int) $l['nb'];
}

admin_page('systeme', [
    'masquees' => permissions_masquees(),
    'parPermission' => $parPermission,
    'nbAdmins' => (int) val("SELECT COUNT(*) FROM utilisateurs u JOIN roles r ON r.id = u.role_id WHERE u.role = 'personnel' AND r.tous_droits = 1 AND r.systeme = 0 AND u.actif = 1"),
    'comptes' => rows("SELECT u.* FROM utilisateurs u JOIN roles r ON r.id = u.role_id WHERE r.systeme = 1 ORDER BY u.id"),
], 'Système', 'systeme');
