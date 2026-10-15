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
			<div class="items-row row row-flex">
<?php foreach ($ouvrages as $o): ?>
				<div class="col-12 col-sm-6 col-md-4">
					<div class="item column-1" itemprop="blogPost" itemscope itemtype="https://schema.org/BlogPosting">

	<!-- Article -->
	<article>
<header class="article-header clearfix">
	<h2 class="article-title" itemprop="headline">
					<a href="<?= e($o['url']) ?>" itemprop="url" title="<?= e($o['titre']) ?>">
				<?= e($o['titre']) ?></a>
			</h2>
			</header>

        <aside class="article-aside clearfix">
            	<dl class="article-info  muted">
			<dt class="article-info-term">
													Détails							</dt>
<?php if ($o['auteurs_txt'] !== ''): ?>
<dd class="createdby hasTooltip" itemprop="author" title="Auteur(s) : ">
	<i class="fa fa-user"></i>
			<span itemprop="name"><?= e($o['auteurs_txt']) ?></span>
</dd>
<?php endif; ?>
										<dd class="category-name hasTooltip" title="Domaine : ">
				<i class="fa fa-folder-open"></i>
									<a href="<?= e(url('domaines/' . $o['domaine_slug'])) ?>" ><span itemprop="genre"><?= e($o['domaine_nom']) ?></span></a>							</dd>
						</dl>
          </aside>

		<section class="article-intro clearfix">
<?= render(__DIR__ . '/_champs.php', ['o' => $o]) ?>
<?php if ($o['image']): ?>
			    <div class="pull-left item-image">
      <a href="<?= e($o['url']) ?>"><img
        src="<?= e(media($o['image'])) ?>" alt="Couverture : <?= e($o['titre']) ?>" itemprop="thumbnailUrl"/></a>
    </div>
<?php endif; ?>

			<?= $o['resume'] ?>		</section>
	</article>
	<!-- //Article -->

					</div><!-- end item -->
									</div><!-- end span -->
<?php endforeach; ?>
				</div>
<?php endif; ?>

<?= render(__DIR__ . '/_pagination.php', ['pagination' => $pagination, 'chemin' => $chemin]) ?>
	</div>
