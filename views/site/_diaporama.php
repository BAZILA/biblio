<?php
/**
 * Diaporama de l'accueil : diapositive de bienvenue, puis actualités et évènements « au diaporama ».
 * Fond image (effet zoom, panoramique…) ou vidéo en boucle ; textes animés ; navigation clavier, tactile et pause.
 * @var array $slides  voir diapositives_accueil()
 */
$nb = count($slides);
if (!$nb) {
    return;
}
$duree = max(4, reglage_int('diaporama_duree', 7)) * 1000;
?>
<section class="vit-diapo" aria-roledescription="carrousel" aria-label="À la une" data-duree="<?= $duree ?>" tabindex="-1">
	<div class="vit-diapo-piste" aria-live="off">
<?php foreach ($slides as $i => $s): $lignes = explode("\n", str_replace("\r", '', trim($s['titre']))); ?>
		<div class="vit-diapo-slide<?= $i === 0 ? ' est-active' : '' ?>" data-animation="<?= e(isset(ANIMATIONS_DIAPO[$s['animation']]) ? $s['animation'] : 'zoom') ?>"
			role="group" aria-roledescription="diapositive" aria-label="<?= $i + 1 ?> sur <?= $nb ?>"<?= $i ? ' aria-hidden="true"' : '' ?>>
			<div class="vit-diapo-fond">
<?php if ($s['video'] !== ''): ?>
				<video muted loop playsinline preload="<?= $i === 0 ? 'auto' : 'none' ?>"<?= $i === 0 ? ' autoplay' : '' ?> poster="<?= e(media($s['image'])) ?>" aria-hidden="true">
					<source src="<?= e(media($s['video'])) ?>" type="<?= type_video($s['video']) ?>">
				</video>
<?php elseif ($s['image'] !== ''): ?>
				<img src="<?= e(media($s['image'])) ?>" alt=""<?= $i === 0 ? ' fetchpriority="high"' : ' loading="lazy"' ?>>
<?php endif; ?>
			</div>
			<div class="vit-diapo-voile"></div>
			<div class="container vit-diapo-contenu">
<?php if ($s['etiquette'] !== ''): ?>
				<span class="vit-diapo-etiquette"><?= e($s['etiquette']) ?></span>
<?php endif; ?>
				<<?= $i === 0 ? 'h1' : 'h2' ?> class="vit-diapo-titre"><?= implode('<br>', array_map('e', $lignes)) ?></<?= $i === 0 ? 'h1' : 'h2' ?>>
<?php if ($s['texte'] !== ''): ?>
				<p class="vit-diapo-texte"><?= e($s['texte']) ?></p>
<?php endif; ?>
<?php if ($s['boutons']): ?>
				<div class="vit-diapo-boutons">
<?php foreach ($s['boutons'] as [$libelle, $lien, $principal]): ?>
					<a class="vit-btn <?= $principal ? 'vit-btn-or' : 'vit-btn-contour' ?>" href="<?= e($lien) ?>"<?= $i ? ' tabindex="-1"' : '' ?>><?= e($libelle) ?><?= $principal ? ' <span aria-hidden="true">→</span>' : '' ?></a>
<?php endforeach; ?>
				</div>
<?php endif; ?>
			</div>
		</div>
<?php endforeach; ?>
	</div>
<?php if ($nb > 1): ?>
	<button type="button" class="vit-diapo-fleche vit-diapo-prec" aria-label="Diapositive précédente"><i class="fa fa-angle-left" aria-hidden="true"></i></button>
	<button type="button" class="vit-diapo-fleche vit-diapo-suiv" aria-label="Diapositive suivante"><i class="fa fa-angle-right" aria-hidden="true"></i></button>
	<div class="vit-diapo-nav">
		<span class="vit-diapo-compteur" aria-hidden="true"><strong>01</strong> / <?= sprintf('%02d', $nb) ?></span>
		<div class="vit-diapo-points" role="tablist" aria-label="Choisir une diapositive">
<?php for ($i = 0; $i < $nb; $i++): ?>
			<button type="button" role="tab" class="vit-diapo-point<?= $i === 0 ? ' est-active' : '' ?>" aria-label="Diapositive <?= $i + 1 ?>" aria-selected="<?= $i === 0 ? 'true' : 'false' ?>"><span></span></button>
<?php endfor; ?>
		</div>
		<button type="button" class="vit-diapo-pause" aria-label="Mettre en pause le défilement" aria-pressed="false"><i class="fa fa-pause" aria-hidden="true"></i></button>
	</div>
<?php endif; ?>
	<div class="vit-diapo-liseret" aria-hidden="true"><span></span><span></span><span></span></div>
</section>
