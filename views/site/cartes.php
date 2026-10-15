<?php
/**
 * Ouvrages d'un type ou d'un auteur, en cartes.
 * @var string $titre
 * @var string $description
 * @var array  $ouvrages
 */
?>
<div class="com-tags-tag tag-category biblio-tag">
	<h1><?= e($titre) ?></h1>

<?php if ($description !== ''): ?>
				<div class="category-desc"><p><?= e($description) ?></p></div>
<?php endif; ?>

			<p class="biblio-tag-count"><?= e(pluriel(count($ouvrages), 'ouvrage')) ?></p>
		<div class="biblio-cards">
<?php foreach ($ouvrages as $o): ?>
															<div class="biblio-card">
<?php if ($o['image']): ?>
											<a href="<?= e($o['url']) ?>" class="biblio-card-cover">
							<img src="<?= e(media($o['image'])) ?>" alt="Couverture : <?= e($o['titre']) ?>" loading="lazy">
						</a>
<?php endif; ?>
										<div class="biblio-card-body">
													<span class="biblio-card-domain"><i class="far fa-folder-open"></i> <?= e($o['domaine_nom']) ?></span>
												<h3><a href="<?= e($o['url']) ?>"><?= e($o['titre']) ?></a></h3>
<?php if ($o['auteurs_txt'] !== ''): ?>
													<p class="biblio-card-authors"><i class="fas fa-user"></i> <?= e($o['auteurs_txt']) ?></p>
<?php endif; ?>
												<div class="biblio-card-text"><?= e(tronquer($o['resume'], 400, '…')) ?></div>
						<a href="<?= e($o['url']) ?>" class="btn btn-primary">Voir la fiche</a>
					</div>
				</div>
<?php endforeach; ?>
					</div>

		</div>
