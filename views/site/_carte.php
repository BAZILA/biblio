<?php
/**
 * Carte d'un ouvrage : même gabarit partout (types, auteurs, catalogue, domaines)
 * pour que toutes les cartes aient la même hauteur et des contenus alignés.
 * @var array $o
 */
$dispo = isset($o['nb_dispo'], $o['nb_ex']) ? disponibilite($o) : '';
?>
				<div class="biblio-card">
					<a href="<?= e($o['url']) ?>" class="biblio-card-cover">
						<img src="<?= e(couverture($o['image'])) ?>" alt="Couverture : <?= e($o['titre']) ?>" loading="lazy">
					</a>
					<div class="biblio-card-body">
						<a href="<?= e(url('domaines/' . $o['domaine_slug'])) ?>" class="biblio-card-domain"><i class="far fa-folder-open"></i> <?= e($o['domaine_nom']) ?></a>
						<h3 title="<?= e($o['titre']) ?>"><a href="<?= e($o['url']) ?>"><?= e($o['titre']) ?></a></h3>
						<p class="biblio-card-authors" title="<?= e($o['auteurs_txt']) ?>"><i class="fas fa-user"></i> <?= $o['auteurs_txt'] !== '' ? e($o['auteurs_txt']) : 'Auteur non renseigné' ?></p>
						<p class="biblio-card-badges">
<?php if (($o['type_nom'] ?? '') !== ''): ?>
							<span class="biblio-badge"><?= e($o['type_nom']) ?></span>
<?php endif; ?>
<?php if ($dispo !== ''): ?>
							<span class="biblio-badge biblio-dispo-<?= slugify($dispo) ?>"><?= e($dispo) ?></span>
<?php endif; ?>
						</p>
						<div class="biblio-card-text"><?= e(tronquer($o['resume'] ?? '', 220, '…')) ?></div>
						<a href="<?= e($o['url']) ?>" class="btn btn-primary">Voir la fiche</a>
					</div>
				</div>
