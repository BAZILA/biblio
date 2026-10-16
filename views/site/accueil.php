<?php
/**
 * Page d'accueil : reprend les sections du site d'origine (diaporama, à propos, chiffres clés,
 * types, domaines, nouveautés, citations, coups de cœur, bandeau contact).
 *
 * @var array $slides, $chiffres, $types, $domaines, $nouveautes, $citations, $coupsDeCoeur
 */

/** Carte d'un ouvrage dans les carrousels « Nouveautés » et « Coups de cœur ». */
$carte = function (array $o, int $index, bool $avecResume): string {
    ob_start(); ?>
			 <div class="slide" style="padding:10px 0px 10px 0px ;margin:0px 0px 0px 0px "  data-slide-index="<?= $index ?>">
			  <div class="jt-inner">
				<div class="jt-imagecover" style="float:none;margin-bottom:15px">
					<img class="jtcs-image" src="<?= e(couverture($o['image'])) ?>" width="400" alt="<?= e($o['titre']) ?>" />
				<div class="hover-icons">
				<a class="jt-icon icon-url" title="<?= e($o['titre']) ?>" href="<?= e($o['url']) ?>"> <i class="fa fa-link"></i></a>
			<a class="jt-icon icon-lightbox jt-image-link" href="<?= e(couverture($o['image'])) ?>" data-lightbox="jt-1"><i class="fa fa-search"></i></a>
		</div>				</div>
		<div class="jt-introtitle-container">
				<span class="jt-category">
				<a href="<?= e(url('domaines/' . $o['domaine_slug'])) ?>" class="cat-link"><i class="far fa-folder-open"></i><?= e($o['domaine_nom']) ?></a>
				</span>
				<h3>
				<a class="jt-title" href="<?= e($o['url']) ?>" itemprop="url" title="<?= e($o['titre']) ?>">
					<?= e($o['titre']) ?>				</a></h3>
<?php if ($avecResume): ?>
				    <div class="jt-introtext">
        <?= e(tronquer($o['resume'], 110)) ?>    </div>
<?php endif; ?>
				<div class="jt-author-date">
				<span class="jt-author" title="<?= e($o['auteurs_txt']) ?>"><i class="fas fa-user"></i>
				<?= e($o['auteurs_txt']) ?>								</span>
								</div>
					<p class="jt-readmore">
						<a class="btn btn-primary jt-readmore" target="_parent"
							title="<?= e($o['titre']) ?>"
							href="<?= e($o['url']) ?>"><i class=" ">&nbsp;</i> Voir la fiche						</a>
					</p>
					</div>
<div></div><div style="clear:both"></div></div></div>
<?php
    return ob_get_clean();
};

/** Styles propres à un carrousel (repris des paramètres du module d'origine). */
$styleCarrousel = fn(string $classe) => "<style type=\"text/css\">.$classe.owl-carousel .owl-nav{justify-content:flex-start}
.$classe.positioncenter.owl-carousel .owl-nav{width:100%;position:absolute;top:50%; bottom:auto;transform:translateY(-50%);justify-content:space-between; z-index:-1;}
.$classe .owl-dots {position: relative;bottom:-15px;}
.$classe.owl-carousel .owl-nav.disabled{ display:none}
.$classe.owl-carousel .jt-introtext{ text-align:justify}
</style>";
?>
<!-- SLIDER -->
  <div style="clear:both"></div>
