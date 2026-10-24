<?php
/** Actualités et évènements. @var string $type @var array $liste, $aVenir, $pagination */
$filtres = ['' => 'Tout', 'actualite' => 'Actualités', 'evenement' => 'Évènements'];
?>
<div class="vit-actus">
	<header class="vit-bandeau">
		<span class="vit-surtitre"><i class="fa fa-newspaper-o" aria-hidden="true"></i> La vie de la bibliothèque</span>
		<h1>Actualités et évènements</h1>
		<p>Nouveautés, animations, conférences, expositions et informations pratiques de la bibliothèque de l’Université de Kara.</p>
		<nav class="vit-filtres" aria-label="Filtrer">
<?php foreach ($filtres as $cle => $lib): ?>
			<a href="<?= e(url('actualites', $cle !== '' ? ['type' => $cle] : [])) ?>" class="<?= $type === $cle ? 'actif' : '' ?>"<?= $type === $cle ? ' aria-current="page"' : '' ?>><?= e($lib) ?></a>
<?php endforeach; ?>
		</nav>
	</header>

<?php if ($aVenir): ?>
	<section class="vit-agenda" aria-labelledby="agenda-titre">
		<h2 id="agenda-titre"><i class="fa fa-calendar" aria-hidden="true"></i> À venir</h2>
		<div class="vit-agenda-liste">
<?php foreach ($aVenir as $ev): $t = strtotime($ev['debut'] ?? $ev['date_publication']); ?>
			<a class="vit-agenda-item" href="<?= e(actualite_url($ev)) ?>">
				<span class="vit-date"><strong><?= date('j', $t) ?></strong><?= e(MOIS_COURTS[(int) date('n', $t)]) ?></span>
				<span class="vit-agenda-texte"><strong><?= e($ev['titre']) ?></strong>
					<small><?= e(date_evenement($ev)) ?><?= $ev['lieu'] !== '' ? ' · ' . e($ev['lieu']) : '' ?></small></span>
			</a>
<?php endforeach; ?>
		</div>
	</section>
<?php endif; ?>

<?php if (!$liste): ?>
	<p class="vit-vide">Aucune <?= $type === 'evenement' ? 'évènement' : 'actualité' ?> pour le moment. Revenez bientôt !</p>
<?php else: ?>
	<div class="vit-grille">
<?php foreach ($liste as $i => $a): ?>
		<article class="vit-carte<?= $i === 0 && $pagination['page'] === 1 && $type === '' ? ' vit-carte-une' : '' ?>">
			<a class="vit-carte-image" href="<?= e(actualite_url($a)) ?>" tabindex="-1" aria-hidden="true">
<?php if ($a['image']): ?>
				<img src="<?= e(media($a['image'])) ?>" alt="" loading="lazy">
<?php else: ?>
				<span class="vit-carte-sans-image"><i class="fa <?= $a['type'] === 'evenement' ? 'fa-calendar' : 'fa-newspaper-o' ?>" aria-hidden="true"></i></span>
<?php endif; ?>
				<span class="vit-badge vit-badge-<?= e($a['type']) ?>"><?= e(TYPES_ACTUALITE[$a['type']]) ?></span>
			</a>
			<div class="vit-carte-corps">
				<p class="vit-meta"><i class="fa <?= $a['type'] === 'evenement' ? 'fa-clock-o' : 'fa-calendar-o' ?>" aria-hidden="true"></i>
					<?= e($a['type'] === 'evenement' && $a['debut'] ? date_evenement($a) : date_courte($a['date_publication'])) ?><?= $a['lieu'] !== '' ? ' · ' . e($a['lieu']) : '' ?></p>
				<h2><a href="<?= e(actualite_url($a)) ?>"><?= e($a['titre']) ?></a></h2>
				<p><?= e($a['chapo']) ?></p>
				<a class="vit-lien-suite" href="<?= e(actualite_url($a)) ?>">Lire la suite <span aria-hidden="true">→</span><span class="sr-only"> : <?= e($a['titre']) ?></span></a>
			</div>
		</article>
<?php endforeach; ?>
	</div>
	<?= render(ROOT_DIR . '/views/site/_pagination.php', ['pagination' => $pagination, 'chemin' => 'actualites']) ?>
<?php endif; ?>
</div>
