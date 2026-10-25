<?php
/**
 * Administration de la bibliothèque — contrôleur frontal.
 * Adresses : admin/index.php/<section>/<action>/<id>
 */

require dirname(__DIR__) . '/app/bootstrap.php';
require ROOT_DIR . '/app/lib/upload.php';
require ROOT_DIR . '/app/lib/formulaires.php';
require ROOT_DIR . '/app/lib/icones.php';
require ROOT_DIR . '/app/lib/import.php';
require ROOT_DIR . '/app/lib/pdf.php';
require ROOT_DIR . '/app/lib/rapports.php';

header('X-Frame-Options: SAMEORIGIN');
header('X-Content-Type-Options: nosniff');

$segments = current_path() === '' ? [] : explode('/', current_path());
$section  = $segments[0] ?? '';
$action   = $segments[1] ?? '';
$id       = isset($segments[2]) ? (int) $segments[2] : 0;

// Journal d'audit : toute page consultée et toute action de l'administration (sauf les recherches techniques en arrière-plan)
const ESPACE_ADMIN = true;
audit_requete(fn() => audit_libelle_admin($segments, $_SERVER['REQUEST_METHOD'] ?? 'GET'), true,
    ($section === 'auteurs' && $action === 'chercher') || ($section === 'guichet' && in_array($action, ['exemplaires', 'etudiants'], true)));

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
taches_quotidiennes();

$controleurs = [
    ''             => 'tableau',
    'tableau'      => 'tableau',
    'guichet'      => 'guichet',
    'prets'        => 'prets',
    'reservations' => 'reservations',
    'lecteurs'     => 'lecteurs',
    'etudiants'    => 'etudiants',
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
    'import'       => 'import',
    'mails'        => 'mails',
    'reglages'     => 'reglages',
    'roles'        => 'roles',
    'systeme'      => 'systeme',
    'rapports'     => 'rapports',
    'audit'        => 'audit',
    'actualites'   => 'actualites',
    'mon-compte'   => 'connexion',
];

if (!isset($controleurs[$section])) {
    admin_introuvable('Page');
}

// Droit minimal pour ouvrir chaque section ; chaque contrôleur vérifie ensuite l'action demandée
$acces = [
    'tableau' => ['tableau.voir'], 'guichet' => ['guichet.voir'], 'prets' => ['prets.voir', 'demandes.voir'], 'reservations' => ['reservations.voir'],
    'lecteurs' => ['lecteurs.voir'], 'ouvrages' => ['ouvrages.voir'], 'exemplaires' => ['ouvrages.voir'],
    'auteurs' => ['auteurs.voir'], 'domaines' => ['domaines.voir'], 'types' => ['types.voir'],
    'slides' => ['slides.voir'], 'citations' => ['citations.voir'], 'blocs' => ['blocs.voir'], 'messages' => ['messages.voir'],
    'import' => ['import.auteurs', 'import.ouvrages', 'import.etudiants'], 'etudiants' => ['etudiants.voir'], 'mails' => ['mails.voir'], 'reglages' => ['reglages.voir'],
    'personnel' => ['personnel.voir'], 'roles' => ['roles.voir'], 'rapports' => ['rapports.voir'],
    'audit' => ['audit.voir'], 'actualites' => ['actualites.voir'],
];
// Page réservée au compte Système : pour les autres, elle n'existe pas
if ($section === 'systeme' && !is_systeme()) {
    admin_introuvable('Page');
}
if ($section === '' && !peut('tableau.voir')) {
    $page = premiere_page_autorisee();
    if ($page === null) {
        admin_page('message', ['message' => 'Votre compte n’a encore aucun droit dans l’administration. Demandez à un administrateur de vous attribuer un rôle.'], 'Aucun accès', '');
    }
    redirect(admin_url($page));
}
if ($section === 'auteurs' && $action === 'chercher') {
    exiger('auteurs.voir', 'ouvrages.ajouter', 'ouvrages.modifier'); // suggestions d'auteurs dans la fiche ouvrage
} elseif ($section === 'lecteurs' && $action === 'rapide') {
    exiger('lecteurs.ajouter');
} elseif ($section === 'guichet' && in_array($action, ['pret', 'retour', 'exemplaires'], true)) {
    // Actions du guichet appelées aussi depuis d'autres pages (Remettre, Retour) : le contrôleur vérifie le droit précis
} elseif (isset($acces[$section])) {
    exiger(...$acces[$section]);
}
require ROOT_DIR . '/app/admin/' . $controleurs[$section] . '.php';
