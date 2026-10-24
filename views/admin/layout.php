<?php
/**
 * Gabarit de l'administration.
 * @var string $titre
 * @var string $contenu
 * @var string $section  Entrée de menu active
 */
$u = user();
// Compteurs des pastilles, calculés seulement pour les rubriques visibles
$compte = fn(string $perm, string $sql) => peut($perm) ? (int) val($sql) : 0;
$compteurs = [
    'retards'      => $compte('prets.voir', 'SELECT COUNT(*) FROM prets WHERE date_retour IS NULL AND date_retour_prevue < CURDATE()'),
    'demandes'     => $compte('demandes.voir', "SELECT COUNT(*) FROM demandes_prolongation WHERE statut = 'en_attente'"),
    'reservations' => $compte('reservations.voir', "SELECT COUNT(*) FROM reservations WHERE statut IN ('en_attente','disponible')"),
    'inscriptions' => $compte('lecteurs.voir', "SELECT COUNT(*) FROM utilisateurs WHERE role = 'lecteur' AND en_attente = 1"),
    'messages'     => $compte('messages.voir', 'SELECT COUNT(*) FROM messages WHERE lu = 0'),
];
$pastille = fn(string $cle, string $classe = '') => $compteurs[$cle] ? [$compteurs[$cle], $classe] : null;
// [clé active, chemin, icône, libellé, pastille, permission(s) requise(s)] : une entrée n'apparaît que si le rôle le permet
$menu = [
    'Bibliothèque' => [
        ['tableau', '', 'fa-gauge', 'Tableau de bord', null, ['tableau.voir']],
        ['guichet', 'guichet', 'fa-right-left', 'Guichet prêt / retour', null, ['guichet.voir']],
        ['prets', 'prets', 'fa-book-open', 'Prêts', $pastille('retards', 'rouge'), ['prets.voir']],
        ['demandes', 'prets/demandes', 'fa-calendar-plus', 'Demandes de prolongation', $pastille('demandes'), ['demandes.voir']],
        ['reservations', 'reservations', 'fa-bookmark', 'Réservations', $pastille('reservations'), ['reservations.voir']],
        ['lecteurs', 'lecteurs', 'fa-users', 'Lecteurs', $pastille('inscriptions'), ['lecteurs.voir']],
        ['etudiants', 'etudiants', 'fa-id-card', 'Étudiants de l’université', null, ['etudiants.voir']],
        ['rapports', 'rapports', 'fa-chart-column', 'Statistiques et rapports', null, ['rapports.voir']],
    ],
    'Catalogue' => [
        ['ouvrages', 'ouvrages', 'fa-book', 'Ouvrages', null, ['ouvrages.voir']],
        ['auteurs', 'auteurs', 'fa-feather', 'Auteurs', null, ['auteurs.voir']],
        ['domaines', 'domaines', 'fa-layer-group', 'Domaines', null, ['domaines.voir']],
        ['types', 'types', 'fa-tags', 'Types de documents', null, ['types.voir']],
        ['import', 'import', 'fa-file-import', 'Importer un fichier', null, ['import.auteurs', 'import.ouvrages', 'import.etudiants']],
    ],
    'Site' => [
        ['actualites', 'actualites', 'fa-newspaper', 'Actualités et évènements', null, ['actualites.voir']],
        ['slides', 'slides', 'fa-images', 'Diaporama d’accueil', null, ['slides.voir']],
        ['citations', 'citations', 'fa-quote-left', 'Citations', null, ['citations.voir']],
        ['blocs', 'blocs', 'fa-pen-to-square', 'Textes du site', null, ['blocs.voir']],
        ['messages', 'messages', 'fa-envelope', 'Messages', $pastille('messages'), ['messages.voir']],
    ],
    'Configuration' => [
        ['reglages', 'reglages', 'fa-sliders', 'Réglages', null, ['reglages.voir']],
        ['mails', 'mails', 'fa-paper-plane', 'E-mails envoyés', null, ['mails.voir']],
        ['personnel', 'personnel', 'fa-user-shield', 'Comptes du personnel', null, ['personnel.voir']],
        ['roles', 'roles', 'fa-user-lock', 'Rôles et permissions', null, ['roles.voir']],
        ['audit', 'audit', 'fa-clipboard-list', 'Journal d’audit', null, ['audit.voir']],
        ['systeme', 'systeme', 'fa-microchip', 'Système', null, ['systeme.voir']], // seul le compte Système a ce droit
    ],
];
foreach ($menu as $groupe => $entrees) {
    $menu[$groupe] = array_filter($entrees, fn($e) => peut(...$e[5]));
}
$menu = array_filter($menu);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<meta name="robots" content="noindex, nofollow">
	<title><?= e($titre) ?> — Administration</title>
	<link rel="icon" href="<?= e(media('images/biblio/favicon.png')) ?>">
	<link rel="stylesheet" href="<?= asset('vendor/fontawesome/css/fontawesome.min.css') ?>">
	<link rel="stylesheet" href="<?= asset('admin/admin.css') ?>">
