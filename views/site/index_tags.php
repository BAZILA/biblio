<?php
/**
 * Index en vignettes (types de documents ou auteurs), comme sur le site d'origine.
 * @var string $titre, $intro
 * @var int    $parLigne  Vignettes par ligne (3 pour les types, 4 pour les auteurs)
 * @var array  $elements  [['url' => ..., 'nom' => ..., 'texte' => HTML]]
 * @var string|null $filtre  Valeur du filtre (index des auteurs), null si pas de filtre
 */
?>
<div class="tag-category">
			<h1>
		<?= e($titre) ?>		</h1>
					<div><p><?= e($intro) ?></p></div>

<?php if ($filtre !== null): ?>
<form action="" method="get" name="adminForm" id="adminForm">
		<fieldset class="filters btn-toolbar">
					<div class="btn-group">
				<label class="filter-search-lbl element-invisible" for="filter-search">
					Rechercher un nom&#160;				</label>
				<input type="text" name="filtre" id="filter-search" value="<?= e($filtre) ?>" class="inputbox" placeholder="Rechercher un nom" />
				<button type="submit" title="Rechercher" class="btn">
					<span class="fa fa-search"></span>
				</button>
				<button type="button" title="Effacer" class="btn" onclick="window.location.search = '';">
					<span class="fa fa-remove"></span>
				</button>
			</div>
		<div class="clearfix"></div>
	</fieldset>
</form>
<?php endif; ?>

<?php if (!$elements): ?>
	<p class="biblio-vide">Aucun résultat.</p>
<?php endif; ?>
<?php $rang = 0; foreach (array_chunk($elements, $parLigne) as $ligne): ?>
						<ul class="thumbnails">
<?php foreach ($ligne as $el): ?>
				 			<li class="cat-list-row<?= $rang++ % 2 ?>" >
				<h3>
					<a href="<?= e($el['url']) ?>">
						<?= e($el['nom']) ?>					</a>
				</h3>
						<div class="caption">
							<span class="tag-body">
					<?= $el['texte'] ?>				</span>
								</div>
	</li>
<?php endforeach; ?>
					</ul>
<?php endforeach; ?>
</div>