<div class="slider_bg">
  <div id="slidermodul"><div id="slider-module-wrapper"><div class="slider">
        <div><div class="n2_clear"><ss3-force-full-width data-overflow-x="body" data-horizontal-selector="body"><div class="n2-section-smartslider fitvidsignore  n2_clear" data-ssid="2"><div id="n2-ss-2-align" class="n2-ss-align"><div class="n2-padding"><div id="n2-ss-2" data-creator="Smart Slider 3" data-responsive="fullwidth" class="n2-ss-slider n2-ow n2-has-hover n2notransition  "><div class="n2-ss-slider-wrapper-inside">
        <div class="n2-ss-slider-1 n2_ss__touch_element n2-ow">
            <div class="n2-ss-slider-2 n2-ow">
                <div class="n2-ss-slider-3 n2-ow">

                    <div class="n2-ss-slide-backgrounds n2-ow-all"><?php foreach ($slides as $i => $s): ?><div class="n2-ss-slide-background" data-public-id="<?= $i + 1 ?>" data-mode="fill"<?= $i ? ' aria-hidden="true"' : '' ?>><div class="n2-ss-slide-background-image" data-blur="0" data-opacity="100" data-x="50" data-y="50" data-alt="" data-title=""><picture class="skip-lazy" data-skip-lazy="1"><img src="<?= e(media($s['image'])) ?>" alt="" title="" loading="lazy" class="skip-lazy" data-skip-lazy="1"></picture></div><div data-color="RGBA(255,255,255,0)" style="background-color: RGBA(255,255,255,0);" class="n2-ss-slide-background-color" data-overlay="1"></div></div><?php endforeach; ?></div>                    <div class="n2-ss-slider-4 n2-ow">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1200 715" data-related-device="desktopPortrait" class="n2-ow n2-ss-preserve-size n2-ss-preserve-size--slider n2-ss-slide-limiter"></svg><?php foreach ($slides as $i => $s):
                            $n = $i + 1;
                            $anim = 'data-animv2="{&quot;basic&quot;:{&quot;in&quot;:{&quot;type&quot;:&quot;basic&quot;,&quot;name&quot;:&quot;&quot;,&quot;keyFrames&quot;:[{}]}}}"';
                            $titreTexte = str_replace("\n", ' ', $s['titre']);
                        ?><div<?= $i ? '' : ' data-first="1"' ?> data-slide-duration="0" data-id="<?= $n ?>" data-slide-public-id="<?= $n ?>"<?= $i ? ' aria-hidden="true"' : '' ?> data-title="<?= e($titreTexte) ?>" class="n2-ss-slide n2-ow  n2-ss-slide-<?= $n ?>"><div role="note" class="n2-ss-slide--focus" tabindex="-1"><?= e($titreTexte) ?></div><div class="n2-ss-layers-container n2-ss-slide-limiter n2-ow"><div class="n2-ss-layer n2-ow n-uc-s<?= $n ?>-slide" data-sstype="slide" data-pm="default"><div class="n2-ss-layer n2-ow n-uc-s<?= $n ?>-content" data-pm="default" data-sstype="content" data-hasbackground="0"><div class="n2-ss-section-main-content n2-ss-layer-with-background n2-ss-layer-content n2-ow n-uc-s<?= $n ?>-content-inner"><div class="n2-ss-layer n2-ow n-uc-s<?= $n ?>-titre" data-pm="absolute" data-responsiveposition="1" data-desktopportraitleft="0" data-tabletportraitleft="15" data-mobileportraitleft="15" data-desktopportraittop="70" data-tabletportraittop="36" data-mobileportraittop="16" data-responsivesize="1" data-desktopportraitwidth="1160" data-desktopportraitheight="auto" data-desktopportraitalign="left" data-desktopportraitvalign="top" data-tabletportraitvalign="top" data-mobileportraitvalign="top" data-parentid="" data-desktopportraitparentalign="center" data-desktopportraitparentvalign="middle" <?= $anim ?> data-sstype="layer"><div class="n2-font-titre-hover   n2-ss-item-content n2-ss-text n2-ow" style="display:block;"><?= nl2br(e($s['titre']), false) ?></div></div><?php if (trim((string) $s['texte']) !== ''): ?><div class="n2-ss-layer n2-ow n-uc-s<?= $n ?>-texte" data-pm="absolute" data-responsiveposition="1" data-desktopportraitleft="0" data-tabletportraitleft="17" data-desktopportraittop="250" data-tabletportraittop="150" data-mobileportraittop="95" data-responsivesize="1" data-desktopportraitwidth="602" data-tabletportraitwidth="875" data-desktopportraitheight="auto" data-tabletportraitheight="auto" data-desktopportraitalign="left" data-tabletportraitalign="left" data-desktopportraitvalign="top" data-tabletportraitvalign="top" data-mobileportraitvalign="top" data-parentid="" data-desktopportraitparentalign="center" data-desktopportraitparentvalign="middle" <?= $anim ?> data-sstype="layer"><div class="n2-ss-item-content n2-ss-text n2-ow-all"><div data-hide-desktoplandscape="1" data-hide-desktopportrait="1" data-hide-tabletlandscape="1" data-hide-tabletportrait="1"><p class="n2-font-texte-paragraph   "><?= e($s['texte']) ?></p>