</head>
<body>
<div class="adm">
	<nav class="adm-nav" aria-label="Menu de l’administration">
		<a class="adm-marque" href="<?= e(admin_url()) ?>">
			<img src="<?= e(media('images/biblio/logo-admin.png')) ?>" alt="<?= e(reglage('site_nom')) ?>">
			<small>Bibliothèque · Administration</small>
		</a>
<?php foreach ($menu as $groupe => $entrees): ?>
		<h4><?= e($groupe) ?></h4>
	<?php foreach ($entrees as [$cle, $chemin, $icone, $libelle, $pastille]): ?>
		<a href="<?= e(admin_url($chemin)) ?>" class="<?= ($section ?? '') === $cle ? 'actif' : '' ?>"><i class="fa-solid <?= $icone ?>"></i><?= e($libelle) ?><?php if ($pastille): ?><span class="pastille <?= $pastille[1] ?>"><?= $pastille[0] ?></span><?php endif; ?></a>
	<?php endforeach; ?>
<?php endforeach; ?>
		<h4>Raccourcis</h4>
		<a href="<?= e(url()) ?>" target="_blank" rel="noopener"><i class="fa-solid fa-house"></i>Voir le site</a>
	</nav>

	<div class="adm-corps">
		<header class="adm-barre">
			<button class="adm-burger" type="button" aria-label="Menu"><i class="fa-solid fa-bars"></i></button>
<?php if (peut('ouvrages.voir')): ?>
			<form class="recherche-rapide" action="<?= e(admin_url('ouvrages')) ?>" method="get" role="search">
				<input type="search" name="q" placeholder="Rechercher un ouvrage, une cote…" aria-label="Rechercher un ouvrage">
			</form>
<?php else: ?>
			<div class="recherche-rapide"></div>
<?php endif; ?>
			<div class="compte">
				<a href="<?= e(admin_url('mon-compte')) ?>" title="Mon compte"><i class="fa-solid fa-circle-user"></i> <span class="nom"><?= e(nom_complet($u)) ?></span><?php if ($roleMoi = role_de()): ?> <small class="role-moi"><?= e($roleMoi['nom']) ?></small><?php endif; ?></a>
				<form method="post" action="<?= e(admin_url('deconnexion')) ?>"><?= csrf_field() ?><button class="btn secondaire petit" type="submit"><i class="fa-solid fa-arrow-right-from-bracket"></i> Quitter</button></form>
			</div>
		</header>

		<main class="adm-contenu">
<?php foreach (flashes() as $f): ?>
			<div class="flash <?= e($f['type']) ?>"><?= e($f['message']) ?></div>
<?php endforeach; ?>
<?= $contenu ?>
		</main>
	</div>
</div>
<?= render(ROOT_DIR . '/views/admin/_modales.php') ?>
<script src="<?= asset('admin/admin.js') ?>"></script>
</body>
</html>
