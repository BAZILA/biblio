<?php
/**
 * Installation sur un serveur neuf : base vide + compte Système.
 *
 *   php install/installer.php --url=https://biblio.exemple.org/ [--email=systeme@exemple.org] [--reinitialiser]
 *
 * 1. crée la base si elle n'existe pas, puis toutes les tables (install/schema.sql) avec les rôles et réglages par défaut ;
 * 2. charge les textes de l'interface du site (install/contenu_site.sql) — aucun ouvrage, lecteur ni prêt ;
 * 3. crée le compte Système (seul compte) et écrit ses identifiants dans install/SYSTEME.txt.
 * Refuse de toucher une base qui contient déjà des tables, sauf avec --reinitialiser (efface tout).
 */
if (PHP_SAPI !== 'cli') {
    exit('À lancer en ligne de commande.');
}
if (!is_file(dirname(__DIR__) . '/app/config.php')) {
    exit("Copiez d'abord app/config.example.php en app/config.php et renseignez la base de données.\n");
}
require dirname(__DIR__) . '/app/bootstrap.php';
require ROOT_DIR . '/app/lib/installation.php';

$options = getopt('', ['url:', 'email:', 'reinitialiser']);
$url = trim((string) ($options['url'] ?? ''));
if ($url !== '' && !preg_match('#^https?://[^\s/]+(/.*)?$#i', $url)) {
    exit("Adresse invalide : --url=https://votre-domaine/\n");
}
$cfg = $GLOBALS['config']['db'];

// 1. Base et tables
try {
    $serveur = new PDO("mysql:host={$cfg['host']};charset=utf8mb4", $cfg['user'], $cfg['password'], [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    $serveur->exec("CREATE DATABASE IF NOT EXISTS `{$cfg['name']}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
} catch (PDOException $ex) {
    echo "Création de la base impossible ({$ex->getMessage()}) : on suppose qu'elle existe déjà.\n";
}
$tables = tables_existantes();
if ($tables && !isset($options['reinitialiser'])) {
    exit("La base « {$cfg['name']} » contient déjà $tables table(s) : rien n'a été modifié.\n"
        . "Pour une mise à jour, utilisez php install/mettre_a_jour.php ; pour TOUT effacer et repartir de zéro, ajoutez --reinitialiser.\n");
}
// 2. Tables, rôles et réglages par défaut, textes de l'interface du site
installer_base($url);
echo "Base « {$cfg['name']} » installée (tables, rôles, réglages, textes du site)"
    . ($url !== '' ? ', adresse publique : ' . rtrim($url, '/') . '/' : '') . ".\n";
foreach (dossiers_non_modifiables() as $dossier) {
    echo "ATTENTION : $dossier n'est pas modifiable par PHP (droits à donner au serveur web).\n";
}

// 3. Compte Système, seul compte de la base
$args = isset($options['email']) ? ' ' . escapeshellarg((string) $options['email']) : '';
passthru(escapeshellarg(PHP_BINARY) . ' ' . escapeshellarg(__DIR__ . '/compte_systeme.php') . $args, $code);
if ($code !== 0) {
    exit("Le compte Système n'a pas pu être créé.\n");
}

echo "\nInstallation terminée. Étapes suivantes :\n"
    . "  1. lisez install/SYSTEME.txt et connectez-vous sur " . ($url !== '' ? rtrim($url, '/') : '<votre site>') . "/admin/\n"
    . "  2. changez le mot de passe dans « Mon compte », puis supprimez install/SYSTEME.txt ;\n"
    . "  3. créez le compte de l'établissement : Configuration → Comptes du personnel → rôle Administrateur ;\n"
    . "  4. réglez les e-mails (Réglages → E-mails aux lecteurs) et la tâche quotidienne (voir DEPLOIEMENT.md).\n";
