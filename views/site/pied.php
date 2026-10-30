<?php
/** Pied de page commun : présentation, liens utiles, domaines, derniers ajouts, horaires. */
$liensUtiles = [
    ['catalogue', 'Tout le catalogue'],
    ['domaines', 'Ouvrages par domaine'],
    ['types', 'Ouvrages par type'],
    ['auteurs', 'Index des auteurs'],
    ['contact', 'Nous contacter'],
];
$derniers = rows('SELECT o.titre, o.slug, d.slug AS domaine_slug FROM ouvrages o JOIN domaines d ON d.id = o.domaine_id
                  WHERE o.publie = 1 ORDER BY o.cree_le DESC, o.id DESC LIMIT 5');
?>
<!-- FOOTER -->
<footer id="t3-footer" class="wrap t3-footer">
		<div class="t3-footer-overlay"></div>
	<div class="t3-spotlight t3-footnav  row">
					<div class=" col-lg-3 col-md-2 col-sm-12 col-xs-12">
								<div class="t3-module module "><div class="module-inner"><div class="module-ct">
<div class="mod-custom custom">
    <?= bloc('pied_infos') ?></div>
</div></div></div>
							</div>
					<div class=" col-lg-2 col-md-2 col-sm-6 col-xs-12">
								<div class="t3-module module_menu "><div class="module-inner"><h3 class="module-title bicolor"><span>Liens utiles</span></h3><div class="module-ct"><ul class="nav nav-pills nav-stacked menu">
<?php foreach ($liensUtiles as [$chemin, $libelle]): ?><li><a href="<?= e(url($chemin)) ?>" class=""><?= e($libelle) ?> </a></li><?php endforeach; ?></ul>
</div></div></div>
							</div>
					<div class=" col-lg-2 col-md-2 col-sm-6 col-xs-12">
								<div class="t3-module module "><div class="module-inner"><h3 class="module-title bicolor"><span>Domaines</span></h3><div class="module-ct"><div class="exeltis articles_count">
<ul>
<?php foreach (domaines() as $d): ?>
<li><i class="fa fa-folder-open-o" aria-hidden="true"></i>
<a href="<?= e(url('domaines/' . $d['slug'])) ?>"><?= e($d['nom']) ?><span class="artcount"> (<?= (int) $d['nb'] ?>)</span></a></li>
<?php endforeach; ?>
</ul>
</div></div></div></div>
							</div>
					<div class=" col-lg-2 col-md-2 col-sm-6 col-xs-12">
								<div class="t3-module module "><div class="module-inner"><h3 class="module-title bicolor"><span>Derniers ajouts</span></h3><div class="module-ct"><ul class="mod-articleslatest latestnews mod-list">
<?php foreach ($derniers as $o): ?>
    <li itemscope itemtype="https://schema.org/Article">
        <a href="<?= e(ouvrage_url($o)) ?>" itemprop="url">
            <span itemprop="name">
                <?= e($o['titre']) ?>
            </span>
        </a>
    </li>
<?php endforeach; ?>
</ul>
</div></div></div>
							</div>
					<div class=" col-lg-3 col-md-4 col-sm-6 col-xs-12">
								<div class="t3-module module "><div class="module-inner"><h3 class="module-title bicolor"><span>Horaires d’ouverture</span></h3><div class="module-ct">
<div class="mod-custom custom">
    <?= bloc('pied_horaires') ?></div>
</div></div></div>
							</div>
			</div>
		<section class="t3-copyright">
				<div class="col-xs-12 col-sm-6 col-md-6 copyright ">
<div class="mod-custom custom">
    <?= bloc('pied_copyright') ?></div>
				</div>
				<div class="col-xs-12 col-sm-6 col-md-6 footerbase-2 ">
<ul class="nav nav-pills nav-stacked menu nav-pills">
<?php foreach ($liensUtiles as [$chemin, $libelle]): ?><li><a href="<?= e(url($chemin)) ?>" class=""><?= e($libelle) ?> </a></li><?php endforeach; ?></ul>
				</div>
		</section>
</footer>

    <div id="back-to-top" data-spy="affix" data-offset-top="300" class="back-to-top hidden-xs hidden-sm affix-top">
      <button class="btn btn-primary" title="Haut de page"><i class="fa fa-angle-up"></i></button>
    </div>
    <script type="text/javascript">
    (function($) {
    	$('#back-to-top').on('click', function(){
    		$("html, body").animate({scrollTop: 0}, 500);
    		return false;
    	});
    })(jQuery);
    </script>
<!-- //FOOTER -->
