<?php
/**
 * Gabarit commun du site public (structure HTML du thème « exeltis » d'origine).
 *
 * @var string $titre       Titre de la page (<title>)
 * @var string $contenu     HTML de la zone principale
 * @var string $actif       Clé du menu actif (accueil, catalogue, domaines, types, auteurs, recherche, contact, compte)
 * @var string $sousActif   Slug de l'élément de sous-menu actif (domaine ou type)
 * @var bool   $accueil     Page d'accueil : sections pleine largeur au lieu du conteneur principal
 * @var string $classeHtml  Classes additionnelles sur <html>
 * @var array  $cssAvant    Feuilles de style propres à la page, chargées avant celles du thème
 * @var string $tete        HTML additionnel dans <head>
 */
$accueil   = $accueil ?? false;
$actif     = $actif ?? '';
$sousActif = $sousActif ?? '';
$menuDomaines = rows('SELECT nom, slug FROM domaines ORDER BY ordre, nom');
$menuTypes    = rows('SELECT nom_pluriel, slug FROM types ORDER BY ordre, nom');
$lecteur = user();
?>
<!DOCTYPE html>
<html lang="fr-fr" dir="ltr" class='<?= e(trim(($classeHtml ?? '') . ($accueil ? ' home' : ''))) ?> j54 j40 mm-hover'>

<head>
	<meta charset="utf-8">
	<meta name="description" content="<?= e($description ?? reglage('site_description')) ?>">
	<title><?= e($titre) ?></title>
	<link href="<?= e(media('images/biblio/favicon.png')) ?>" rel="icon" type="image/png">
	<link href="<?= e(media('images/biblio/favicon.ico')) ?>" rel="alternate icon" type="image/vnd.microsoft.icon">
	<link href="<?= e(media('images/biblio/uk-sigle.png')) ?>" rel="apple-touch-icon">
<?php foreach ($cssAvant ?? [] as $css): ?>
	<link href="<?= e($css) ?>" rel="stylesheet">
<?php endforeach; ?>
	<link href="<?= asset('theme/css/bootstrap.css') ?>" rel="stylesheet">
	<link href="<?= asset('vendor/fontawesome/css/fontawesome.min.css') ?>" rel="stylesheet">
	<link href="<?= asset('theme/css/template.css') ?>" rel="stylesheet">
	<link href="<?= asset('theme/css/megamenu.css') ?>" rel="stylesheet">
	<link href="<?= asset('theme/fonts/font-awesome/css/font-awesome.min.css') ?>" rel="stylesheet">
	<link href="<?= asset('theme/css/custom.css') ?>" rel="stylesheet">
<?php if ($accueil): ?>
	<link href="<?= asset('vendor/jt-contentslider/css/style.css') ?>" rel="stylesheet">
	<link href="<?= asset('vendor/jt-contentslider/css/lightbox.css') ?>" rel="stylesheet">
	<link href="<?= asset('vendor/jt-testimonial/css/style.css') ?>" rel="stylesheet">
	<link href="<?= asset('vendor/jt-testimonial/css/owl2.carousel.css') ?>" rel="stylesheet">
	<link href="<?= asset('vendor/jt-testimonial/font-awesome.css') ?>" rel="stylesheet">
<?php endif; ?>
	<link href="<?= asset('site/site.css') ?>" rel="stylesheet">
	<script src="<?= asset('vendor/jquery/jquery.min.js') ?>"></script>
	<script>jQuery.noConflict();</script>
	<script src="<?= asset('vendor/t3/bootstrap/js/bootstrap.js') ?>"></script>
	<script src="<?= asset('vendor/t3/js/jquery.tap.min.js') ?>"></script>
	<script src="<?= asset('vendor/t3/js/script.js') ?>"></script>
	<script src="<?= asset('vendor/t3/js/menu.js') ?>"></script>
	<script src="<?= asset('theme/js/script.js') ?>"></script>
	<script src="<?= asset('theme/js/moduletitle.js') ?>"></script>
	<script src="<?= asset('theme/js/count.js') ?>"></script>
	<script src="<?= asset('theme/js/progress.js') ?>"></script>
	<script src="<?= asset('theme/js/skills.js') ?>"></script>
	<script src="<?= asset('vendor/t3/js/nav-collapse.js') ?>"></script>
<?php if ($accueil): ?>
	<script src="<?= asset('vendor/jt-contentslider/js/lightbox-plus-jquery.js') ?>"></script>
	<script src="<?= asset('vendor/jt-contentslider/js/owl.carousel.js') ?>"></script>
	<script src="<?= asset('vendor/jt-testimonial/js/jquery.owl2.carousel.js') ?>"></script>
	<script src="<?= asset('vendor/jt-testimonial/js/theme.js') ?>"></script>
	<script src="<?= asset('site/carrousels.js') ?>"></script>
