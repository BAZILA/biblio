<?php

/*------------------------------------------------------------------------
# mod_jt_testimonial Extension
# ------------------------------------------------------------------------
# author    joomlatema
# copyright Copyright (C) 2022 joomlatema.net. All Rights Reserved.
# @license - http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
# Websites: http://www.joomlatema.net
-------------------------------------------------------------------------*/
// no direct access
defined('_JEXEC') or die;
use Joomla\CMS\Uri\Uri;

use Joomla\CMS\Factory;
if(!defined('DS')){
define( 'DS', DIRECTORY_SEPARATOR );
}
$slide = $params->get('slides');
$cacheFolder = URI::base(true).'/cache/';
$modID = $module->id;
$modPath = URI::base(true).'/modules/mod_jt_testimonial/';
$document = Factory::getDocument(); 
$moduleclass_sfx = htmlspecialchars($params->get('moduleclass_sfx')??'');
$jqueryload = $params->get('jqueryload');
$jtpreload = $params->get('jtpreload');
$showarrows = $params->get('showarrows');
$fontawesome = $params->get('fontawesome');
$testimonials_items    = $params->get('testimonials_items');
$title            = $params->get('title');
$img            = $params->get('img');
$testimonial       = $params->get('testimonial');
$name           = $params->get('name');
$get_stars   = $params->get('get_stars')==1;
$ImgFloat =  $params->get('ImgFloat');
if ($ImgFloat=='row-reverse')  {
$ImgMargin = 'left';
}
else if ($ImgFloat=='column')  {
$ImgMargin = 'bottom';
}
else if ($ImgFloat=='column-reverse')  {
$ImgMargin = 'top';
}
else   {
$ImgMargin = 'right';
}

$ImgMarginValue = $params->get('ImgMarginValue',20);

/////////////////////////////////////////////////////////////////////
if($jqueryload) $document->addScript($modPath.'assets/js/jquery.min.js');
if($jqueryload) $document->addScript($modPath.'assets/js/jquery-noconflict.js');
$document->addScript($modPath.'assets/js/jquery.owl2.carousel.js');
$document->addScript($modPath.'assets/js/theme.js');
if($jtpreload) $document->addStyleSheet($modPath.'assets/css/jtpreload.css');
$document->addStyleSheet($modPath.'assets/css/style.css');
$document->addStyleSheet($modPath.'assets/css/owl2.carousel.css');
if($fontawesome) $document->addStyleSheet($modPath.'assets/font-awesome.css');
?>
<style type="text/css">.jt_testimonial<?php echo $modID;?>.owl2-carousel2.nav-outside-top .owl2-nav {top:<?php echo $params->get('PosTop',-40);?>px;right: <?php echo $params->get('PosRight',0);?>px;}
.jt_testimonial<?php echo $modID;?>.nav-bottom-right .owl2-nav {bottom:<?php echo $params->get('PosBot');?>px;right: <?php echo $params->get('PosRight',0);?>px;}
.jt_testimonial<?php echo $modID;?> .testimonial_block-image{max-width:100%;min-width:<?php  if (($ImgFloat=='column') or  ($ImgFloat=='column-reverse')){echo "auto";} else echo $params->get('ImgWidth','80px');?>;width:<?php if (($ImgFloat=='column') or  ($ImgFloat=='column-reverse')){echo "auto";} else echo $params->get('ImgWidth','80px');?>;height:<?php echo $params->get('ImgHeight','80px');?>;border-radius:<?php echo $params->get('ImgBorderRad','100%');?>;margin-<?php echo $ImgMargin;?>:<?php echo $ImgMarginValue;?>;border:<?php echo $params->get('ImgBorder');?>;}
.jt_testimonial<?php echo $modID;?> .testimonial_block {flex-direction:<?php echo $ImgFloat;?>;}
.jt_testimonial<?php echo $modID;?>{padding:<?php echo $params->get('ModulePadding');?>; background:<?php echo $params->get('ModuleBg');?>; }
.jt_testimonial<?php echo $modID;?> .owl2-dots,.jt_testimonial<?php echo $modID;?> .owl2-nav.disabled + .owl2-dots {bottom: <?php echo $params->get('dotPos')?>;}
.jt_testimonial<?php echo $modID;?> .jt_testimonial-block-slide {padding:<?php echo $params->get('ItemBlockPadding');?>; }
</style>
<?php if($params->get('jtpreload')=='1') : ?>
<script type="text/javascript">
function preload(){
document.getElementById("loaded").style.display = "none";
//document.getElementById("owl2-testimonial").style.display = "block";
}//preloader
window.onload = preload;
</script>
<div id="loaded"></div>
<?php endif; ?>