</div><div class="" data-hide-mobilelandscape="1" data-hide-mobileportrait="1"><p class="n2-font-texte-paragraph   "><?= e($s['texte']) ?></p>
</div></div></div><?php endif; ?><?php if ($s['bouton'] !== '' && $s['lien'] !== ''): ?><div class="n2-ss-layer n2-ow n-uc-s<?= $n ?>-bouton" data-pm="absolute" data-responsiveposition="1" data-desktopportraitleft="0" data-tabletportraitleft="17" data-mobileportraitleft="15" data-desktopportraittop="360" data-tabletportraittop="250" data-mobileportraittop="175" data-responsivesize="1" data-desktopportraitwidth="260" data-desktopportraitheight="49" data-desktopportraitalign="left" data-desktopportraitvalign="top" data-tabletportraitvalign="top" data-mobileportraitvalign="top" data-parentid="" data-desktopportraitparentalign="center" data-desktopportraitparentvalign="middle" <?= $anim ?> data-sstype="layer"><div><div class="n2-ss-item-content n2-ow"><div style="text-align:inherit;"><a href="<?= e(lien_slide($s['lien'])) ?>" class="btn btn-primary"><?= e($s['bouton']) ?></a></div></div></div></div><?php endif; ?></div></div></div></div></div><?php endforeach; ?>                    </div>

                                    </div>
            </div>
        </div>
        <div class="n2-ss-slider-controls n2-ss-slider-controls-absolute-left-center"><div style="--widget-offset:15px;" class="n2-ss-widget nextend-arrow n2-ow-all nextend-arrow-previous  nextend-arrow-animated-fade" data-hide-mobilelandscape="1" data-hide-mobileportrait="1" id="n2-ss-2-arrow-previous" role="button" aria-label="previous arrow" tabindex="0"><img width="32" height="32" class="skip-lazy" data-skip-lazy="1" src="data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMzIiIGhlaWdodD0iMzIiIHZpZXdCb3g9IjAgMCAzMiAzMiIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj4KICAgIDxwYXRoIGQ9Ik0xMS40MzMgMTUuOTkyTDIyLjY5IDUuNzEyYy4zOTMtLjM5LjM5My0xLjAzIDAtMS40Mi0uMzkzLS4zOS0xLjAzLS4zOS0xLjQyMyAwbC0xMS45OCAxMC45NGMtLjIxLjIxLS4zLjQ5LS4yODUuNzYtLjAxNS4yOC4wNzUuNTYuMjg0Ljc3bDExLjk4IDEwLjk0Yy4zOTMuMzkgMS4wMy4zOSAxLjQyNCAwIC4zOTMtLjQuMzkzLTEuMDMgMC0xLjQybC0xMS4yNTctMTAuMjkiCiAgICAgICAgICBmaWxsPSIjZmZmZmZmIiBvcGFjaXR5PSIwLjgiIGZpbGwtcnVsZT0iZXZlbm9kZCIvPgo8L3N2Zz4=" alt="previous arrow"></div></div><div class="n2-ss-slider-controls n2-ss-slider-controls-absolute-right-center"><div style="--widget-offset:15px;" class="n2-ss-widget nextend-arrow n2-ow-all nextend-arrow-next  nextend-arrow-animated-fade" data-hide-mobilelandscape="1" data-hide-mobileportrait="1" id="n2-ss-2-arrow-next" role="button" aria-label="next arrow" tabindex="0"><img width="32" height="32" class="skip-lazy" data-skip-lazy="1" src="data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMzIiIGhlaWdodD0iMzIiIHZpZXdCb3g9IjAgMCAzMiAzMiIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj4KICAgIDxwYXRoIGQ9Ik0xMC43MjIgNC4yOTNjLS4zOTQtLjM5LTEuMDMyLS4zOS0xLjQyNyAwLS4zOTMuMzktLjM5MyAxLjAzIDAgMS40MmwxMS4yODMgMTAuMjgtMTEuMjgzIDEwLjI5Yy0uMzkzLjM5LS4zOTMgMS4wMiAwIDEuNDIuMzk1LjM5IDEuMDMzLjM5IDEuNDI3IDBsMTIuMDA3LTEwLjk0Yy4yMS0uMjEuMy0uNDkuMjg0LS43Ny4wMTQtLjI3LS4wNzYtLjU1LS4yODYtLjc2TDEwLjcyIDQuMjkzeiIKICAgICAgICAgIGZpbGw9IiNmZmZmZmYiIG9wYWNpdHk9IjAuOCIgZmlsbC1ydWxlPSJldmVub2RkIi8+Cjwvc3ZnPg==" alt="next arrow"></div></div></div></div><ss3-loader></ss3-loader></div></div><div class="n2_clear"></div></div></ss3-force-full-width></div></div>
      </div></div></div>
