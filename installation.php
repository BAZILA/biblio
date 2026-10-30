<?php
/*
 * Installation par le navigateur : https://votre-site/installation.php
 *
 * Ce fichier est volontairement écrit pour fonctionner même avec un très vieux PHP : il vérifie d'abord
 * la version et les extensions, explique quoi régler (o2switch : cPanel « Sélectionner une version de PHP »
 * ou ligne AddHandler du .htaccess), puis lance l'installation (app/installation_web.php).
 * Il s'efface de lui-même une fois l'installation faite.
 */
define('INSTALLATION_PAGE', __FILE__);

$problemes = array();
if (version_compare(PHP_VERSION, '8.2.0', '<')) {
    $problemes[] = 'Version de PHP trop ancienne : <strong>' . PHP_VERSION . '</strong> (il faut 8.2 ou plus). '
        . 'Sur o2switch : cPanel → « Sélectionner une version de PHP » → 8.3, ou vérifiez la ligne <code>AddHandler application/x-httpd-alt-php83___lsphp .php</code> en haut du fichier .htaccess du site.';
}
$extensions = array(
    'pdo_mysql' => 'connexion à la base de données',
    'mbstring'  => 'textes accentués',
    'gd'        => 'images (couvertures, logo des PDF)',
    'fileinfo'  => 'contrôle des images envoyées',
    'openssl'   => 'envoi des e-mails sécurisés',
    'iconv'     => 'rapports PDF',
    'session'   => 'connexion à l’administration',
    'dom'       => 'nettoyage des textes mis en forme',
    'ctype'     => 'contrôle des formulaires',
);
foreach ($extensions as $ext => $usage) {
    if (!extension_loaded($ext)) {
        $problemes[] = 'Extension PHP <strong>' . $ext . '</strong> absente (' . $usage . ') : cPanel → « Sélectionner une version de PHP » → onglet Extensions → cochez « ' . $ext . ' ».';
    }
}
if (!is_file(dirname(__FILE__) . '/app/config.php')) {
    $problemes[] = 'Fichier <code>app/config.php</code> absent : copiez <code>app/config.example.php</code> en <code>app/config.php</code> et indiquez la base de données créée dans cPanel.';
}

if ($problemes) {
    header('Content-Type: text/html; charset=utf-8');
    header('Cache-Control: no-store');
    echo '<!DOCTYPE html><html lang="fr"><head><meta charset="utf-8"><meta name="robots" content="noindex"><title>Installation — vérifications</title>'
        . '<style>body{font:15px/1.6 system-ui,sans-serif;max-width:720px;margin:40px auto;padding:0 16px;color:#1c2433}h1{font-size:1.3rem}'
        . 'li{margin:10px 0;padding:10px 14px;background:#fde8e6;border-left:4px solid #c0362c}code{background:#f1f3f8;padding:1px 5px}</style></head><body>'
        . '<h1>Installation de la bibliothèque : à régler d’abord</h1><ul><li>' . implode('</li><li>', $problemes) . '</li></ul>'
        . '<p>PHP actuel : ' . PHP_VERSION . '. Corrigez ces points puis rechargez cette page.</p></body></html>';
    exit;
}

require dirname(__FILE__) . '/app/installation_web.php';