<div class="exeltis">
<?php if( $params->get('show_pretext')==1 ): ?>
<div class="jt-pretext">
<span class="pretext_title"><?php echo $params->get('pretext_title');?></span>
<p class="pretext"><?php echo $params->get('pretext');?></p>
</div>
<?php endif; ?>
<div class="jt_testimonial<?php echo $modID;?> owl2-carousel2 <?php echo $params->get('navStyle')?> <?php echo $params->get('navPos')?> <?php echo $params->get('navRounded')?>"  data-autoplay="<?php echo $params->get('autoplay')?>" data-autoplay-hover-pause="<?php echo $params->get('autoplay-hover-pause')?>" data-autoplay-timeout="<?php echo $params->get('autoplay-timeout')?>" data-autoplay-speed="<?php echo $params->get('autoplay-speed')?>" data-loop="<?php echo $params->get('dataLoop')?>" data-nav-speed="<?php echo $params->get('autoplay-speed')?>"  data-dots-speed="<?php echo $params->get('dotsSpeed',1000)?>" >
<?php $i = 0;
 foreach ($testimonials_items as $item) : ?>
<div class="jt_testimonial-outer slide" data-slide-index="<?php echo $i; ?>">
<div class="jt_testimonial-block-slide">
<div class="testimonial_block">
<?php if (($params->get('Showavatar')==1)  && !empty($item->img)) : ?><div class="testimonial_block-image"> <img src="<?php echo $item->img; ?>" alt="<?php echo $item->name; ?>"> </div><?php endif;?>
<div class="testimonial_block-data">
<?php if (($params->get('Showname')==1)  && !empty($item->name)) : ?><div class="testimonial_block-name"><?php echo $item->name; ?></div><?php endif;?>
<?php if (($params->get('Showposition')==1)  && !empty($item->position)) : ?><div class="testimonial_block-position"><?php echo $item->position; ?></div><?php endif;?>
<?php if (($params->get('Showemail')==1)  && !empty($item->email)) : ?><div class="testimonial_block-email"><?php echo $item->email; ?></div><?php endif;?>
<?php if (($params->get('Showwebsite')==1)  && !empty($item->website)) : ?><div class="testimonial_block-website"><a href="<?php echo $item->website; ?>"><?php echo $item->website; ?></a></div><?php endif;?>
<?php if (    ($params->get('Showfacebook')==1)  || ($params->get('Showtwitter')==1)  || ($params->get('Showlinkedin')==1) || ($params->get('Showinstagram')==1) || ($params->get('Showpinterest')==1) || ($params->get('Showyoutube')==1)      ) : ?>
<div class="jt-social-icons">
<?php if (($params->get('Showfacebook')==1)  && !empty($item->facebook)) : ?><div class="testimonial_block-facebook"><a href="<?php echo $item->facebook; ?>"><i class="fa fa-facebook"></i></a></div><?php endif;?>
<?php if (($params->get('Showtwitter')==1)  && !empty($item->twitter)) : ?><div class="testimonial_block-twitter"><a href="<?php echo $item->twitter; ?>"><i class="fa-brands fa-x-twitter"></i></a></div><?php endif;?>
<?php if (($params->get('Showlinkedin')==1)  && !empty($item->linkedin)) : ?><div class="testimonial_block-linkedin"><a href="<?php echo $item->linkedin; ?>"><i class="fa fa-linkedin"></i></a></div><?php endif;?>
<?php if (($params->get('Showinstagram')==1)  && !empty($item->instagram)) : ?><div class="testimonial_block-instagram"><a href="<?php echo $item->instagram; ?>"><i class="fa fa-instagram"></i></a></div><?php endif;?>
<?php if (($params->get('Showpinterest')==1)  && !empty($item->pinterest)) : ?><div class="testimonial_block-pinterest"><a href="<?php echo $item->pinterest; ?>"><i class="fa fa-pinterest"></i></a></div><?php endif;?>
<?php if (($params->get('Showyoutube')==1)  && !empty($item->youtube)) : ?><div class="testimonial_block-youtube"><a href="<?php echo $item->youtube; ?>"><i class="fa fa-youtube"></i></a></div><?php endif;?>
</div>
<?php endif;?>
<?php if (($get_stars) && !empty($item->stars)) : ?>
<div class="rating">
<?php 
$stars = $item->stars;

