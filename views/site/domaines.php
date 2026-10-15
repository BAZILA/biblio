<?php /** Liste des domaines. @var array $domaines */ ?>
<div class="categories-list">

<h1>
	Ouvrages par domaine</h1>

						<div class="category-desc base-desc">
			<p>Choisissez un domaine pour afficher ses ouvrages.</p>			</div>
<?php foreach ($domaines as $i => $d): ?>
							<div class="category-item<?= $i ? '' : ' first' ?>">
					<h3 class="page-header item-title">
				<a href="<?= e(url('domaines/' . $d['slug'])) ?>">
				<?= e($d['nom']) ?></a>
									<span class="badge badge-info tip hasTooltip" title="Nombre d&#039;ouvrages dans ce domaine">
						Ouvrages :&nbsp;
						<?= (int) $d['nb'] ?>					</span>
											</h3>
<?php if ($d['description']): ?>
															<div class="category-desc">
						<p><?= e($d['description']) ?></p>					</div>
<?php endif; ?>
					</div>
<?php endforeach; ?>
</div>
