<?php
/**
 * Point d'entrée commun du site public et de l'administration.
 * Charge la configuration, ouvre la session et fournit les fonctions de base.
 */

declare(strict_types=1);

define('APP_DIR', __DIR__);
define('ROOT_DIR', dirname(__DIR__));

$configFile = APP_DIR . '/config.php';
if (!is_file($configFile)) {
    http_response_code(500);
    exit('Configuration manquante : copiez app/config.example.php en app/config.php.');
}
$GLOBALS['config'] = require $configFile;

date_default_timezone_set($GLOBALS['config']['timezone'] ?? 'UTC');
if (!empty($GLOBALS['config']['debug'])) {
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
} else {
    ini_set('display_errors', '0');
}
mb_internal_encoding('UTF-8');

if (PHP_SAPI !== 'cli') {
    header_remove('X-Powered-By');
    session_name('biblio_sess');
    session_set_cookie_params([
        'httponly' => true,
        'samesite' => 'Lax',
        'secure'   => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
    ]);
    session_start();
}

require APP_DIR . '/lib/db.php';
require APP_DIR . '/lib/helpers.php';
require APP_DIR . '/lib/auth.php';
require APP_DIR . '/lib/catalogue.php';
require APP_DIR . '/lib/prets.php';
