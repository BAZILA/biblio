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

// Liste des étudiants de l'université (inscription en ligne vérifiée, prêt au guichet sans compte)
if (!table_existe('etudiants')) {
    preg_match('/CREATE TABLE etudiants \(.*?\) ENGINE=[^;]+;/s', file_get_contents(__DIR__ . '/schema.sql'), $m);
    db()->exec(rtrim($m[0], ';'));
    foreach (['Bibliothécaire' => ['etudiants.voir', 'import.etudiants'], 'Agent de prêt' => ['etudiants.voir']] as $role => $droits) {
        $roleId = val('SELECT id FROM roles WHERE nom = ? AND tous_droits = 0', [$role]);
        foreach ($roleId ? $droits : [] as $p) {
            q('INSERT IGNORE INTO role_permissions (role_id, permission) VALUES (?, ?)', [$roleId, $p]);
        }
    }
    echo "table etudiants créée (droits ajoutés aux rôles Bibliothécaire et Agent de prêt)\n";
    $etapes++;
} elseif (colonne_existe('etudiants', 'numero_carte')) {
    // Première version de la liste (n° de carte, faculté…) : n° de suivi, et seulement l'identité
    db()->exec('ALTER TABLE etudiants DROP INDEX uq_carte, CHANGE numero_carte numero_suivi VARCHAR(50) NOT NULL,
        CHANGE inscrit actif TINYINT(1) NOT NULL DEFAULT 1, DROP faculte, DROP departement, DROP niveau, DROP email, DROP telephone,
        ADD UNIQUE KEY uq_suivi (numero_suivi)');
    echo "liste des étudiants : n° de suivi\n";
    $etapes++;
}
if (colonne_existe('utilisateurs', 'departement')) {
    db()->exec("ALTER TABLE utilisateurs CHANGE departement parcours VARCHAR(150) NOT NULL DEFAULT ''");
    echo "lecteurs : « département » devient « parcours »\n";
    $etapes++;
}
// Activer / désactiver des étudiants de la liste : accordé au rôle Bibliothécaire fourni par défaut
$biblio = val("SELECT id FROM roles WHERE nom = 'Bibliothécaire' AND tous_droits = 0");
if ($biblio && !val("SELECT COUNT(*) FROM role_permissions WHERE permission = 'etudiants.activer'")) {
    q("INSERT IGNORE INTO role_permissions (role_id, permission) VALUES (?, 'etudiants.activer')", [$biblio]);
    echo "droit « Étudiants : activer / désactiver » ajouté au rôle Bibliothécaire\n";
    $etapes++;
}

// Faculté chargée avec la liste des étudiants
if (!colonne_existe('etudiants', 'faculte')) {
    db()->exec("ALTER TABLE etudiants ADD COLUMN faculte VARCHAR(150) NOT NULL DEFAULT '' AFTER lieu_naissance");
    echo "etudiants.faculte ajoutée\n";
    $etapes++;
}

// Diaporama : vidéo de fond et effet d'entrée
foreach (['video' => "VARCHAR(255) NOT NULL DEFAULT '' AFTER image", 'animation' => "VARCHAR(20) NOT NULL DEFAULT 'zoom' AFTER video"] as $col => $def) {
    if (!colonne_existe('slides', $col)) {
        db()->exec("ALTER TABLE slides ADD COLUMN $col $def");
        echo "slides.$col ajoutée\n";
        $etapes++;
    }
}

// Actualités et évènements, journal d'audit : mêmes définitions que dans schema.sql
foreach (['actualites', 'audit'] as $table) {
    if (!table_existe($table)) {
        preg_match('/CREATE TABLE ' . $table . ' \(.*?\) ENGINE=[^;]+;/s', file_get_contents(__DIR__ . '/schema.sql'), $m);
        db()->exec(rtrim($m[0], ';'));
        echo "table $table créée\n";
        $etapes++;
    }
}
foreach (['diaporama_actualites_max' => '6', 'diaporama_duree' => '7'] as $cle => $valeur) {
    if (!val('SELECT COUNT(*) FROM reglages WHERE cle = ?', [$cle])) {
        q('INSERT INTO reglages (cle, valeur) VALUES (?, ?)', [$cle, $valeur]);
        $etapes++;
    }
}

// Nouveaux droits accordés aux rôles fournis par défaut (une seule fois)
$droits = [
    'Bibliothécaire' => ['etudiants.ajouter', 'etudiants.modifier', 'actualites.voir', 'actualites.ajouter', 'actualites.modifier', 'actualites.supprimer', 'actualites.publier'],
    'Éditeur du site' => ['actualites.voir', 'actualites.ajouter', 'actualites.modifier', 'actualites.supprimer', 'actualites.publier'],
];
if (!val("SELECT COUNT(*) FROM role_permissions WHERE permission LIKE 'actualites.%'")) {
    foreach ($droits as $role => $liste) {
        $roleId = val('SELECT id FROM roles WHERE nom = ? AND tous_droits = 0', [$role]);
        foreach ($roleId ? $liste : [] as $p) {
            q('INSERT IGNORE INTO role_permissions (role_id, permission) VALUES (?, ?)', [$roleId, $p]);
        }
    }
    echo "droits Actualités (et Étudiants : ajouter / modifier) ajoutés aux rôles Bibliothécaire et Éditeur du site\n";
    $etapes++;
}

// Rôles masqués par le compte Système ; préférences personnelles (tableau de bord)
if (!colonne_existe('roles', 'masque')) {
    db()->exec('ALTER TABLE roles ADD COLUMN masque TINYINT(1) NOT NULL DEFAULT 0 AFTER systeme');
    echo "roles.masque ajoutée
";
    $etapes++;
}
if (!colonne_existe('utilisateurs', 'preferences')) {
    db()->exec('ALTER TABLE utilisateurs ADD COLUMN preferences TEXT NULL AFTER derniere_connexion');
    echo "utilisateurs.preferences ajoutée
";
    $etapes++;
}

// Collections éditoriales et notice bibliographique détaillée (sous-titre, tome, volume, édition, ISSN)
if (!table_existe('collections')) {
    preg_match('/CREATE TABLE collections \(.*?\) ENGINE=[^;]+;/s', file_get_contents(__DIR__ . '/schema.sql'), $m);
    db()->exec(rtrim($m[0], ';'));
    echo "table collections créée\n";
    $etapes++;
}
$notice = [
    'sous_titre'        => "VARCHAR(255) NOT NULL DEFAULT '' AFTER titre",
    'collection_id'     => 'INT UNSIGNED NULL AFTER type_id',
    'numero_collection' => "VARCHAR(30) NOT NULL DEFAULT '' AFTER collection_id",
    'edition'           => "VARCHAR(100) NOT NULL DEFAULT '' AFTER annee",
    'tome'              => "VARCHAR(30) NOT NULL DEFAULT '' AFTER edition",
    'volume'            => "VARCHAR(30) NOT NULL DEFAULT '' AFTER tome",
    'issn'              => "VARCHAR(20) NOT NULL DEFAULT '' AFTER isbn",
];
foreach ($notice as $col => $def) {
    if (!colonne_existe('ouvrages', $col)) {
        db()->exec("ALTER TABLE ouvrages ADD COLUMN $col $def");
        echo "ouvrages.$col ajoutée\n";
        $etapes++;
    }
}
if (!val("SELECT COUNT(*) FROM information_schema.STATISTICS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'ouvrages' AND INDEX_NAME = 'ix_collection'")) {
    db()->exec('ALTER TABLE ouvrages ADD KEY ix_collection (collection_id),
                ADD CONSTRAINT fk_ouvrage_collection FOREIGN KEY (collection_id) REFERENCES collections(id) ON DELETE SET NULL');
    echo "ouvrages.collection_id liée aux collections\n";
    $etapes++;
}
if (!val("SELECT COUNT(*) FROM role_permissions WHERE permission LIKE 'collections.%'")) {
    foreach (['Bibliothécaire' => ['voir', 'ajouter', 'modifier', 'supprimer'], 'Agent de prêt' => ['voir']] as $role => $actions) {
        $roleId = val('SELECT id FROM roles WHERE nom = ? AND tous_droits = 0', [$role]);
        foreach ($roleId ? $actions : [] as $a) {
            q('INSERT IGNORE INTO role_permissions (role_id, permission) VALUES (?, ?)', [$roleId, "collections.$a"]);
        }
    }
    echo "droits Collections ajoutés aux rôles Bibliothécaire et Agent de prêt\n";
    $etapes++;
}

// Indexation décimale (Dewey), emplacement dans la bibliothèque, exemplaire « perdu »
if (!table_existe('dewey')) {
    $sql = file_get_contents(__DIR__ . '/schema.sql');
    preg_match('/CREATE TABLE dewey \(.*?\) ENGINE=[^;]+;/s', $sql, $m);
    db()->exec(rtrim($m[0], ';'));
    preg_match('/INSERT INTO dewey .*?;/s', $sql, $m);
    db()->exec(rtrim($m[0], ';'));
    echo 'table dewey créée (' . val('SELECT COUNT(*) FROM dewey') . " entrées)\n";
    $etapes++;
}
foreach (['indice_dewey' => "VARCHAR(20) NOT NULL DEFAULT '' AFTER issn",
          'emplacement'  => "VARCHAR(190) NOT NULL DEFAULT '' AFTER cote"] as $col => $def) {
    if (!colonne_existe('ouvrages', $col)) {
        db()->exec("ALTER TABLE ouvrages ADD COLUMN $col $def");
        echo "ouvrages.$col ajoutée\n";
        $etapes++;
    }
}
if (!val("SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME = 'exemplaires' AND COLUMN_NAME = 'etat' AND COLUMN_TYPE LIKE '%perdu%'")) {
    db()->exec("ALTER TABLE exemplaires MODIFY COLUMN etat ENUM('neuf','bon','use','abime','perdu') NOT NULL DEFAULT 'bon'");
    echo "exemplaires.etat : état « perdu » ajouté\n";
    $etapes++;
}
// Remplacement d'un exemplaire : la ligne retirée garde son historique et pointe vers l'exemplaire neuf qui a repris son code
if (!colonne_existe('exemplaires', 'remplace_par')) {
    db()->exec('ALTER TABLE exemplaires ADD COLUMN remplace_par INT UNSIGNED NULL AFTER notes, ADD KEY ix_remplace (remplace_par)');
    db()->exec('ALTER TABLE exemplaires ADD CONSTRAINT fk_ex_remplacant FOREIGN KEY (remplace_par) REFERENCES exemplaires(id) ON DELETE SET NULL');
    echo "exemplaires.remplace_par ajouté\n";
    $etapes++;
}
if (!val('SELECT COUNT(*) FROM reglages WHERE cle = ?', ['emplacement_defaut'])) {
    q('INSERT INTO reglages (cle, valeur) VALUES (?, ?)', ['emplacement_defaut', 'Salle de lecture']);
    echo "réglage emplacement_defaut ajouté\n";
    $etapes++;
}
if (!val("SELECT COUNT(*) FROM role_permissions WHERE permission LIKE 'dewey.%'")) {
    $roleId = val('SELECT id FROM roles WHERE nom = ? AND tous_droits = 0', ['Bibliothécaire']);
    foreach ($roleId ? ['dewey.voir', 'dewey.ajouter', 'dewey.modifier', 'dewey.supprimer', 'import.collections'] : [] as $p) {
        q('INSERT IGNORE INTO role_permissions (role_id, permission) VALUES (?, ?)', [$roleId, $p]);
    }
    $agent = val('SELECT id FROM roles WHERE nom = ? AND tous_droits = 0', ['Agent de prêt']);
    if ($agent) {
        q('INSERT IGNORE INTO role_permissions (role_id, permission) VALUES (?, ?)', [$agent, 'dewey.voir']);
    }
    echo "droits Indexation Dewey ajoutés\n";
    $etapes++;
}

echo $etapes ? "Mise à jour terminée ($etapes étape(s)).\n" : "La base est déjà à jour.\n";
$sansType = (int) val('SELECT COUNT(*) FROM ouvrages WHERE type_id IS NULL');
if ($sansType) {
    echo "\nLe type de document est désormais obligatoire à la saisie : $sansType ouvrage(s) n’en ont pas encore.\n"
        . "Retrouvez-les dans Ouvrages → filtre « Sans type de document ».\n";
}
