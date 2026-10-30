<?php
/** Page d'une actualité ou d'un évènement. @var array $a, $autres */
$evenement = $a['type'] === 'evenement';
?>
<article class="vit-article">
	<header class="vit-article-tete<?= $a['image'] ? '' : ' sans-image' ?>"<?= $a['image'] ? ' style="--fond:url(\'' . e(media($a['image'])) . '\')"' : '' ?>>
		<div class="vit-article-voile">
			<nav class="vit-fil" aria-label="Fil d’Ariane"><a href="<?= e(url()) ?>">Accueil</a> <span aria-hidden="true">›</span>
				<a href="<?= e(url('actualites')) ?>">Actualités et évènements</a></nav>
			<span class="vit-badge vit-badge-<?= e($a['type']) ?>"><?= e(TYPES_ACTUALITE[$a['type']]) ?></span>
			<h1><?= e($a['titre']) ?></h1>
			<p class="vit-chapo"><?= e($a['chapo']) ?></p>
		</div>
	</header>

	<div class="vit-article-grille">
		<div class="vit-article-texte">
			<?= $a['contenu'] ?: '<p>' . e($a['chapo']) . '</p>' ?>
			<p class="vit-retour"><a class="btn btn-primary" href="<?= e(url('actualites')) ?>"><i class="fa fa-arrow-left" aria-hidden="true"></i> Toutes les actualités</a></p>
		</div>
		<aside class="vit-article-infos">
<?php if ($evenement && $a['debut']): $t = strtotime($a['debut']); ?>
			<div class="vit-info-evenement">
				<span class="vit-date vit-date-grande"><strong><?= date('j', $t) ?></strong><?= e(MOIS_COURTS[(int) date('n', $t)]) ?> <?= date('Y', $t) ?></span>
				<dl>
					<dt><i class="fa fa-clock-o" aria-hidden="true"></i> Quand</dt><dd><?= e(ucfirst(date_evenement($a))) ?></dd>
<?php if ($a['lieu'] !== ''): ?>
					<dt><i class="fa fa-map-marker" aria-hidden="true"></i> Où</dt><dd><?= e($a['lieu']) ?></dd>
<?php endif; ?>
				</dl>
			</div>
<?php endif; ?>
			<p class="vit-meta">Publié le <?= e(date_courte($a['date_publication'])) ?></p>
<?php if ($autres): ?>
			<h2>À lire aussi</h2>
			<ul class="vit-autres">
<?php foreach ($autres as $o): ?>
				<li><a href="<?= e(actualite_url($o)) ?>">
					<?php if ($o['image']): ?><img src="<?= e(media($o['image'])) ?>" alt="" loading="lazy"><?php endif; ?>
					<span><small><?= e(TYPES_ACTUALITE[$o['type']]) ?> · <?= e(date_courte($o['date_publication'])) ?></small><?= e($o['titre']) ?></span></a></li>
<?php endforeach; ?>
			</ul>
<?php endif; ?>
		</aside>
	</div>
</article>