switch ($stars) {
  case "star1":
    echo "
<span class='fa fa-star'></span>
<span class='fa fa-star-o'></span>
<span class='fa fa-star-o'></span>
<span class='fa fa-star-o'></span>
<span class='fa fa-star-o'></span>";
    break;
	
	 case "star15":
    echo "
<span class='fa fa-star'></span>
<span class='fa fa-star-half-o'></span>
<span class='fa fa-star-o'></span>
<span class='fa fa-star-o'></span>
<span class='fa fa-star-o'></span>";
    break;
	
  case "star2":
    echo "
<span class='fa fa-star'></span>
<span class='fa fa-star'></span>
<span class='fa fa-star-o'></span>
<span class='fa fa-star-o'></span>
<span class='fa fa-star-o'></span>";
    break;
	
	case "star25":
    echo "
<span class='fa fa-star'></span>
<span class='fa fa-star'></span>
<span class='fa fa-star-half-o'></span>
<span class='fa fa-star-o'></span>
<span class='fa fa-star-o'></span>";
    break;
	
	case "star3":
    echo "
<span class='fa fa-star'></span>
<span class='fa fa-star'></span>
<span class='fa fa-star'></span>
<span class='fa fa-star-o'></span>
<span class='fa fa-star-o'></span>";
    break;
	
	case "star35":
    echo "
<span class='fa fa-star'></span>
<span class='fa fa-star'></span>
<span class='fa fa-star'></span>
<span class='fa fa-star-half-o'></span>
<span class='fa fa-star-o'></span>";
    break;
	
	case "star4":
    echo "
<span class='fa fa-star'></span>
<span class='fa fa-star'></span>
<span class='fa fa-star'></span>
<span class='fa fa-star'></span>
<span class='fa fa-star-o'></span>";
    break;
	
	case "star45":
    echo "
<span class='fa fa-star'></span>
<span class='fa fa-star'></span>
<span class='fa fa-star'></span>
<span class='fa fa-star'></span>
<span class='fa fa-star-half-o'></span>";
    break;
	
	case "star5":
    echo "
<span class='fa fa-star'></span>
<span class='fa fa-star'></span>
<span class='fa fa-star'></span>
<span class='fa fa-star'></span>
<span class='fa fa-star'></span>";
    break;
	
  default:
    echo "<span class='fa fa-star-o'></span>
<span class='fa fa-star-o'></span>
<span class='fa fa-star-o'></span>
<span class='fa fa-star-o'></span>
<span class='fa fa-star-o'></span>";

}
?>
</div><?php endif;?>
<?php if (($params->get('ShowTestimonial')==1)  && !empty($item->testimonial)) : ?><div class="testimonial_block-text"><?php echo $item->testimonial; ?></div><?php endif;?>
</div>