</div>
<div style="clear:both"></div>
<!-- //SLIDER -->

<?php foreach (flashes() as $f): ?>
<div class="container"><div class="biblio-alert biblio-alert-<?= e($f['type']) ?>"><?= e($f['message']) ?></div></div>
<?php endforeach; ?>

<!-- TOPSL -->
<div class="wrap topsl_bg ">
<div class="topsl_inner container t3-sl">
	 <div class="t3-module module "><div class="module-inner"><div class="module-ct">
<div class="mod-custom custom">
    <?= bloc('accueil_apropos') ?></div>
</div></div></div>
	</div></div>
		<div style="clear:both"></div>
<!-- //TOPSL -->

	<div style="clear:both"></div>
			 <div style="clear:both"></div>
			 <div class="wrap uppermain">
			 <div class="uppermain">
			 <div class="container t3-sl">
        <div class="t3-module module "><div class="module-inner"><div class="module-ct">
<div class="mod-custom custom">
    <div id="counter" class="homepage">
<div class="counters row">
<?php foreach ([['fa-book', $chiffres['ouvrages'], 'Ouvrages'], ['fa-th-large', $chiffres['domaines'], 'Domaines'], ['fa-users', $chiffres['auteurs'], 'Auteurs'], ['fa-tags', $chiffres['types'], 'Types de documents']] as [$icone, $nombre, $libelle]): ?>
<div class="col-md-3 col-sm-3">
<div class="counter-value"><i class="fas <?= $icone ?>"> </i><span class="timer" data-to="<?= (int) $nombre ?>" data-speed="3000"><?= (int) $nombre ?></span>
<h4><?= e($libelle) ?></h4>
</div>
</div>
<?php endforeach; ?>
</div>
</div></div>
</div></div></div>
      </div></div></div>
	   			 <div style="clear:both"></div>
			 <div class="uppermain-2">
			 <div class="container t3-sl">
        <div class="t3-module module "><div class="module-inner"><div class="module-ct">
<div class="mod-custom custom">
    <div class="special-work-wrap">