<?php endif; ?>

	<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
	<meta name="HandheldFriendly" content="true"/>
	<meta name="mobile-web-app-capable" content="yes"/>
	<meta name="apple-mobile-web-app-capable" content="YES"/>

<style type="text/css">
body {font-family:Nunito Sans Regular,Helvetica,Arial, sans-serif!important; font-size:16px}
.bg-pattern {background: url(<?= asset('theme/images/patterns/blank.png') ?>);}
#t3-header{background-image:url(<?= asset('theme/images/patterns/blank.png') ?>); background-repeat:repeat}
.logo{justify-items:flex-start;align-items:center; }

.colormodule.submain h3.module-title,.tp-caption a.btn,.tp-caption a.btn:hover,h1, h2, h3, h4, h5, h6, .h1, .h2, .h3, .h4, .h5, .h6, h1 a, h2 a, h3 a, h4 a, h5 a, h6 a, .h1 a, .h2 a .h3 a, .h4 a, .h5 a, .h6 a,.bt-cs.exeltis .bt-inner .bt-title ,.avs_gallery.exeltis  span.title,.avs_gallery.exeltis  span.title a,.registration legend,.ztTestimonial.exeltis .name, .large_text_exeltis,.bt-cs.exeltis h3,.bt-cs.exeltis h3 a,.tab-exeltis ul.tabs_container li.tab,.iceventlist-title, .purchase.blue.exeltis, .purchase:hover.blue.exeltis,.playersquad-entry.exeltis .member h2{font-family:Poppins Bold,Helvetica,Arial, sans-serif!important; text-transform:none;}

.tp-caption,.tp-caption.large_text_exeltis,.tp-caption.medium_light_black_exeltis, .medium_light_black_exeltis,.tp-caption.largeblackbg_exeltis, .largeblackbg_exeltis,.tp-button.blue.exeltis, .tp-button:hover.blue.exeltis,span.timer,.tp-caption.large_bold_white_exeltis{font-family:Poppins Bold,Helvetica,Arial, sans-serif!important;}

#t3-mainnav,#t3-mainnav.navbar-default .navbar-nav > li > a,.navbar-default .navbar-nav li > .separator, .navbar-default .navbar-nav li > span{ font-family: Nunito Sans Regular,Helvetica,Arial, sans-serif!important;transition: all 0.2s  linear 0s;text-transform:none; font-size:18px;}
</style>
<?= $tete ?? '' ?>
</head>

<body class="fullwidth">
<div class="t3-wrapper">

  <!-- HEADER -->
<header id="t3-header">
<div class="container t3-header">
	<div class="row">
		<!-- LOGO -->
		<div class="col-xs-12 col-sm-5 col-md-5 logo">
			<div class="logo-image">
				<a href="<?= e(site_url()) ?>" title="<?= e(reglage('site_nom')) ?>">
					<img class="logo-img" src="<?= e(media('images/biblio/logo-uk-original.png')) ?>" alt="<?= e(reglage('site_nom')) ?>" />
					<span><?= e(reglage('site_nom')) ?></span>
				</a>
				<small class="site-slogan"></small>
			</div>
		</div>
		<!-- //LOGO -->
			<div class="col-xs-12 col-sm-7 col-md-7 header-right ">
				<div class="mainnav-inner pull-right">

<!-- MAIN NAVIGATION -->
<nav id="t3-mainnav" class="wrap navbar navbar-default t3-mainnav">
		<div class="navbar-header">
			<button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".t3-navbar-collapse">
				<i class="fa fa-bars"></i>
			</button>
		</div>
		<div class="t3-navbar-collapse navbar-collapse collapse"></div>
		<div class="t3-navbar navbar-collapse collapse">
			<div  class="t3-megamenu animate slide"  data-duration="400" data-responsive="true">
