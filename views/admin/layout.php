<?php
/**
 * Gabarit de l'administration.
 * @var string $titre
 * @var string $contenu
 * @var string $section  Entrée de menu active
 */
$u = user();
$compteurs = [
    'retards'      => (int) val('SELECT COUNT(*) FROM prets WHERE date_retour IS NULL AND date_retour_prevue < CURDATE()'),
    'reservations' => (int) val("SELECT COUNT(*) FROM reservations WHERE statut IN ('en_attente','disponible')"),
    'inscriptions' => (int) val("SELECT COUNT(*) FROM utilisateurs WHERE role = 'lecteur' AND en_attente = 1"),
    'messages'     => (int) val('SELECT COUNT(*) FROM messages WHERE lu = 0'),
];
$menu = [
    'Bibliothèque' => [
        ['tableau', '', 'fa-gauge', 'Tableau de bord', null],
        ['guichet', 'guichet', 'fa-right-left', 'Guichet prêt / retour', null],
        ['prets', 'prets', 'fa-book-open', 'Prêts', $compteurs['retards'] ? [$compteurs['retards'], 'rouge'] : null],
        ['reservations', 'reservations', 'fa-bookmark', 'Réservations', $compteurs['reservations'] ? [$compteurs['reservations'], ''] : null],
        ['lecteurs', 'lecteurs', 'fa-users', 'Lecteurs', $compteurs['inscriptions'] ? [$compteurs['inscriptions'], ''] : null],
    ],
    'Catalogue' => [
        ['ouvrages', 'ouvrages', 'fa-book', 'Ouvrages', null],
        ['auteurs', 'auteurs', 'fa-feather', 'Auteurs', null],
        ['domaines', 'domaines', 'fa-layer-group', 'Domaines', null],
        ['types', 'types', 'fa-tags', 'Types de documents', null],
    ],
    'Site' => array_merge(
        [
            ['slides', 'slides', 'fa-images', 'Diaporama d’accueil', null],
            ['citations', 'citations', 'fa-quote-left', 'Citations', null],
        ],
        is_admin() ? [['blocs', 'blocs', 'fa-pen-to-square', 'Textes du site', null]] : [],
        [['messages', 'messages', 'fa-envelope', 'Messages', $compteurs['messages'] ? [$compteurs['messages'], ''] : null]]
    ),
    'Configuration' => array_merge(
        [['reglages', 'reglages', 'fa-sliders', 'Réglages', null]],
        is_admin() ? [['personnel', 'personnel', 'fa-user-shield', 'Comptes du personnel', null]] : []
    ),
];
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
		<div class="adm-marque">
			<img src="<?= e(media('images/biblio/uk-sigle.png')) ?>" alt="">
			<div>Bibliothèque<small><?= e(reglage('site_nom')) ?></small></div>
		</div>
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
			<form class="recherche-rapide" action="<?= e(admin_url('ouvrages')) ?>" method="get" role="search">
				<input type="search" name="q" placeholder="Rechercher un ouvrage, une cote…" aria-label="Rechercher un ouvrage">
			</form>
			<div class="compte">
				<a href="<?= e(admin_url('mon-compte')) ?>" title="Mon compte"><i class="fa-solid fa-circle-user"></i> <span class="nom"><?= e(nom_complet($u)) ?></span></a>
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
<script src="<?= asset('admin/admin.js') ?>"></script>
</body>
</html>
