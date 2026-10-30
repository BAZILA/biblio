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
<?= render(__DIR__ . '/_carte.php', ['o' => $o]) ?>
<?php endforeach; ?>
		</div>

		</div>