<div class="col-md-6 col-sm-6 col-xs-12 special-work">
<?= bloc('accueil_types') ?>
<div>
<ul class="biblio-types">
<?php foreach ($types as $ty): ?>
<li><i class="fa fa-check-square-o" aria-hidden="true"></i> <a href="<?= e(url('types/' . $ty['slug'])) ?>"><?= e($ty['nom_pluriel']) ?></a><?php if ($ty['description']): ?> <small>— <?= e($ty['description']) ?></small><?php endif; ?></li>
<?php endforeach; ?>
</ul>
</div>
<div style="clear: both; margin-top: 10px;" class="styled-text"><a href="<?= e(url('types')) ?>">VOIR TOUS LES TYPES DE DOCUMENTS…</a></div>
</div>
<div class="col-md-6 col-sm-6 col-xs-12 special-work">
<div class="clearfix margin-bottom-2"><img class="img-responsive" style="float: right;" src="<?= e(media('images/biblio/types.jpg')) ?>" alt="Livres ouverts" />
<div class="glow-wrap"><i class="glow"></i></div>
</div>
</div>
</div></div>
</div></div></div>
      </div></div>
	  	 			 <div style="clear:both"></div>
			 <div class="uppermain-5">
			 <div class="container t3-sl">
        <div class="t3-module module "><div class="module-inner"><div class="module-ct">
<div class="mod-custom custom">
    <div class="row-title" style="text-align: center;">
<h4 class="bordered-title" style="text-align: center;">Domaines</h4>
<h1 class="leading" style="text-align: center;">Explorez le catalogue par domaine</h1>
</div>
<p>&nbsp;</p>
<div class="biblio-domaines">
<?php foreach ($domaines as $d): ?>
<div class="col-md-3 col-sm-6">
<div class="xl-services-box-wrapper box-shadow">
<div class="relative services-count-2 bg-white p-a30">
<div class="services-xl inline-icon m-b25 text-primary styled-bg align-center"><span class="services-cell"><i class="fa <?= e($d['icone']) ?>" aria-hidden="true"> </i></span></div>
<div class="services-content">
<h4 class="xl-title m-b25"><?= e($d['nom']) ?></h4>
<p><?= e($d['description']) ?></p>
<p class="biblio-count"><?= e(pluriel((int) $d['nb'], 'ouvrage')) ?></p>
<a href="<?= e(url('domaines/' . $d['slug'])) ?>" class="btn btn-primary" data-hover="Voir les ouvrages">Voir les ouvrages</a></div>
</div>
</div>
</div>
<?php endforeach; ?>
</div></div>
</div></div></div>
		</div>
      </div>
	<div style="clear:both"></div>
	<div class="uppermain-6-7">
	<div class="uppermain-6 col-xs-12 col-sm-12 col-md-12">
		 <div class="t3-module module "><div class="module-inner"><div class="module-ct"><div class="jtcs_item_wrapper jt-cs exeltis exeltis-2 projects" style="padding:10px 10px 10px 10px;">
<div class="jt-pretext">
<span class="pretext_title"><h4 class="bordered-title" style="text-align: center;">Nouveautés</h4><br/><br/><h1 class="leading">Derniers ouvrages ajoutés</h1><br/></span>
<p class="pretext"> </p>
</div>
<div class="jtcs-nouveautes positioncenter owl-carousel owl-carousel owl-theme slides_container">
<?php foreach ($nouveautes as $i => $o): ?><?= $carte($o, $i, true) ?><?php endforeach; ?>
		</div>
</div>
<?= $styleCarrousel('jtcs-nouveautes') ?>
<script>biblioCarrousel('.jtcs-nouveautes.owl-carousel', {0: {items: 1, rows: 4}, 768: {items: 2, rows: 3}, 991: {items: 4, rows: 1}});</script>
</div></div></div>
	</div>
	<div class="uppermain-7 ">
	</div></div>

