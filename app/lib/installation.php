<?php
/**
 * Installation d'une base neuve, partagée par install/installer.php (ligne de commande)
 * et installation.php (navigateur, pour les hébergements sans accès au terminal).
 */

/** Nombre de tables déjà présentes dans la base configurée. */
function tables_existantes(): int
{
    return (int) val('SELECT COUNT(*) FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE()');
}

/**
 * Crée toutes les tables (rôles et réglages par défaut) et charge les textes de l'interface du site.
 * Aucune donnée de catalogue, de lecteur ni de prêt.
 */
function installer_base(string $url = ''): void
{
    foreach (preg_split('/;\s*\n/', file_get_contents(ROOT_DIR . '/install/schema.sql')) as $instr) {
        $instr = trim(preg_replace('/^--.*$/m', '', $instr));
        if ($instr !== '') {
            db()->exec($instr);
        }
    }
    // Plusieurs instructions d'un coup : le contenu HTML des textes peut contenir des « ; »
    db()->exec(file_get_contents(ROOT_DIR . '/install/contenu_site.sql'));
    if ($url !== '') {
        q('REPLACE INTO reglages (cle, valeur) VALUES (?, ?)', ['site_adresse', rtrim($url, '/') . '/']);
    }
    foreach (['images/ouvrages', 'images/biblio'] as $dossier) {
        if (!is_dir(ROOT_DIR . '/' . $dossier)) {
            @mkdir(ROOT_DIR . '/' . $dossier, 0755, true); // 775 et 777 sont refusés par suPHP (o2switch)
        }
    }
}

/** Dossiers où l'administration enregistre des images et que PHP ne peut pas modifier. */
function dossiers_non_modifiables(): array
{
    return array_values(array_filter(['images/ouvrages', 'images/biblio'], fn($d) => !is_writable(ROOT_DIR . '/' . $d)));
}

/** Crée le compte Système (seul compte de la base neuve) avec le mot de passe donné. */
function creer_compte_systeme(string $email, string $motDePasse): int
{
    $roleId = (int) val('SELECT id FROM roles WHERE systeme = 1 ORDER BY id LIMIT 1');
    return insert('utilisateurs', ['role' => 'personnel', 'role_id' => $roleId, 'nom' => 'Système', 'prenom' => '',
        'email' => mb_strtolower(trim($email)), 'mot_de_passe' => password_hash($motDePasse, PASSWORD_DEFAULT), 'actif' => 1]);
}