<ul itemscope itemtype="http://www.schema.org/SiteNavigationElement" class="nav navbar-nav level0">
<li itemprop='name'<?= $actif === 'accueil' ? ' class="current active"' : '' ?> data-level="1">
<a itemprop='url' class=""  href="<?= e(url()) ?>"   data-target="#">Accueil </a>
</li>
<li itemprop='name'<?= $actif === 'catalogue' ? ' class="current active"' : '' ?> data-level="1">
<a itemprop='url' class=""  href="<?= e(url('catalogue')) ?>"   data-target="#">Catalogue </a>
</li>
<li itemprop='name' class="dropdown mega<?= $actif === 'domaines' ? ' active' . ($sousActif === '' ? ' current' : '') : '' ?>" data-level="1">
<a itemprop='url' class=" dropdown-toggle"  href="<?= e(url('domaines')) ?>"   data-target="#" data-toggle="dropdown">Domaines <em class="caret"></em></a>
<div class="nav-child dropdown-menu mega-dropdown-menu"  ><div class="mega-dropdown-inner">
<div class="row">
<div class="col-xs-12 mega-col-nav" data-width="12"><div class="mega-inner">
<ul itemscope itemtype="http://www.schema.org/SiteNavigationElement" class="mega-nav level1">
<?php foreach ($menuDomaines as $d): ?>
<li itemprop='name'<?= $actif === 'domaines' && $sousActif === $d['slug'] ? ' class="current active"' : '' ?> data-level="2">
<a itemprop='url' class=""  href="<?= e(url('domaines/' . $d['slug'])) ?>"   data-target="#"><?= e($d['nom']) ?> </a>
</li>
<?php endforeach; ?>
</ul>
</div></div>
</div>
</div></div>
</li>
<li itemprop='name' class="dropdown mega<?= $actif === 'types' ? ' active' . ($sousActif === '' ? ' current' : '') : '' ?>" data-level="1">
<a itemprop='url' class=" dropdown-toggle"  href="<?= e(url('types')) ?>"   data-target="#" data-toggle="dropdown">Types <em class="caret"></em></a>
<div class="nav-child dropdown-menu mega-dropdown-menu"  ><div class="mega-dropdown-inner">
<div class="row">
<div class="col-xs-12 mega-col-nav" data-width="12"><div class="mega-inner">
<ul itemscope itemtype="http://www.schema.org/SiteNavigationElement" class="mega-nav level1">
<?php foreach ($menuTypes as $ty): ?>
<li itemprop='name'<?= $actif === 'types' && $sousActif === $ty['slug'] ? ' class="current active"' : '' ?> data-level="2">
<a itemprop='url' class=""  href="<?= e(url('types/' . $ty['slug'])) ?>"   data-target="#"><?= e($ty['nom_pluriel']) ?> </a>
</li>
<?php endforeach; ?>
</ul>
</div></div>
</div>
</div></div>
</li>
<li itemprop='name'<?= $actif === 'auteurs' ? ' class="current active"' : '' ?> data-level="1">
<a itemprop='url' class=""  href="<?= e(url('auteurs')) ?>"   data-target="#">Auteurs </a>
</li>
<li itemprop='name'<?= $actif === 'recherche' ? ' class="current active"' : '' ?> data-level="1">
<a itemprop='url' class=""  href="<?= e(url('recherche')) ?>"   data-target="#">Recherche </a>
</li>
<li itemprop='name'<?= $actif === 'contact' ? ' class="current active"' : '' ?> data-level="1">
<a itemprop='url' class=""  href="<?= e(url('contact')) ?>"   data-target="#">Contact </a>
</li>
<li itemprop='name' class="biblio-nav-compte<?= $actif === 'compte' ? ' current active' : '' ?>" data-level="1">
<a itemprop='url' class=""  href="<?= e(url('compte')) ?>"   data-target="#" title="<?= $lecteur ? 'Espace lecteur — ' . e(nom_complet($lecteur)) : 'Espace lecteur : se connecter' ?>"><i class="fa <?= $lecteur ? 'fa-user-circle' : 'fa-user-circle-o' ?>" aria-hidden="true"></i><span class="biblio-nav-libelle">Espace lecteur</span></a>
</li>
</ul>
</div>

		</div>
</nav>
<!-- //MAIN NAVIGATION -->
<div data-position="top" class="scroll-progress-bar"></div>
				</div>
			</div>
	</div>
	</div>
</header>
<!-- //HEADER -->

<?php if ($accueil): ?>
<?= $contenu ?>
<?php else: ?>
<div id="t3-mainbody" class="container t3-mainbody">
	<div class="row">

		<!-- MAIN CONTENT -->
		<div id="t3-content" class="t3-content col-xs-12">
<?php foreach (flashes() as $f): ?>
			<div class="biblio-alert biblio-alert-<?= e($f['type']) ?>"><?= e($f['message']) ?></div>
<?php endforeach; ?>
<?= $contenu ?>
		</div>
		<!-- //MAIN CONTENT -->

	</div>
</div>
<?php endif; ?>

<?= render(ROOT_DIR . '/views/site/pied.php') ?>

</div>
</body>
</html>
