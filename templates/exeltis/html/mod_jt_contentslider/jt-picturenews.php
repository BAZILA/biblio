<?php
/**
 * @package     mod_jt_contentslider
 * @copyright   Copyright (C) 2007 - 2021 http://www.joomlatema.net, Inc. All rights reserved.
 * @license		http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 * @author     	JoomlaTema.Net
 * @link 		http://www.joomlatema.net
 **/

defined('_JEXEC') or die;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Router\Route;
use Joomla\Module\JTContentSlider\Site\Helper\JTContentSliderHelper;

?>
<style type="text/css">.tooltip-inner { max-width:150px!important; padding-top:7px; padding-bottom:7px; font-size:13px}</style>
<div class="jtcs_item_wrapper jt-cs exeltis picturenews" style="padding:<?php echo $params->get('content_padding');?>;">	
<?php if( $params->get('show_pretext')==1 ): ?>
<div class="jt-pretext">
<span class="pretext_title"><?php echo $params->get('pretext_title');?></span>
<p class="pretext"><?php echo $params->get('pretext');?></p>
</div>
<?php endif; ?>
<div class="jtcs<?php echo $module->id; ?> <?php echo $params->get('NavPosition'); ?> owl-carousel owl-carousel owl-theme slides_container">
<?php 
	$n = 1;
	$morecatlinks = array();
	foreach ($list as $i =>  $item) : 
		// Check whether thumbnail image is exist or not. If it's not then start thumbnail generation process
		
			// More Category List and Blog
			$item->categlist = Route::_('index.php?option=com_content&view=category&id='.$item->catid);
			// Get the thumbnail 
			$thumb_img = JTContentSliderHelper::getThumbnail($item->id, $item->images,$thumb_folder,$show_default_thumb,$thumb_width,$thumb_height,$item->title,$item->introtext,$modulebase);
			$org_img = JTContentSliderHelper::getOrgImage($item->id, $item->images,$item->title,$item->introtext,$modulebase);
			$caption_text = JTContentSliderHelper::getCaption($item->id, $item->images,$item->introtext,$use_caption);
			?>
			 <div class="slide no-border-bottom"  data-slide-index="<?php echo $i; ?>">
			 <div class="jt-inner">
			 <?php if( $params->get('show_thumbnail') ==1): ?>
				<div class="jt-imagecover" style="float:<?php echo $params->get('thumb_align');?>;margin<?php if($params->get('thumb_align')=="left") {echo "-right";} else if($params->get('thumb_align')=="right"){echo "-left";} else echo "-bottom";?>:<?php echo $params->get('thumb_margin');?>">
				<?php if($params->get('link_image')== 1):?>	
				<a href="<?php echo $item->link;?>" class="link-image tooltip-exeltis"   data-html="true" data-container="body"  title="<?php echo $item->title;?>"><div class="picturenews-imagecover"><i class="fa fa-file"></i></div><?php echo $thumb_img;?> </a>
				<?php else :?>
				<a href="<?php echo $item->link;?>" class="link-image tooltip-exeltis"   data-html="true" data-container="body"  title="<?php echo $item->title;?>"><div class="picturenews-imagecover"><i class="fa fa-file"></i></div><?php echo $thumb_img;?> </a>
				<?php endif; ?>
		</div>
		<?php if($params->get('use_caption')== 1 &&  !empty($caption_text) ):?><span class="jt-caption"><?php echo $caption_text;?></span><?php endif; ?>

		<?php endif; ?>

				<?php if($params->get('show_category')== 1):?>	
				<?php if($params->get('show_category_link')== 1):?>	
				<span class="jt-category">
				<a href="<?php echo Route::_('index.php?option=com_content&view=category&id='.$item->catid);?>" class="cat-link"><?php echo $item->category_title;?></a>
				</span>
				<?php else :?>
				<span class="jt-category">
				<?php echo $item->category_title;?>
				</span>
				<?php endif; ?>
				<?php endif; ?>
		<?php if($params->get('show_title')== 1):?>	
				<a class="jt-title" href="<?php echo $item->link; ?>" itemprop="url">
					<?php 
					$item->title =  HTMLHelperString::truncate($item->title, $limit_title,false,true); 
					$item->title= str_replace('...', $params->get('replacer',''), $item->title);
					echo $item->title ?>
				</a>
				<?php endif; ?>
				<?php if(($params->get('show_date')== 1) || ($params->get('show_author')== 1)):?>	
				<div class="jt-author-date">
				<?php if($params->get('show_date')== 1):?>	
				<span class="jtcs_item_date">
				<?php
					//show date
					JTContentSliderHelper::getDate($show_date, $show_date_type, $item->created, $custom_date_format);
				?>
				</span>
				<?php endif; ?>
				<?php if($params->get('show_author')== 1):?>	
				<span class="jtcs_item_author">	
				<?php echo $item->author;?>
				<?php endif; ?>
				</span>
				</div>
				<?php endif; ?>
				<?php if($params->get('show_introtext')== 1):?>		
				<div class="jtcs_item_introtext">
					<?php 					
					$item->introtext =  HTMLHelperString::truncate(strip_tags($item->introtext), $introtext_truncate,false,true);  
					$item->introtext= str_replace('...', $params->get('replacer_text',''), $item->introtext);
					echo $item->introtext ?>
					
				</div>
				<?php endif; ?>
				<?php if($params->get('showReadmore')== 1):?>
					<p class="readmore">
						<a class="btn btn-primary bt-readmore" target="<?php echo $openTarget; ?>"
							title="<?php echo $item->title;?>"
							href="<?php echo $item->link;?>"><i class="fa fa-file-text-o" aria-hidden="true">&nbsp;</i> <?php echo JText::_('JTCS_READ_MORE');?>
						</a>
					</p>
					<?php endif; ?>