</div>
</div>	
</div>
<?php $i++; // Increment counter
endforeach; ?>
</div>
</div>
<script defer type="text/javascript">
jQuery(document).ready(function() {

  var el = jQuery('.jt_testimonial<?php echo $modID;?>.owl2-carousel2');
  
  var carousel2;
  var carousel2Options = {
    margin: <?php echo $params->get('item_distance',6);?>,
    nav: <?php echo $params->get('dataNav')?>,
    dots: <?php echo $params->get('showDots');?>,
    slideBy: 'page',
	navText : ["<i class='<?php echo $params->get('prev_icon','fa fa-chevron-left')?>'></i>","<i class='<?php echo $params->get('next_icon','fa fa-chevron-right')?>'></i>"],
    responsive: {
      0: {
        items: <?php echo $params->get('column_of_items_mobp')?>,
        rows: <?php echo $params->get('row_of_items_mobp')?> //custom option not used by Owl Carousel, but used by the algorithm below
      },
      768: {
        items: <?php echo $params->get('column_of_items_tabp')?>,
        rows: <?php echo $params->get('row_of_items_tabp')?> //custom option not used by Owl Carousel, but used by the algorithm below
      },
      991: {
        items:<?php echo $params->get('column_of_items_tabl')?>,
        rows: <?php echo $params->get('row_of_items_tabl')?> //custom option not used by Owl Carousel, but used by the algorithm below
      },
	   1024: {
        items:<?php echo $params->get('column_of_items')?>,
        rows: <?php echo $params->get('row_of_items')?> //custom option not used by Owl Carousel, but used by the algorithm below
      }
    }
  };

  //Taken from Owl Carousel so we calculate width the same way
  var viewport = function() {
    var width;
    if (carousel2Options.responsiveBaseElement && carousel2Options.responsiveBaseElement !== window) {
      width = $(carousel2Options.responsiveBaseElement).width();
    } else if (window.innerWidth) {
      width = window.innerWidth;
    } else if (document.documentElement && document.documentElement.clientWidth) {
      width = document.documentElement.clientWidth;
    } else {
      console.warn('Can not detect viewport width.');
    }
    return width;
  };

  var severalRows = false;
  var orderedBreakpoints = [];
  for (var breakpoint in carousel2Options.responsive) {
    if (carousel2Options.responsive[breakpoint].rows > 1) {
      severalRows = true;
    }
    orderedBreakpoints.push(parseInt(breakpoint));
  }
  
  //Custom logic is active if carousel2 is set up to have more than one row for some given window width
  if (severalRows) {
    orderedBreakpoints.sort(function (a, b) {
      return b - a;
    });
    var slides = el.find('[data-slide-index]');
    var slidesNb = slides.length;
    if (slidesNb > 0) {
      var rowsNb;
      var previousRowsNb = undefined;
      var colsNb;
      var previousColsNb = undefined;

      //Calculates number of rows and cols based on current window width
      var updateRowsColsNb = function () {
        var width =  viewport();
        for (var i = 0; i < orderedBreakpoints.length; i++) {
          var breakpoint = orderedBreakpoints[i];
          if (width >= breakpoint || i == (orderedBreakpoints.length - 1)) {
            var breakpointSettings = carousel2Options.responsive['' + breakpoint];
            rowsNb = breakpointSettings.rows;
            colsNb = breakpointSettings.items;
            break;
          }
        }
      };

      var updateCarousel = function () {
        updateRowsColsNb();

        //Carousel is recalculated if and only if a change in number of columns/rows is requested
        if (rowsNb != previousRowsNb || colsNb != previousColsNb) {
          var reInit = false;
          if (carousel2) {
            //Destroy existing carousel2 if any, and set html markup back to its initial state
            carousel2.trigger('destroy.owl2.carousel2');
            carousel2 = undefined;
            slides = el.find('[data-slide-index]').detach().appendTo(el);
            el.find('.fake-col-wrapper').remove();
            reInit = true;
          }


          //This is the only real 'smart' part of the algorithm

          //First calculate the number of needed columns for the whole carousel2
          var perPage = rowsNb * colsNb;
          var pageIndex = Math.floor(slidesNb / perPage);
          var fakeColsNb = pageIndex * colsNb + (slidesNb >= (pageIndex * perPage + colsNb) ? colsNb : (slidesNb % colsNb));

          //Then populate with needed html markup
          var count = 0;
          for (var i = 0; i < fakeColsNb; i++) {
            //For each column, create a new wrapper div
            var fakeCol = $('<div class="fake-col-wrapper"></div>').appendTo(el);
            for (var j = 0; j < rowsNb; j++) {
              //For each row in said column, calculate which slide should be present
              var index = Math.floor(count / perPage) * perPage + (i % colsNb) + j * colsNb;
              if (index < slidesNb) {
                //If said slide exists, move it under wrapper div
                slides.filter('[data-slide-index=' + index + ']').detach().appendTo(fakeCol);
              }
              count++;
            }
          }
          //end of 'smart' part

          previousRowsNb = rowsNb;
          previousColsNb = colsNb;

          if (reInit) {
            //re-init carousel2 with new markup
            carousel2 = el.owl2Carousel(carousel2Options);
          }
        }
      };

      //Trigger possible update when window size changes
      $(window).on('resize', updateCarousel);

      //We need to execute the algorithm once before first init in any case
      updateCarousel();
    }
  }

  //init
  carousel2 = el.owl2Carousel(carousel2Options);
});
</script>