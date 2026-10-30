<?php
/**
 * Liste d'ouvrages en grille (catalogue complet ou domaine).
 * @var string     $titre
 * @var array|null $domaine   Domaine affiché (null pour le catalogue complet)
 * @var array      $ouvrages
 * @var array      $pagination
 * @var string     $chemin    Chemin de la page pour les liens de pagination
 */
?>
<div class="com-content-category-blog blog" itemscope itemtype="https://schema.org/Blog">
<?php if ($domaine): ?>
	<div class="page-subheader clearfix">
		<h1 class="page-subtitle">						<small class="subheading-category"><?= e($domaine['nom']) ?></small>
		</h1>
	</div>
		<div class="category-desc clearfix">
			<p><?= e($domaine['description']) ?></p>
		</div>
<?php else: ?>
		<div class="page-header clearfix">
		<h1 class="page-title"> Catalogue des ouvrages </h1>
	</div>
		<div class="category-desc clearfix">
			<p>Retrouvez l’ensemble des ouvrages de la bibliothèque, classés par domaine. Chaque fiche précise les auteurs, le type de document, l’éditeur et la disponibilité.</p><?= formulaire_recherche() ?>
		</div>
<?php endif; ?>

<?php if (!$ouvrages): ?>
		<p class="biblio-vide">Aucun ouvrage pour le moment.</p>
<?php else: ?>
		<p class="biblio-tag-count"><?= e(pluriel($pagination['total'] ?? count($ouvrages), 'ouvrage')) ?></p>
		<div class="biblio-cards">
<?php foreach ($ouvrages as $o): ?>
<?= render(__DIR__ . '/_carte.php', ['o' => $o]) ?>
<?php endforeach; ?>
		</div>
<?php endif; ?>

<?= render(__DIR__ . '/_pagination.php', ['pagination' => $pagination, 'chemin' => $chemin]) ?>
	</div>