<div></div><div style="clear:both"></div></div></div>
		<?php 
		// More category links
		$morecatlink = "<a href=".Route::_('index.php?option=com_content&view=category&id='.$item->catid).">".$item->category_title."</a>";
		$morecatlinks[$morecatlink] = true;
		if (isset($morecatlinks[$morecatlink])) {
			continue;
		}
	endforeach; ?></div>
<?php 
if($show_morecat_links) 
{	echo "<div class='jtcs_more_cat'>".$params->get('morein_text'). " ";
	foreach ($morecatlinks as $morecatlink => $val) 
	{
		echo $morecatlink. '&nbsp;&nbsp;';
	}
	echo "</div>";
}
?>
</div>
<style type="text/css">.jtcs<?php echo $module->id; ?>.owl-carousel .owl-nav{justify-content:<?php echo $params->get('NavAlignment'); ?>}
.jtcs<?php echo $module->id; ?>.positiontop.owl-carousel .owl-nav{width:100%;position:absolute;top:<?php echo $params->get('NavTopPos'); ?>; bottom:auto;justify-content:<?php echo $params->get('NavAlignment'); ?>;gap:0 10px;}
.jtcs<?php echo $module->id; ?>.positioncenter.owl-carousel .owl-nav{width:100%;position:absolute;top:50%; bottom:auto;transform:translateY(-50%);justify-content:space-between; z-index:-1;}
.jtcs<?php echo $module->id; ?>.positionbottom.owl-carousel .owl-nav{width:100%;position:absolute;top:auto; bottom:<?php echo $params->get('NavBotPos'); ?>;justify-content:<?php echo $params->get('NavAlignment'); ?>;gap:0 10px;}
.jtcs<?php echo $module->id; ?> .owl-dots {position: relative;bottom:<?php echo $params->get('DotsBottomPos'); ?>;;}
.jtcs<?php echo $module->id; ?>.owl-carousel .owl-nav.disabled{ display:none}
</style>
<script defer type="text/javascript">
jQuery(document).ready(function() {
  var el = jQuery('.jtcs<?php echo $module->id; ?>.owl-carousel');
  var carousel;
  var carouselOptions = {
    margin: <?php echo $params->get('marginRight');?>,
    stagePadding: <?php echo $params->get('stagePadding');?>,
	center: <?php echo $params->get('centerItems');?>,
	loop: <?php echo $params->get('infiniteLoop');?>,
    nav: <?php echo $params->get('show_navigation');?>,
    dots: <?php echo $params->get('showDots');?>,
	mouseDrag:<?php echo $params->get('mouseDrag');?>,
	rtl: <?php echo $params->get('rtl');?>,
    slideBy: '<?php echo $params->get('slideBy');?>',
	autoplay:<?php echo $params->get('autoPlay');?>,
	autoplaySpeed:<?php echo $params->get('autoplaySpeed');?>,
	smartSpeed:<?php echo $params->get('smartSpeed');?>,
	autoplayTimeout:<?php echo $params->get('autoplayTimeout');?>,
	autoplayHoverPause:<?php echo $params->get('PauseOnHover');?>,
    responsive: {
      0: {
        items: <?php echo $params->get('slideColumnxs');?>,
        rows: <?php echo $params->get('slideRowxs');?> //custom option not used by Owl Carousel, but used by the algorithm below
      },
      768: {
        items: <?php echo $params->get('slideColumnsm');?>,
        rows:<?php echo $params->get('slideRowsm');?>//custom option not used by Owl Carousel, but used by the algorithm below
      },
      991: {
        items:<?php echo $params->get('slideColumn');?>,
        rows:<?php echo $params->get('slideRow');?> //custom option not used by Owl Carousel, but used by the algorithm below
      }
    }
  };

  //Taken from Owl Carousel so we calculate width the same way
  var viewport = function() {
    var width;
    if (carouselOptions.responsiveBaseElement && carouselOptions.responsiveBaseElement !== window) {
      width = jQuery(carouselOptions.responsiveBaseElement).width();
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
  for (var breakpoint in carouselOptions.responsive) {
    if (carouselOptions.responsive[breakpoint].rows > 1) {
      severalRows = true;
    }
    orderedBreakpoints.push(parseInt(breakpoint));
  }
  
  //Custom logic is active if carousel is set up to have more than one row for some given window width
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
            var breakpointSettings = carouselOptions.responsive['' + breakpoint];
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
          if (carousel) {
            //Destroy existing carousel if any, and set html markup back to its initial state
            carousel.trigger('destroy.owl.carousel');
            carousel = undefined;
            slides = el.find('[data-slide-index]').detach().appendTo(el);
            el.find('.fake-col-wrapper').remove();
            reInit = true;
          }


          //This is the only real 'smart' part of the algorithm

          //First calculate the number of needed columns for the whole carousel
          var perPage = rowsNb * colsNb;
          var pageIndex = Math.floor(slidesNb / perPage);
          var fakeColsNb = pageIndex * colsNb + (slidesNb >= (pageIndex * perPage + colsNb) ? colsNb : (slidesNb % colsNb));

          //Then populate with needed html markup
          var count = 0;
          for (var i = 0; i < fakeColsNb; i++) {
            //For each column, create a new wrapper div
            var fakeCol = jQuery('<div class="fake-col-wrapper"></div>').appendTo(el);
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
            //re-init carousel with new markup
            carousel = el.owlCarousel(carouselOptions);
          }
        }
      };

      //Trigger possible update when window size changes
      jQuery(window).on('resize', updateCarousel);

      //We need to execute the algorithm once before first init in any case
      updateCarousel();
    }
  }

  //init
  carousel = el.owlCarousel(carouselOptions);
});
</script>

<script>
lightbox.option({
    fadeDuration:<?php echo $params->get('fadeDuration',300);?>,
    fitImagesInViewport:<?php echo $params->get('fitImagesInViewport',true);?>,
    imageFadeDuration: <?php echo $params->get('imageFadeDuration',300);?>,
    positionFromTop: <?php echo $params->get('positionFromTop');?>,
    resizeDuration: <?php echo $params->get('resizeDuration',150);?>,
	  })
</script>
<script>jQuery('.tooltip-exeltis').tooltip({ boundary: 'window' })</script>