<?php if ($citations): ?>
		<div style=" clear:both"></div>
		<div class="wrap bottomspot-2 "><div class="container t3-sl">
			<div class="bottomspot-2-module">
				<div class="t3-module module "><div class="module-inner"><div class="module-ct"><style type="text/css">.jt_testimonial-biblio.owl2-carousel2.nav-outside-top .owl2-nav {top:-40px;right: 0px;}
.jt_testimonial-biblio.nav-bottom-right .owl2-nav {bottom:0px;right: 0px;}
.jt_testimonial-biblio .testimonial_block-image{max-width:100%;min-width:66px;width:66px;height:66px;border-radius:100%;margin-right:20px;border:4px solid #d5d5d5;}
.jt_testimonial-biblio .testimonial_block {flex-direction:inherit;}
.jt_testimonial-biblio{padding:10px 10px 40px 10px; }
.jt_testimonial-biblio .owl2-dots,.jt_testimonial-biblio .owl2-nav.disabled + .owl2-dots {bottom: -5%;}
.jt_testimonial-biblio .jt_testimonial-block-slide {padding:30px 30px 30px 30px; }
</style>

<div class="exeltis client">

<div class="jt-pretext">
<span class="pretext_title"></span>
<p class="pretext"><h4 class="bordered-title  text-center w-100">Citations</h4><br/><h1  class="text-center w-100 leading bicolor">Paroles d’auteurs</h1></p>
</div>
<div class="jt_testimonial-biblio owl2-carousel2  owl2-nav "  data-autoplay="true" data-autoplay-hover-pause="true" data-autoplay-timeout="5000" data-autoplay-speed="1000" data-loop="true" data-nav-speed="1000"  data-dots-speed="1000" >
<?php foreach ($citations as $i => $c): ?>
<div class="jt_testimonial-outer slide" data-slide-index="<?= $i ?>">
<div class="jt_testimonial-block-slide"><div class="testimonial_block-text"><?= e($c['texte']) ?></div><div class="testimonial_block">

<?php if ($c['image']): ?><div class="testimonial_block-image"> <img src="<?= e(media($c['image'])) ?>" alt="<?= e($c['auteur']) ?>"> </div><?php endif; ?><div class="testimonial_block-data">
<div class="testimonial_block-name"><h3><?= e($c['auteur']) ?></h3></div><div class="testimonial_block-position"><?= e($c['source']) ?></div><div class="jt-social-icons">
</div>

</div>

</div>
</div>
</div>
<?php endforeach; ?>
</div>
</div>
<script>biblioCitations('.jt_testimonial-biblio.owl2-carousel2');</script>
</div></div></div>
				</div>
		</div></div>
<?php endif; ?>

<?php if ($coupsDeCoeur): ?>
	<div style=" clear:both"></div>
	<div class="wrap bottomspot-3 ">
		<div class="container t3-sl">
			<div class="jtcs_item_wrapper jt-cs exeltis exeltis-2 news" style="padding:0px 0px 0px 0px;">
<div class="jt-pretext">
<span class="pretext_title"><h4 class="bordered-title" style="text-align: center;">Sélection</h4><br/><br/><h1 class="leading">Les coups de cœur des bibliothécaires</h1><br/></span>
<p class="pretext"> </p>
</div>
<div class="jtcs-coeur positioncenter owl-carousel owl-carousel owl-theme slides_container">
<?php foreach ($coupsDeCoeur as $i => $o): ?><?= $carte($o, $i, false) ?><?php endforeach; ?>
		</div>
</div>
<?= $styleCarrousel('jtcs-coeur') ?>
<script>biblioCarrousel('.jtcs-coeur.owl-carousel', {0: {items: 1, rows: 4}, 768: {items: 2, rows: 3}, 991: {items: 3, rows: 1}});</script>
		</div>
	</div>
<?php endif; ?>

	<!-- NAV HELPER -->
	<nav class="wrap t3-navhelper ">
		<div class="container">
<div class="mod-custom custom">
    <?= bloc('appel_contact') ?></div>
		</div>
	</nav>
	<!-- //NAV HELPER -->
