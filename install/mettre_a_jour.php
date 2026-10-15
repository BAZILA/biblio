<?php
/**
 * Met à jour la structure d'une base déjà installée, sans rien effacer.
 * À lancer après une mise à jour du code :  php install/mettre_a_jour.php
 * Chaque étape vérifie d'abord si elle est déjà faite : le script peut être relancé sans risque.
 */
if (PHP_SAPI !== 'cli') {
    exit('À lancer en ligne de commande.');
}
require __DIR__ . '/../app/bootstrap.php';

function colonne_existe(string $table, string $colonne): bool
{
    return (bool) val('SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = ?', [$table, $colonne]);
}

function table_existe(string $table): bool
{
    return (bool) val('SELECT COUNT(*) FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ?', [$table]);
}

$etapes = 0;
$colonnes = [
    'etat_sortie'     => "ENUM('neuf','bon','use','abime') NULL AFTER notes",
    'etat_retour'     => "ENUM('conforme','use','abime','perdu') NULL AFTER etat_sortie",
    'note_retour'     => "VARCHAR(255) NOT NULL DEFAULT '' AFTER etat_retour",
    'agent_retour_id' => 'INT UNSIGNED NULL AFTER note_retour',
];
foreach ($colonnes as $col => $def) {
    if (!colonne_existe('prets', $col)) {
        db()->exec("ALTER TABLE prets ADD COLUMN $col $def");
        echo "prets.$col ajoutée\n";
        $etapes++;
    }
}

foreach (['actif' => 'TINYINT(1) NOT NULL DEFAULT 1 AFTER publie', 'desactive_le' => 'DATETIME NULL AFTER actif'] as $col => $def) {
    if (!colonne_existe('ouvrages', $col)) {
        db()->exec("ALTER TABLE ouvrages ADD COLUMN $col $def");
        echo "ouvrages.$col ajoutée\n";
        $etapes++;
    }
}

if (!table_existe('demandes_prolongation')) {
    // Même définition que dans schema.sql
    preg_match('/CREATE TABLE demandes_prolongation \(.*?\) ENGINE=[^;]+;/s', file_get_contents(__DIR__ . '/schema.sql'), $m);
    db()->exec(rtrim($m[0], ';'));
    echo "table demandes_prolongation créée\n";
    $etapes++;
}

// Rôles et permissions : tables, rôles par défaut, puis passage des comptes admin / bibliothécaire au rôle correspondant
if (!table_existe('roles')) {
    $schema = file_get_contents(__DIR__ . '/schema.sql');
    foreach (['/CREATE TABLE roles \(.*?\) ENGINE=[^;]+;/s', '/CREATE TABLE role_permissions \(.*?\) ENGINE=[^;]+;/s',
              '/INSERT INTO roles \(.*?\);\n/s', '/INSERT INTO role_permissions \(.*?\);\n/s'] as $motif) {
        preg_match($motif, $schema, $m);
        db()->exec(rtrim(trim($m[0]), ';'));
    }
    echo "tables roles et role_permissions créées (4 rôles par défaut)\n";
    $etapes++;
}
if (!colonne_existe('utilisateurs', 'role_id')) {
    db()->exec("ALTER TABLE utilisateurs MODIFY role ENUM('admin','bibliothecaire','personnel','lecteur') NOT NULL DEFAULT 'lecteur'");
    db()->exec('ALTER TABLE utilisateurs ADD COLUMN role_id INT UNSIGNED NULL AFTER role');
    q("UPDATE utilisateurs SET role_id = 1, role = 'personnel' WHERE role = 'admin'");
    q("UPDATE utilisateurs SET role_id = 2, role = 'personnel' WHERE role = 'bibliothecaire'");
    db()->exec("ALTER TABLE utilisateurs MODIFY role ENUM('personnel','lecteur') NOT NULL DEFAULT 'lecteur'");
    db()->exec('ALTER TABLE utilisateurs ADD CONSTRAINT fk_utilisateur_role FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE SET NULL');
    echo "comptes du personnel rattachés aux rôles Administrateur et Bibliothécaire\n";
    $etapes++;
}

// Compte Système (super administrateur) et permissions masquées
if (!colonne_existe('roles', 'systeme')) {
    db()->exec('ALTER TABLE roles ADD COLUMN systeme TINYINT(1) NOT NULL DEFAULT 0 AFTER tous_droits');
    echo "roles.systeme ajoutée\n";
    $etapes++;
}
if (!val('SELECT COUNT(*) FROM roles WHERE systeme = 1')) {
    $nom = val("SELECT 1 FROM roles WHERE nom = 'Système'") ? 'Système (technique)' : 'Système';
    insert('roles', ['nom' => $nom, 'tous_droits' => 1, 'systeme' => 1,
        'description' => 'Compte technique lié au système : tous les droits, masque des permissions, invisible des autres comptes.']);
    echo "rôle Système créé\n";
    $etapes++;
}
if (!val("SELECT COUNT(*) FROM reglages WHERE cle = 'permissions_masquees'")) {
    q("INSERT INTO reglages (cle, valeur) VALUES ('permissions_masquees', '')");
    $etapes++;
}

// Statistiques et rapports : accordés au rôle Bibliothécaire fourni par défaut
$biblio = val("SELECT id FROM roles WHERE nom = 'Bibliothécaire' AND tous_droits = 0");
if ($biblio && !val("SELECT COUNT(*) FROM role_permissions WHERE permission LIKE 'rapports.%'")) {
    foreach (['rapports.voir', 'rapports.exporter'] as $p) {
        q('INSERT IGNORE INTO role_permissions (role_id, permission) VALUES (?, ?)', [$biblio, $p]);
    }
    echo "droits « Statistiques et rapports » ajoutés au rôle Bibliothécaire\n";
    $etapes++;
}

if (!val("SELECT COUNT(*) FROM reglages WHERE cle = 'photo_apropos'")) {
    q("INSERT INTO reglages (cle, valeur) VALUES ('photo_apropos', 'images/biblio/apropos-rayonnages.jpg')");
    q("UPDATE blocs SET contenu = REPLACE(contenu, '{base}/images/biblio/apropos.jpg', '{photo_apropos}') WHERE cle = 'accueil_apropos'");
    echo "photo « À propos » réglable\n";
    $etapes++;
}

echo $etapes ? "Mise à jour terminée ($etapes étape(s)).\n" : "La base est déjà à jour.\n";
