<?php 
defined('_JEXEC') or die;
use Joomla\CMS\HTML\HTMLHelper;

$images = ModJTImageGalleryHelper::getImages($params);
$groweffect = $params->get('grow_effect') ? 'groweffect' : '';
$itemsPerPage = (int)$params->get('itemsPerPage', 5);
$pages = array_chunk($images, $itemsPerPage);

// Column settings
$columnsDesktop = $params->get('columns', 4);
$columnsDesktop = 100 / $columnsDesktop;
$columnsTablet = $params->get('columns_fortablet', 2);
$columnsTablet = 100 / $columnsTablet ;

$columnsMobile = $params->get('columns_formobile', 1);
$columnsMobile = 100 / $columnsMobile;

$wa = $app->getDocument()->getWebAssetManager();

$css = "
ul.jt-image-gallery{$module->id} li {
    width: {$columnsDesktop}%;
    max-width: {$columnsDesktop}%;
}
ul.jt-image-gallery{$module->id} li .jt-img {
    margin: {$params->get('item_margin')};
    padding: {$params->get('item_padding')};
    border-radius: {$params->get('ImgBorderRadius')};
    border: {$params->get('ImageBorder')};
    overflow: hidden;
    position: relative;
}
a.gallery_item{$module->id}.grow {
    overflow: hidden!important;
}
.fancybox-skin.gallery{$module->id} {
    position: relative;
    background: {$params->get('fancybox_background')};
}
.fancybox-skin.gallery{$module->id} .fancybox-close,
.fancybox-skin.gallery{$module->id} .fancybox-prev span,
.fancybox-skin.gallery{$module->id} .fancybox-next span {
    background-color: {$params->get('fancybox_background')};
}
ul.jt-image-gallery{$module->id} {
    margin: {$params->get('item_margin')};
}

@media screen and (max-width: 1024px) {
  ul.jt-image-gallery{$module->id} li {
      width: {$columnsTablet}%;
      max-width: {$columnsTablet}%;
  }
}

@media screen and (max-width: 768px) {
  ul.jt-image-gallery{$module->id} li {
      width: {$columnsMobile}%;
      max-width: {$columnsMobile}%;
  }
}
";

$wa->addInlineStyle($css);
?>

<div class="mod-image-gallery" style="padding:<?php echo $params->get('module_padding'); ?>;">
   <div style="clear:both" class="jtimg-gallery-slide<?php echo $module->id ?>">
      <?php foreach ($pages as $key => $images): ?>
      <ul class="jt-image-gallery<?php echo $module->id ?> jt-img">
         <?php foreach ($images as $image) : ?>
         <li>
            <a href="<?php echo $image['url']; ?>" class="gallery_item<?php echo $module->id ?> jtimg-item <?php echo $groweffect; ?>" data-caption="<?php echo $image['title']; ?>" data-fancybox-group="gallery" target="_blank">
               
			   <figure class="jt-img"><span class="img-overlay"></span>
               <?php echo HTMLHelper::_('image', $image['thumb'], $image['title'], array('class' => 'mod-image-gallery-thumbnail')); ?></figure>
            </a>
         </li>
         <?php endforeach; ?>
      </ul>
      <?php endforeach; ?>
   </div>
   <div></div>
   <div style="clear:both"></div>
</div>

<script>
jQuery(document).ready(function($){	
    $('.gallery_item<?php echo $module->id ?>').fancybox({
        autoDimensions:   false,
        fitToView:false,
        autoSize : false,
        maxWidth:'100%',
        maxHeight:'100%',
		openEffect :'<?php echo $params->get('FancyboxNextEffect'); ?>',
		closeEffect  :'<?php echo $params->get('FancyboxCloseEffect'); ?>',
		nextEffect :'<?php echo $params->get('FancyboxNextEffect'); ?>',
		prevEffect  :'<?php echo $params->get('FancyboxPrevEffect'); ?>',
		openSpeed  :<?php echo $params->get('OpeningSpeed'); ?>,
		tpl:{wrap : '<div class="fancybox-wrap" tabIndex="-1"><div class="fancybox-skin gallery<?php echo $module->id ?>"><div class="fancybox-outer"><div class="fancybox-inner"></div></div></div></div>'},
        helpers: {
			overlay: { locked: false },
            title : { type : '<?php echo $params->get('Title_Type'); ?>' }
        },
        beforeShow : function() {
            $('.fancybox-wrap').addClass('mediaFancy');
        }
    });
});
</script>

<script>
jQuery(document).ready(function(){
	"use strict";	
    if (jQuery('.jtimg-gallery-slide<?php echo $module->id ?>').length) {
        jQuery('.jtimg-gallery-slide<?php echo $module->id ?>').slick({
            infinite: true,
            loop: true,
            pauseOnHover: <?php echo $params->get('pauseOnHover'); ?>,
            fade: <?php echo $params->get('fade'); ?>,
            draggable: <?php echo $params->get('draggable'); ?>,
            speed: <?php echo $params->get('speed'); ?>,
            autoplay: <?php echo $params->get('autoplay'); ?>,
            dots: <?php echo $params->get('dots'); ?>,
            dotsClass: '<?php echo $params->get('dotsClass'); ?>',
            autoplaySpeed: <?php echo $params->get('autoplaySpeed'); ?>,
            arrows: <?php echo $params->get('arrows'); ?>,
            swipe: <?php echo $params->get('swipe'); ?>,
            vertical: <?php echo $params->get('vertical'); ?>,
            verticalSwiping: <?php echo $params->get('verticalSwiping'); ?>,
            slidesToShow: 1,
        });
    }
});
</script>


