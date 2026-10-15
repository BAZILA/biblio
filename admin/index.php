<?php
/**
 * Administration de la bibliothèque — contrôleur frontal.
 * Adresses : admin/index.php/<section>/<action>/<id>
 */

require dirname(__DIR__) . '/app/bootstrap.php';
require ROOT_DIR . '/app/lib/upload.php';
require ROOT_DIR . '/app/lib/formulaires.php';

header('X-Frame-Options: SAMEORIGIN');
header('X-Content-Type-Options: nosniff');

$segments = current_path() === '' ? [] : explode('/', current_path());
$section  = $segments[0] ?? '';
$action   = $segments[1] ?? '';
$id       = isset($segments[2]) ? (int) $segments[2] : 0;

/** Affiche une page d'administration dans le gabarit et termine. */
function admin_page(string $vue, array $vars, string $titre, string $section): never
{
    $contenu = render(ROOT_DIR . '/views/admin/' . $vue . '.php', $vars);
    echo render(ROOT_DIR . '/views/admin/layout.php', compact('titre', 'contenu', 'section'));
    exit;
}

function admin_introuvable(string $quoi = 'Élément'): never
{
    http_response_code(404);
    admin_page('message', ['message' => "$quoi introuvable."], 'Introuvable', '');
}

/** Exige une requête POST avec jeton CSRF valide. */
function exiger_post(): void
{
    if (!is_post()) {
        http_response_code(405);
        exit('Méthode non autorisée.');
    }
    csrf_verify();
}

// Pages accessibles sans être connecté
if ($section === 'connexion' || $section === 'deconnexion') {
    require ROOT_DIR . '/app/admin/connexion.php';
    exit;
}

$moi = exiger_personnel();
expirer_reservations();

$controleurs = [
    ''             => 'tableau',
    'guichet'      => 'guichet',
    'prets'        => 'prets',
    'reservations' => 'reservations',
    'lecteurs'     => 'lecteurs',
    'ouvrages'     => 'ouvrages',
    'exemplaires'  => 'ouvrages',
    'auteurs'      => 'crud',
    'domaines'     => 'crud',
    'types'        => 'crud',
    'slides'       => 'crud',
    'citations'    => 'crud',
    'personnel'    => 'personnel',
    'blocs'        => 'blocs',
    'messages'     => 'messages',
    'reglages'     => 'reglages',
    'mon-compte'   => 'connexion',
];

if (!isset($controleurs[$section])) {
    admin_introuvable('Page');
}
require ROOT_DIR . '/app/admin/' . $controleurs[$section] . '.php';
