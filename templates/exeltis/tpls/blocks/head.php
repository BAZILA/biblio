<?php
/**
 * @package   T3 Blank
 * @copyright Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license   GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;
use Joomla\CMS\Factory;
error_reporting(E_ALL ^ E_NOTICE  ^ E_DEPRECATED ^ E_WARNING);
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Language\Text;

?>

<!-- META FOR IOS & HANDHELD -->
<?php if ($this->getParam('responsive', 1)): ?>
	<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no"/>
	<style type="text/stylesheet">
		@-webkit-viewport   { width: device-width; }
		@-moz-viewport      { width: device-width; }
		@-ms-viewport       { width: device-width; }
		@-o-viewport        { width: device-width; }
		@viewport           { width: device-width; }
	</style>
	<script type="text/javascript">
		//<![CDATA[
		if (navigator.userAgent.match(/IEMobile\/10\.0/)) {
			var msViewportStyle = document.createElement("style");
			msViewportStyle.appendChild(
				document.createTextNode("@-ms-viewport{width:auto!important}")
			);
			document.getElementsByTagName("head")[0].appendChild(msViewportStyle);
		}
		//]]>
	</script>
<?php endif ?>
<meta name="HandheldFriendly" content="true"/>
<meta name="apple-mobile-web-app-capable" content="YES"/>
<!-- //META FOR IOS & HANDHELD -->
<link rel="stylesheet" id="jssDefault" type="text/css" href="<?php echo $this->baseurl ?>/templates/<?php echo $this->template; ?>/css/default.css" />


<?php
// T3 BASE HEAD
$this->addHead();
$document = Factory::getDocument();
$document->addHeadLink(Uri::root(true) . '/media/system/images/favicon.png?v=2', 'icon', 'rel', ['type' => 'image/png']);
$document->addHeadLink(Uri::root(true) . '/media/system/images/favicon.ico?v=2', 'alternate icon', 'rel', ['type' => 'image/vnd.microsoft.icon']);
$document->addHeadLink(Uri::root(true) . '/images/biblio/uk-sigle.png', 'apple-touch-icon', 'rel');
$document->addScript(T3_TEMPLATE_URL.'/js/moduletitle.js');
$document->addScript(T3_TEMPLATE_URL.'/js/count.js', 'text/javascript', false, true);
$document->addScript(T3_TEMPLATE_URL.'/js/progress.js');
$document->addScript(T3_TEMPLATE_URL.'/js/skills.js');
//STYLE SWITCHER
$document->addScript(T3_TEMPLATE_URL.'/js/styleswitcher.js');
?>
<?php  if($this->direction == 'rtl') {
$css_path='css/rtl';
}
else if($this->direction == 'ltr')  {
$css_path='css/themes';
}
 ?>
<?php
// CUSTOM CSS
if (is_file(T3_TEMPLATE_PATH . '/css/custom.css')) {
	$this->addStyleSheet(T3_TEMPLATE_URL . '/css/custom.css');
}
?>
<?php
//////////Google Fonts//////////////

$googlefontsbody  = $this->params->get('googlefontsbody');
if($fontfamilyBody= null){
$fontfamilyBody='';
}
if ($googlefontsbody=='Source+Sans+Pro:wght@400;600')
{
	$fontfamilyBody='Source Sans Pro Regular,';
}
if ($googlefontsbody=='OpenSans')
{
	$fontfamilyBody='Open Sans,';
}
if ($googlefontsbody=='PT+Sans')
{
	$fontfamilyBody='PT Sans Regular,';
}
if ($googlefontsbody=='Rajdhani')
{
	$fontfamilyBody='Rajdhani,';
}
if ($googlefontsbody=='Roboto')
{
	$fontfamilyBody='Roboto Regular,';
}
if ($googlefontsbody=='Roboto:wght@500')
{
	$fontfamilyBody='Roboto Medium,';
}
if ($googlefontsbody=='Old+Standard+TT')
{
	$fontfamilyBody='Old Standard TT,';
	}
	
if ($googlefontsbody=='Roboto+Slab:wght@400;700')
{
	$fontfamilyBody='Roboto Slab,';
}
if ($googlefontsbody=='Lato:wght@400;700')
{
	$fontfamilyBody='Lato,';
}
if ($googlefontsbody=='Libre+Franklin')
{
	$fontfamilyBody='Libre Franklin,';
}
if ($googlefontsbody=='News+Cycle')
{
	$fontfamilyBody='"News Cycle",';
}
if ($googlefontsbody=='Domine')
{
	$fontfamilyBody='Domine,';
}

if ($googlefontsbody=='Brawler')
{
	$fontfamilyBody='Brawler,';
}

if ($googlefontsbody=='Belgrano')
{
	$fontfamilyBody='Belgrano,';
}


if ($googlefontsbody=='Playfair+Display:wght@400;700')
{
	$fontfamilyBody='Playfair Display,';
} 

if ($googlefontsbody=='Slabo+27px')
{
	$fontfamilyBody='"Slabo 27px",';
} 
if ($googlefontsbody=='Montserrat:wght@400;600;700')
{
	$fontfamilyBody='Montserrat,';
}

if ($googlefontsbody=='IBM+Plex+Sans:wght@400;600;700')
{
	$fontfamilyBody='IBM Plex Sans,';
}

if ($googlefontsbody=='Varela')
{
	$fontfamilyBody='Varela,';
}

if ($googlefontsbody=='Raleway')
{
	$fontfamilyBody='Raleway Regular,';
}
if ($googlefontsbody=='Oswald')
{
	$fontfamilyBody='Oswald Regular,';
}
if ($googlefontsbody=='Cairo')
{
	$fontfamilyBody='Cairo,';
}

if ($googlefontsbody=='Poppins')
{
	$fontfamilyBody='Poppins Regular,';
}
if ($googlefontsbody=='Poppins:wght@700')
{
	$fontfamilyBody='Poppins Bold,';
}
if ($googlefontsbody=='Roboto+Condensed')
{
	$fontfamilyBody='Roboto Condensed Regular,';
}
if ($googlefontsbody=='Roboto+Condensed+Bold')
{
	$fontfamilyBody='Roboto Condensed Bold,';
}
if ($googlefontsbody=='Play')
{
	$fontfamilyBody='Play,';
}

if ($googlefontsbody=='Prompt:wght@400;500;700')
{
	$fontfamilyBody='Prompt,';
}

if ($googlefontsbody=='Nunito+Sans')
{
	$fontfamilyBody='Nunito Sans Regular,';
}

if ($googlefontsbody=='Nunito+Sans:wght@700')
{
	$fontfamilyBody='Nunito Sans Bold,';
}
////////////////////

$googlefontsheading  = $this->params->get('googlefontsheading');
if($fontfamilyHeading= null){
$fontfamilyHeading='';
}

if ($googlefontsheading=='Source+Sans+Pro:wght@400;600')
{
	$fontfamilyHeading='Source Sans Pro Regular,';
}
if ($googlefontsheading=='OpenSans')
{
	$fontfamilyHeading='Open Sans,';
}
if ($googlefontsheading=='PT+Sans')
{
	$fontfamilyHeading='PT Sans Regular,';
}
if ($googlefontsheading=='Rajdhani')
{
	$fontfamilyHeading='Rajdhani,';
}
if ($googlefontsheading=='Roboto')
{
	$fontfamilyHeading='Roboto Regular,';
}
if ($googlefontsheading=='Roboto:wght@500')
{
	$fontfamilyHeading='Roboto Medium,';
}
if ($googlefontsheading=='Old+Standard+TT')
{
	$fontfamilyHeading='Old Standard TT,';
	}
	
if ($googlefontsheading=='Roboto+Slab:wght@400;700')
{
	$fontfamilyHeading='Roboto Slab,';
}
if ($googlefontsheading=='Lato:wght@400;700')
{
	$fontfamilyHeading='Lato,';
}
if ($googlefontsheading=='Libre+Franklin')
{
	$fontfamilyHeading='Libre Franklin,';
}
if ($googlefontsheading=='News+Cycle')
{
	$fontfamilyHeading='"News Cycle",';
}
if ($googlefontsheading=='Domine')
{
	$fontfamilyHeading='Domine,';
}

if ($googlefontsheading=='Brawler')
{
	$fontfamilyHeading='Brawler,';
}

if ($googlefontsheading=='Belgrano')
{
	$fontfamilyHeading='Belgrano,';
}


if ($googlefontsheading=='Playfair+Display:wght@400;700')
{
	$fontfamilyHeading='Playfair Display,';
} 

if ($googlefontsheading=='Slabo+27px')
{
	$fontfamilyHeading='"Slabo 27px",';
} 
if ($googlefontsheading=='Montserrat:wght@400;600;700')
{
	$fontfamilyHeading='Montserrat,';
}

if ($googlefontsheading=='IBM+Plex+Sans:wght@400;600;700')
{
	$fontfamilyHeading='IBM Plex Sans,';
}

if ($googlefontsheading=='Varela')
{
	$fontfamilyHeading='Varela,';
}

if ($googlefontsheading=='Raleway')
{
	$fontfamilyHeading='Raleway Regular,';
}
if ($googlefontsheading=='Oswald')
{
	$fontfamilyHeading='Oswald Regular,';
}
if ($googlefontsheading=='Cairo')
{
	$fontfamilyHeading='Cairo,';
}
if ($googlefontsheading=='Noto+Sans')
{
	$fontfamilyHeading='Noto Sans,';
}

if ($googlefontsheading=='Poppins')
{
	$fontfamilyHeading='Poppins Regular,';
}
if ($googlefontsheading=='Poppins:wght@700')
{
	$fontfamilyHeading='Poppins Bold,';
}

if ($googlefontsheading=='Roboto+Condensed')
{
	$fontfamilyHeading='Roboto Condensed Regular,';
}
if ($googlefontsheading=='Roboto+Condensed+Bold')
{
	$fontfamilyHeading='Roboto Condensed Bold,';
}
if ($googlefontsheading=='Play')
{
	$fontfamilyHeading='Play,';
}
if ($googlefontsheading=='Prompt:wght@400;500;700')
{
	$fontfamilyHeading='Prompt,';
}

if ($googlefontsheading=='Nunito+Sans')
{
	$fontfamilyHeading='Nunito Sans Regular,';
}

if ($googlefontsheading=='Nunito+Sans:wght@700')
{
	$fontfamilyHeading='Nunito Sans Bold,';
}
////////////////////

$googlefontsnav  = $this->params->get('googlefontsnav');
if($fontfamilyNav= null){
$fontfamilyNav='';
}

if ($googlefontsnav=='Source+Sans+Pro:wght@400;600')
{
	$fontfamilyNav='Source Sans Pro Regular,';
}
if ($googlefontsnav=='OpenSans')
{
	$fontfamilyNav='Open Sans,';
}
if ($googlefontsnav=='PT+Sans')
{
	$fontfamilyNav='PT Sans Regular,';
}
if ($googlefontsnav=='Rajdhani')
{
	$fontfamilyNav='Rajdhani,';
}
if ($googlefontsnav=='Roboto')
{
	$fontfamilyNav='Roboto Regular,';
}
if ($googlefontsnav=='Roboto:wght@500')
{
	$fontfamilyNav='Roboto Medium,';
}
if ($googlefontsnav=='Roboto+Slab:wght@400;700')
{
	$fontfamilyNav='Roboto Slab,';
}
if ($googlefontsnav=='Old+Standard+TT')
{
	$fontfamilyNav='Old Standard TT,';
	}
	
if ($googlefontsnav=='Lato:wght@400;700')
{
	$fontfamilyNav='Lato,';
}
if ($googlefontsnav=='Libre+Franklin')
{
	$fontfamilyNav='Libre Franklin,';
}
if ($googlefontsnav=='News+Cycle')
{
	$fontfamilyNav='"News Cycle",';
}
if ($googlefontsnav=='Domine')
{
	$fontfamilyNav='Domine,';
}

if ($googlefontsnav=='Brawler')
{
	$fontfamilyNav='Brawler,';
}

if ($googlefontsnav=='Belgrano')
{
	$fontfamilyNav='Belgrano,';
}


if ($googlefontsnav=='Playfair+Display:wght@400;700')
{
	$fontfamilyNav='Playfair Display,';
} 

if ($googlefontsnav=='Slabo+27px')
{
	$fontfamilyNav='"Slabo 27px",';
} 
if ($googlefontsnav=='Montserrat:wght@400;600;700')
{
	$fontfamilyNav='Montserrat,';
}

if ($googlefontsnav=='IBM+Plex+Sans:wght@400;600;700')
{
	$fontfamilyNav='IBM Plex Sans,';
}

if ($googlefontsnav=='Varela')
{
	$fontfamilyNav='Varela,';
}

if ($googlefontsnav=='Raleway')
{
	$fontfamilyNav='Raleway Regular,';
}
if ($googlefontsnav=='Oswald')
{
	$fontfamilyNav='Oswald Regular,';
}
if ($googlefontsnav=='Cairo')
{
	$fontfamilyNav='Cairo,';
}

if ($googlefontsnav=='Poppins')
{
	$fontfamilyNav='Poppins Regular,';
}
if ($googlefontsnav=='Poppins:wght@700')
{
	$fontfamilyNav='Poppins Bold,';
}

if ($googlefontsnav=='Roboto+Condensed')
{
	$fontfamilyNav='Roboto Condensed Regular,';
}
if ($googlefontsnav=='Roboto+Condensed+Bold')
{
	$fontfamilyNav='Roboto Condensed Bold,';
}

if ($googlefontsnav=='Play')
{
	$fontfamilyNav='Play,';
}
if ($googlefontsnav=='Prompt:wght@400;500;700')
{
	$fontfamilyNav='Prompt,';
}
if ($googlefontsnav=='Nunito+Sans')
{
	$fontfamilyNav='Nunito Sans Regular,';
}

if ($googlefontsnav=='Nunito+Sans:wght@700')
{
	$fontfamilyNav='Nunito Sans Bold,';
}
?>
<?php 

if (($this->params->get('googlefontsbody')=='Poppins:wght@700') || ($this->params->get('googlefontsbody')=='Nunito+Sans:wght@700') ||  ($this->params->get('googlefontsbody')=='Poppins') || ($this->params->get('googlefontsbody')=='Nunito+Sans:wght@700') ||  ($this->params->get('googlefontsbody')=='Nunito+Sans') )
 {
echo '';
}
else {
echo '<link href="https://fonts.googleapis.com/css2?family='.$googlefontsbody.'&display=swap" rel="stylesheet">';
}
?>
<?php
if (($this->params->get('googlefontsheading')=='Poppins:wght@700') || ($this->params->get('googlefontsheading')=='Nunito+Sans:wght@700') || ($this->params->get('googlefontsheading')=='Poppins') || ($this->params->get('googlefontsheading')=='Nunito+Sans:wght@700') || ($this->params->get('googlefontsheading')=='Nunito+Sans') )
 {
echo '';
}
else {
echo '<link href="https://fonts.googleapis.com/css2?family='.$googlefontsheading.'&display=swap" rel="stylesheet">';
}
?>

<?php
if (($this->params->get('googlefontsnav')=='Poppins:wght@700') || ($this->params->get('googlefontsnav')=='Nunito+Sans:wght@700') || ($this->params->get('googlefontsnav')=='Poppins') || ($this->params->get('googlefontsnav')=='Nunito+Sans:wght@700') || ($this->params->get('googlefontsnav')=='Nunito+Sans') )
 {
echo '';
}
else {
echo '<link href="https://fonts.googleapis.com/css2?family='.$googlefontsnav.'&display=swap" rel="stylesheet">';
}

?>
<?php
//$slideoverlay=$this->params->get('slide_overlay');
if ($this->params->get('slide_overlay')=='dots'){
$slideoverlay='background:url('.Uri::base(true) .'/templates/exeltis/images/dots-overlay.png)';
}
else {
$slideoverlay='';
}
?>
<script type="text/javascript">
	jQuery(document).ready(function() {
	jQuery('.presetArea').styleSwitcher({
  	hasPreview: false,
  	defaultThemeId: 'jssDefault',
  	fullPath:'<?php echo $this->baseurl ?>/templates/<?php echo $this->template; ?>/<?php echo $css_path; ?>/',
  	cookie: {
  		expires:180,
  		isManagingLoad: true
  	}
});
});
</script>

<style type="text/css">
body {font-family:<?php echo $fontfamilyBody;?>Helvetica,Arial, sans-serif!important; font-size:<?php echo $this->params->get('fontSizeBody');?>}
.bg-pattern {background: url(<?php echo Uri::base(true) .'/templates/exeltis/images/patterns/'.$this->params->get('body_bgpattern');?>);}
#t3-header{background-image:url(<?php echo Uri::base(true) .'/templates/exeltis/images/patterns/'.$this->params->get('header_bgpattern');?>); background-repeat:repeat}
.logo{justify-items:<?php echo $this->params->get('horizontal_align');?>;align-items:<?php echo $this->params->get('vertical_align');?>; }

.colormodule.submain h3.module-title,.tp-caption a.btn,.tp-caption a.btn:hover,h1, h2, h3, h4, h5, h6, .h1, .h2, .h3, .h4, .h5, .h6, h1 a, h2 a, h3 a, h4 a, h5 a, h6 a, .h1 a, .h2 a .h3 a, .h4 a, .h5 a, .h6 a,.bt-cs.exeltis .bt-inner .bt-title ,.avs_gallery.exeltis  span.title,.avs_gallery.exeltis  span.title a,.registration legend,.ztTestimonial.exeltis .name, .large_text_exeltis,.bt-cs.exeltis h3,.bt-cs.exeltis h3 a,.tab-exeltis ul.tabs_container li.tab,.iceventlist-title, .purchase.blue.exeltis, .purchase:hover.blue.exeltis,.playersquad-entry.exeltis .member h2{font-family:<?php echo $fontfamilyHeading;?>Helvetica,Arial, sans-serif!important; text-transform:<?php echo $this->params->get('text_transform_titles');?>;}

.tp-caption,.tp-caption.large_text_exeltis,.tp-caption.medium_light_black_exeltis, .medium_light_black_exeltis,.tp-caption.largeblackbg_exeltis, .largeblackbg_exeltis,.tp-button.blue.exeltis, .tp-button:hover.blue.exeltis,span.timer,.tp-caption.large_bold_white_exeltis{font-family:<?php echo $fontfamilyHeading;?>Helvetica,Arial, sans-serif!important;}


#t3-mainnav,#t3-mainnav.navbar-default .navbar-nav > li > a,.navbar-default .navbar-nav li > .separator, .navbar-default .navbar-nav li > span{ font-family: <?php echo $fontfamilyNav;?>Helvetica,Arial, sans-serif!important;transition: all 0.2s  linear 0s;text-transform:<?php echo $this->params->get('text_transform_nav');?>; font-size:<?php echo $this->params->get('fontsize_nav');?>;}
.slider .n2-ss-slide-backgrounds::after{<?php echo $slideoverlay;?>}
</style>
<!-- Le HTML5 shim and media query for IE8 support -->
<!--[if lt IE 9]>
<script src="//cdnjs.cloudflare.com/ajax/libs/html5shiv/3.7.3/html5shiv.min.js"></script>
<script type="text/javascript" src="<?php echo T3_URL ?>/js/respond.min.js"></script>
<![endif]-->

<!-- You can add Google Analytics here or use T3 Injection feature -->
