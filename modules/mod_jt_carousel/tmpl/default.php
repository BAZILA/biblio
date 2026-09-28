<?php
/*------------------------------------------------------------------------
# JT Carousel Extension
# ------------------------------------------------------------------------
 * 
 * @package 	JT Carousel
 * @subpackage 	mod_jt_carousel
 * @version   	1.0
 * @author    	JoomlaTema
 * @copyright 	Copyright (C) 2008 - 2022 http://www.joomlatema.net. All rights reserved.
 * @license   	GNU General Public License version 2 or later; see LICENSE.txt
 *
 * # Website: http://www.joomlatema.net
 */

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper; 
$carousel_items    = $params->get('carousel_items');
?>

<style type='text/css' media='screen'>
.jt-carousel<?php echo $module->id; ?>.tns-horizontal.tns-subpixel .carousel-inner.tns-item {padding:<?php echo $params->get('ItemPadding');?>;}
.jt-carousel<?php echo $module->id; ?>.tns-horizontal.tns-subpixel .carousel-inner.tns-item .carousel-title.titleonimg{}
.jt-carousel<?php echo $module->id; ?>.tns-horizontal.tns-subpixel .carousel-inner.tns-item .carousel-title.titleonimg{
h1,h2,h3,h4,h5,h6{position:absolute; z-index:6;top:0;left:0;width:100%;height:100%;display:flex; justify-content:<?php echo $params->get('PosHorz');?>; align-items:<?php echo $params->get('PosVert');?>; padding:10px}
}
.tns-controls,.playButton{display:flex; justify-content:<?php echo $params->get('NavButPos');?>; }
.tns-nav{display:flex; justify-content:<?php echo $params->get('DotHorzPos');?>; }
</style>
<?php if( $params->get('showpretext')==1 ): ?>
<div class="jt-pretext jt-carousel">
<span class="pretexttitle"><?php echo $params->get('pretexttitle');?></span>
<p class="pretext"><?php echo $params->get('pretext_text');?></p>
</div>
<?php endif; ?>
<div class="jt-carousel<?php echo $module->id; ?>" style="padding:<?php echo $params->get('ModulePadding');?>;">
<?php if ( ! empty($images) ) 
{
	$jtc_imgwidth 		= (int) $params->get('jtc_imgwidth', 100);
	$jtc_imgheight 		= (int) $params->get('jtc_imgheight', 75);
	$jtc_autointerval = $params->get('jtc_autointerval', 'true');
	$jtc_intervaltime = (int) $params->get('jtc_intervaltime', 1500);
	$jtc_animation 	= (int) $params->get('jtc_animation', 1000);
	$jtc_random 		= $params->get('jtc_random', 'NO');
	$jtc_arrowcolor 	= $params->get('jtc_arrowcolor');
	
	if($jtc_random == "YES")
	{
		shuffle($images);
	}
	$titleposclass="";
	if ($params->get('TitleonImage')==1){
	$titleposclass="titleonimg";
	}
	$JTcImg = "";
	$jtc_imgwidth1 = $jtc_imgwidth + 4;
	$jtc_imgheight1 = $jtc_imgheight + 4;
	?>
	<?php foreach ($carousel_items as $item) : ?>
<?php
    // Strip Joomla media manager fragment (#joomlaImage://...?width=x&height=y)
    $imgSource = $item->jtcimage;
    $imgClean  = HTMLHelper::cleanImageURL($imgSource);
    $jtcimage  = ($imgClean->url != '') ? $imgClean->url : $imgSource;
?>
<div class="carousel-inner">
<?php if (($params->get('ShowImg')==1)  && !empty($jtcimage)) : ?>
<div class="carousel-image" style="border:<?php echo $params->get('ImgBorder');?>;border-radius:<?php echo $params->get('ImgBorderRad');?>; width:<?php echo $params->get('jtc_imgwidth');?>; max-width: 100%;height:<?php echo $params->get('jtc_imgheight');?>;"><a class="link-image" title="<?php echo $item->title;?>" href="<?php echo $item->link;?>">
<?php if($params->get('showhovericons')== 1):?>	
				<div class="hovericons">
				<a class="jt-icon icon-url" title="<?php echo $item->title;?>" href="<?php echo $item->link;?>"> <i class="fa fa-link"></i></a>
			<a class="jt-icon icon-lightbox2 jt-image-link" href="<?php echo $jtcimage; ?>" data-lightbox2="jt-1"><i class="fa fa-search"></i></a>
		</div><?php endif; ?>
<?php if($params->get('LinkImg')== 1):?>	
<figure class="jt-carousel-img" >
<img src="<?php echo $jtcimage; ?>" alt="<?php echo $item->title; ?>"></a></figure>
<?php if (($params->get('ShowTitle')==1)  && !empty($item->title) && ($params->get('TitleonImage')==1)) : ?>
<div class="carousel-title <?php echo $titleposclass; ?>"><<?php echo $params->get('titleclass'); ?>><?php echo $item->title; ?></<?php echo $params->get('titleclass'); ?>></div>
<?php endif;?>
<?php else :?>
<figure class="jt-carousel-img" style="border:<?php echo $params->get('ImgBorder');?>;border-radius:<?php echo $params->get('ImgBorderRad');?>; width:<?php echo $jtc_imgwidth;?>; height:<?php echo $jtc_imgheight;?>;" >
<img src="<?php echo $jtcimage; ?>" alt="<?php echo $item->title; ?>"></figure><?php if (($params->get('ShowTitle')==1)  && !empty($item->title) && ($params->get('TitleonImage')==1)) : ?>
<div class="carousel-title <?php echo $titleposclass; ?>"><<?php echo $params->get('titleclass'); ?>><?php echo $item->title; ?></<?php echo $params->get('titleclass'); ?>></div>
<?php endif;?>
<?php endif;?>

</div><?php endif;?>
<?php if (($params->get('ShowTitle')==1)  && !empty($item->title) && ($params->get('TitleonImage')==0)) : ?>
<div class="carousel-title <?php echo $titleposclass; ?>"><<?php echo $params->get('titleclass'); ?>><?php echo $item->title; ?></<?php echo $params->get('titleclass'); ?>></div><?php endif;?>
<?php if (($params->get('ShowDescription')==1)  && !empty($item->description)) : ?><div class="carousel-desc"><p><?php echo $item->description; ?></p></div><?php endif;?>
</div>
<?php endforeach;?>
</div><script type="module">
  var slider = tns({
    container: '.jt-carousel<?php echo $module->id; ?>',
    items: <?php echo $params->get('jtc_items');?>,
    mode: '<?php echo $params->get('JtcMode');?>',
    axis: '<?php echo $params->get('Jtcaxis');?>',
    gutter: <?php echo $params->get('ItemMargin');?>,
    edgePadding: 0,
    fixedWidth: false,
    autoWidth: false,
    viewportMax: false,
    slideBy:<?php echo $params->get('slideBy');?>,//<!-- Default:1 Number of slides going on one �click�.-->
    center: false,
    controls: <?php echo $params->get('JtcControls');?>,
    controlsPosition: '<?php echo $params->get('navPos')?>',
    controlsText: [<?php echo $params->get('controlsText');?>],
    controlsContainer: false,
    prevButton: false,
    nextButton: false,
    nav: <?php echo $params->get('showDots');?>,
    navPosition: '<?php echo $params->get('dotPos');?>',
    navContainer: false,
    navAsThumbnails: false,
    arrowKeys: false,
    speed:<?php echo $params->get('jtc_animation');?>,
    autoplay: <?php echo $params->get('jtc_autointerval');?>,
    autoplayPosition: '<?php echo $params->get('navPos');?>',
    autoplayTimeout: <?php echo $params->get('jtc_intervaltime');?>,
    autoplayDirection: 'forward',
    autoplayText: ['start', 'stop'],
    autoplayHoverPause: true,
    autoplayButton: false,
    autoplayButtonOutput: true,
    autoplayResetOnVisibility: true,
    animateIn: 'tns-fadeIn',
    animateOut: 'tns-fadeOut',
    animateNormal: 'tns-normal',
    animateDelay: false,
    loop: true,
    rewind: false,
    autoHeight: false,
    responsive: {
    "220": {
      "items": <?php echo $params->get('slideColumnxs');?>
    },
    "480": {
      "items": <?php echo $params->get('slideColumnsm');?>
    },
	"768": {
      "items": <?php echo $params->get('jtc_items');?>
    }
  },
    lazyload: false,
    lazyloadSelector: '.tns-lazy-img',
    touch: true,
    mouseDrag: false,
    swipeAngle: 15,
    nested: false,
    preventActionWhenRunning: false,
    preventScrollOnTouch: false,
    freezable: true,
    onInit: false,
    useLocalStorage: true,
    nonce: false
  });
  </script>
  <script>
lightbox2.option({
    fadeDuration:<?php echo $params->get('fade_Duration',300);?>,
    fitImagesInViewport:<?php echo $params->get('fitImages_InViewport',true);?>,
    imageFadeDuration: <?php echo $params->get('imageFade_Duration',300);?>,
    positionFromTop: <?php echo $params->get('position_FromTop');?>,
    resizeDuration: <?php echo $params->get('resize_Duration',150);?>,
	  })
</script>
<?php
}
?>