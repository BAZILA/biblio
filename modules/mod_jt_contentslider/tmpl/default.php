<?php
/**
 * @package     mod_jt_contentslider
 * @copyright   Copyright (C) http://www.joomlatema.net, Inc. All rights reserved.
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 * @author      JoomlaTema.Net
 * @link        http://www.joomlatema.net
 */

defined('_JEXEC') or die;

use Joomla\CMS\Router\Route;
use Joomla\CMS\Language\Text;
use Joomla\Module\JTContentSlider\Site\Helper\JTContentSliderHelper;

if ($params->get('autoPlay') == 'true') {
    $autoplayscreenTimeout = $params->get('autoplayTimeout');
} else {
    $autoplayscreenTimeout = 9900000000000;
}

// Sanitize navigation text (allow icon HTML tags)
$navTextLeft  = json_encode(JTContentSliderHelper::safeIconHtml($params->get('navTextLeft',  '<i class="fas fa-angle-left"></i>')));
$navTextRight = json_encode(JTContentSliderHelper::safeIconHtml($params->get('navTextRight', '<i class="fas fa-angle-right"></i>')));
?>
<div class="jtcs_item_wrapper jt-cs" style="padding:<?php echo JTContentSliderHelper::safeHtml($params->get('content_padding')); ?>;">
<?php if( $params->get('show_pretext')==1 ): ?>
<div class="jt-pretext">
<span class="pretext_title"><?php echo $params->get('pretext_title');?></span>
<p class="pretext"><?php echo $params->get('pretext');?></p>
</div>
<?php endif; ?>

<div class="jtcs<?php echo $module->id; ?> <?php echo JTContentSliderHelper::safeHtml($params->get('NavPosition')); ?> owl-carousel owl-theme slides_container" 
     role="region" 
     aria-label="<?php echo Text::_('MOD_JTCS_CAROUSEL_LABEL', true); ?>"
     aria-live="polite">
<?php 
    $n = 1;
    $morecatlinks = array();
    foreach ($list as $i => $item):
        // More Category List and Blog
        $item->categlist = Route::_('index.php?option=com_content&view=category&id=' . $item->catid);

        // Get the thumbnail 
        $thumb_img = JTContentSliderHelper::getThumbnail(
            $item->id,
            $item->images,
            $thumb_folder,
            $show_default_thumb,
            $thumb_width,
            $thumb_height,
            $item->title,
            $item->introtext,
            $modulebase
        );
        $org_img = JTContentSliderHelper::getOrgImage(
            $item->id,
            $item->images,
            $item->title,
            $item->introtext,
            $modulebase
        );
        $caption_text = JTContentSliderHelper::getCaption(
            $item->id,
            $item->images,
            $item->introtext,
            $use_caption
        );
?>
<div class="slide" 
     style="padding:<?php echo JTContentSliderHelper::safeHtml($params->get('article_block_padding')); ?>;margin:<?php echo JTContentSliderHelper::safeHtml($params->get('article_block_margin')); ?>" 
     data-slide-index="<?php echo $i; ?>"
     role="group"
     aria-roledescription="slide"
     aria-label="<?php echo Text::sprintf('MOD_JTCS_SLIDE_LABEL', $i + 1, count($list)); ?>">
    <div class="jt-inner">
    <?php if ($params->get('show_thumbnail') == 1): ?>
        <div class="jt-imagecover" style="float:<?php echo JTContentSliderHelper::safeHtml($params->get('thumb_align')); ?>;margin<?php 
            if ($params->get('thumb_align') == "left") {
                echo "-right";
            } elseif ($params->get('thumb_align') == "right") {
                echo "-left";
            } else {
                echo "-bottom";
            }
        ?>:<?php echo JTContentSliderHelper::safeHtml($params->get('thumb_margin')); ?>">
        <?php if ($params->get('link_image') == 1): ?>
            <a class="link-image" title="<?php echo htmlspecialchars($item->title, ENT_QUOTES, 'UTF-8'); ?>" href="<?php echo $item->link; ?>" aria-label="<?php echo htmlspecialchars(Text::sprintf('MOD_JTCS_VIEW_ARTICLE', $item->title), ENT_QUOTES, 'UTF-8'); ?>"><?php echo $thumb_img; ?></a>
        <?php else: ?>
            <?php echo $thumb_img; ?>
        <?php endif; ?>
        <?php if ($params->get('hover_icons') == 1): ?>

            <div class="hover-icons" role="group" aria-label="<?php echo Text::_('MOD_JTCS_IMAGE_ACTIONS'); ?>">
                <a class="jt-icon icon-url" 
                   title="<?php echo htmlspecialchars($item->title, ENT_QUOTES, 'UTF-8'); ?>" 
                   href="<?php echo $item->link; ?>"
                   aria-label="<?php echo htmlspecialchars(Text::sprintf('MOD_JTCS_VIEW_ARTICLE', $item->title), ENT_QUOTES, 'UTF-8'); ?>">
                    <i class="fa fa-link" aria-hidden="true"></i>
                    <span class="sr-only"><?php echo Text::_('MOD_JTCS_VIEW_ARTICLE_LINK'); ?></span>
                </a>
                <a class="jt-icon icon-lightbox jt-image-link" 
                   href="<?php echo htmlspecialchars($org_img, ENT_QUOTES, 'UTF-8'); ?>" 
                   data-lightbox="jt-1"
                   aria-label="<?php echo htmlspecialchars(Text::sprintf('MOD_JTCS_VIEW_IMAGE', $item->title), ENT_QUOTES, 'UTF-8'); ?>">
                    <i class="fa fa-search" aria-hidden="true"></i>
                    <span class="sr-only"><?php echo Text::_('MOD_JTCS_VIEW_FULL_IMAGE'); ?></span>
                </a>
            </div>
        <?php endif; ?>
        <?php if ($params->get('use_caption') == 1 && !empty($caption_text)): ?>
            <span class="jt-caption"><?php echo htmlspecialchars($caption_text, ENT_QUOTES, 'UTF-8'); ?></span>
        <?php endif; ?>
        </div>
    <?php endif; ?>
    
    <?php if ($params->get('show_category') == 1): ?>
        <?php if ($params->get('show_category_link') == 1): ?>
            <span class="jt-category">
                <a href="<?php echo Route::_('index.php?option=com_content&view=category&id=' . $item->catid); ?>" class="cat-link">
                    <?php if ($params->get('ShowCategoryIcon') == 1): ?>
                        <i class="<?php echo JTContentSliderHelper::safeHtml($params->get('CategoryIcon')); ?>" aria-hidden="true"></i>
                    <?php endif; ?>
                    <?php echo htmlspecialchars($item->category_title, ENT_QUOTES, 'UTF-8'); ?>
                </a>
            </span>
        <?php else: ?>
            <span class="jt-category">
                <?php if ($params->get('ShowCategoryIcon') == 1): ?>
                    <i class="<?php echo JTContentSliderHelper::safeHtml($params->get('CategoryIcon')); ?>" aria-hidden="true"></i>
                <?php endif; ?>
                <?php echo htmlspecialchars($item->category_title, ENT_QUOTES, 'UTF-8'); ?>
            </span>
        <?php endif; ?>
    <?php endif; ?>
    
    <?php if ($params->get('show_title') == 1): ?>
        <<?php echo JTContentSliderHelper::safeHtml($params->get('TitleClass')); ?>>
            <a class="jt-title" href="<?php echo $item->link; ?>" itemprop="url">
                <?php 
                if ($limit_title_by == 'word' && $limit_title > 0) {
                    $limit_title = (int)$limit_title;
                    $item->title = JTContentSliderHelper::substrword($item->title, $strip_tags, $allowed_tags, $replacertitle, $limit_title);
                } elseif ($limit_title_by == 'char' && $limit_title > 0) {
                    $limit_title = (int)$limit_title;
                    $item->title = JTContentSliderHelper::substring($item->title, $strip_tags, $allowed_tags, $replacertitle, $limit_title);
                }
                echo htmlspecialchars($item->title, ENT_QUOTES, 'UTF-8');
                ?>
            </a>
        </<?php echo JTContentSliderHelper::safeHtml($params->get('TitleClass')); ?>>
    <?php endif; ?>
    
    <?php if (($params->get('show_date') == 1) || ($params->get('show_author') == 1) || ($params->get('show_hits') == 1)): ?>
        <div class="jt-author-date">
        <?php if ($params->get('show_date') == 1): ?>
            <?php if ($params->get('ShowDateIcon') == 1): ?>
                <i class="<?php echo JTContentSliderHelper::safeHtml($params->get('DateIcon')); ?>" aria-hidden="true"></i>
            <?php endif; ?>
            <span class="jt-date">
                <?php JTContentSliderHelper::getDate($show_date, $show_date_type, $item->created, $custom_date_format); ?>
            </span>
        <?php endif; ?>
        <?php if ($params->get('show_author') == 1): ?>
            <span class="jt-author">
                <?php if ($params->get('ShowAuthorIcon') == 1): ?>
                    <i class="<?php echo JTContentSliderHelper::safeHtml($params->get('AuthorIcon')); ?>" aria-hidden="true"></i>
                <?php endif; ?>
                <?php echo htmlspecialchars($item->author, ENT_QUOTES, 'UTF-8'); ?>
            </span>
        <?php endif; ?>
        <?php if ($params->get('show_hits') == 1): ?>
            <span class="jt-hits">
                <?php if ($params->get('ShowHitIcon') == 1): ?>
                    <i class="<?php echo JTContentSliderHelper::safeHtml($params->get('HitIcon')); ?>" aria-hidden="true"></i>
                <?php endif; ?>
                <?php echo Text::sprintf('COM_CONTENT_ARTICLE_HITS', $item->hits); ?>
            </span>
        <?php endif; ?>
        </div>
    <?php endif; ?>
    
    <?php if ($params->get('show_introtext') == 1): ?>
        <div class="jt-introtext">
            <?php
            if ($limit_intro_by == 'word' && $introtext_truncate > 0) {
                $item->introtext = JTContentSliderHelper::substrword($item->introtext, $strip_tags, $allowed_tags, $replacer_text, $introtext_truncate);
                echo $item->introtext;
            } elseif ($limit_intro_by == 'char' && $introtext_truncate > 0) {
                $item->introtext = JTContentSliderHelper::substring($item->introtext, $strip_tags, $allowed_tags, $replacer_text, $introtext_truncate);
                echo $item->introtext;
            }
            ?>
        </div>
    <?php endif; ?>
    
    <?php if ($params->get('showReadmore') == 1): ?>
        <p class="jt-readmore">
            <a class="btn btn-primary jt-readmore" target="<?php echo htmlspecialchars($openTarget, ENT_QUOTES, 'UTF-8'); ?>"
                title="<?php echo htmlspecialchars($item->title, ENT_QUOTES, 'UTF-8'); ?>"
                href="<?php echo $item->link; ?>">
                <i class="<?php echo JTContentSliderHelper::safeHtml($params->get('ReadMoreIcon')); ?>" aria-hidden="true">&nbsp;</i>
                <?php echo JTContentSliderHelper::safeHtml($params->get('ReadMoreText', 'Read More')); ?>
            </a>
        </p>
    <?php endif; ?>
    <div></div>
    <div style="clear:both"></div>
    </div>
</div>
<?php 
        // More category links
        $morecatlink = "<a href=" . Route::_('index.php?option=com_content&view=category&id=' . $item->catid) . ">" . htmlspecialchars($item->category_title, ENT_QUOTES, 'UTF-8') . "</a>";
        if (!isset($morecatlinks[$morecatlink])) {
            $morecatlinks[$morecatlink] = true;
        }
    endforeach; 
?></div>
<?php 
if ($show_morecat_links) {
    echo "<div class='jtcs_more_cat'>" . JTContentSliderHelper::safeHtml($params->get('morein_text', 'More In')) . " ";
    foreach ($morecatlinks as $morecatlink => $val) {
        echo $morecatlink . '&nbsp;&nbsp;';
    }
    echo "</div>";
}
?>
</div>
<style type="text/css">
/* Existing styles */
.jtcs<?php echo $module->id; ?>.owl-carousel .owl-nav{justify-content:<?php echo JTContentSliderHelper::safeHtml($params->get('NavAlignment')); ?>}
.jtcs<?php echo $module->id; ?>.positiontop.owl-carousel .owl-nav{width:100%;position:absolute;top:<?php echo JTContentSliderHelper::safeHtml($params->get('NavTopPos')); ?>; bottom:auto;justify-content:<?php echo JTContentSliderHelper::safeHtml($params->get('NavAlignment')); ?>;gap:0 10px;}
.jtcs<?php echo $module->id; ?>.positioncenter.owl-carousel .owl-nav{width:100%;position:absolute;top:50%; bottom:auto;transform:translateY(-50%);justify-content:space-between; z-index:1; height:0px;}
.jtcs<?php echo $module->id; ?>.positionbottom.owl-carousel .owl-nav{width:100%;position:absolute;top:auto; bottom:<?php echo JTContentSliderHelper::safeHtml($params->get('NavBotPos')); ?>;justify-content:<?php echo JTContentSliderHelper::safeHtml($params->get('NavAlignment')); ?>;gap:0 10px;}
.jtcs<?php echo $module->id; ?> .owl-dots {position: relative;bottom:<?php echo JTContentSliderHelper::safeHtml($params->get('DotsBottomPos')); ?>;}
.jtcs<?php echo $module->id; ?>.owl-carousel .owl-nav.disabled{ display:none}
.jtcs<?php echo $module->id; ?>.owl-carousel .jt-introtext{ text-align:<?php echo JTContentSliderHelper::safeHtml($params->get('IntroTextAlign')); ?>}
/* ADA Compliance: Screen reader only text */
.sr-only {position: absolute;width: 1px;height: 1px;padding: 0;margin: -1px;overflow: hidden;clip: rect(0, 0, 0, 0);white-space: nowrap;border-width: 0;}
/* ADA Compliance: Focus visible styles - only show on keyboard navigation, not mouse clicks */
.jtcs<?php echo $module->id; ?> a:focus-visible,
.jtcs<?php echo $module->id; ?> button:focus-visible,
.jtcs<?php echo $module->id; ?> .owl-nav button:focus-visible,
.jtcs<?php echo $module->id; ?> .owl-dot:focus-visible {outline: 3px solid #4A90E2;outline-offset: 2px;box-shadow: 0 0 0 3px rgba(74, 144, 226, 0.3);}
/* Remove default focus outline for mouse users */
.jtcs<?php echo $module->id; ?> a:focus:not(:focus-visible),
.jtcs<?php echo $module->id; ?> button:focus:not(:focus-visible),
.jtcs<?php echo $module->id; ?> .owl-nav button:focus:not(:focus-visible),
.jtcs<?php echo $module->id; ?> .owl-dot:focus:not(:focus-visible) {outline: none;box-shadow: none;}
/* Fallback for browsers without :focus-visible support */
.jtcs<?php echo $module->id; ?> a.keyboard-focus,
.jtcs<?php echo $module->id; ?> button.keyboard-focus,
.jtcs<?php echo $module->id; ?> .owl-nav button.keyboard-focus,
.jtcs<?php echo $module->id; ?> .owl-dot.keyboard-focus {outline: 3px solid #4A90E2 !important;outline-offset: 2px;box-shadow: 0 0 0 3px rgba(74, 144, 226, 0.3) !important;}
/* ADA Compliance: Ensure focused slide content is visible */
.jtcs<?php echo $module->id; ?> .slide:focus-within {scroll-margin: 20px;}
/* ADA Compliance: Better button visibility and sizing for touch targets */
.jtcs<?php echo $module->id; ?> .owl-nav button {min-width: 44px;min-height: 44px;display: flex;align-items: center;justify-content: center;}
.jtcs<?php echo $module->id; ?> .owl-dot {min-width: 44px;min-height: 44px;}
/* ADA Compliance: Ensure keyboard focus visibility in carousel navigation */
.jtcs<?php echo $module->id; ?> .owl-nav button:focus-visible {background-color: rgba(74, 144, 226, 0.2) !important;}
/* ADA Compliance: Show nav buttons whenever keyboard focus is anywhere inside
   the carousel — Owl hides them with opacity:0 by default until hover,
   so keyboard users would never see them without this rule. */
.jtcs<?php echo $module->id; ?>:focus-within .owl-nav button,
.jtcs<?php echo $module->id; ?>.keyboard-focused .owl-nav button {opacity: 1 !important;visibility: visible !important;}
</style>
<script defer type="text/javascript">
jQuery(document).ready(function() {
  // Focus-visible polyfill: Track if user is using keyboard
  var isUsingKeyboard = false;
  
  jQuery(document).on('mousedown', function() {
    isUsingKeyboard = false;
  });
  
  jQuery(document).on('keydown', function(e) {
    // Tab, Shift, arrows, Enter, Space indicate keyboard usage
    if (e.key === 'Tab' || e.key === 'Enter' || e.key === ' ' || 
        e.key === 'ArrowLeft' || e.key === 'ArrowRight' || 
        e.key === 'ArrowUp' || e.key === 'ArrowDown') {
      isUsingKeyboard = true;
    }
  });
  
  // Add/remove keyboard-user class for browsers without :focus-visible support
  jQuery(document).on('focus', '.jtcs<?php echo $module->id; ?> a, .jtcs<?php echo $module->id; ?> button, .jtcs<?php echo $module->id; ?> .owl-nav button, .jtcs<?php echo $module->id; ?> .owl-dot', function() {
    if (isUsingKeyboard) {
      jQuery(this).addClass('keyboard-focus');
    } else {
      jQuery(this).removeClass('keyboard-focus');
    }
  });
  
  jQuery(document).on('blur', '.jtcs<?php echo $module->id; ?> a, .jtcs<?php echo $module->id; ?> button, .jtcs<?php echo $module->id; ?> .owl-nav button, .jtcs<?php echo $module->id; ?> .owl-dot', function() {
    jQuery(this).removeClass('keyboard-focus');
  });

  var el = jQuery('.jtcs<?php echo $module->id; ?>.owl-carousel');
  var carousel;
  var carouselOptions = {
    margin: <?php echo (int)$params->get('marginRight', 20); ?>,
    stagePadding: <?php echo (int)$params->get('stagePadding', 0); ?>,
    center: <?php echo $params->get('centerItems') == 'true' ? 'true' : 'false'; ?>,
    loop: <?php echo $params->get('infiniteLoop') == 'true' ? 'true' : 'false'; ?>,
    nav: <?php echo $params->get('show_navigation') == 'true' ? 'true' : 'false'; ?>,
    navText:[<?php echo $navTextLeft; ?>,<?php echo $navTextRight; ?>],
    dots: <?php echo $params->get('showDots') == 'true' ? 'true' : 'false'; ?>,
    rtl: <?php echo $params->get('rtl') == 'true' ? 'true' : 'false'; ?>,
    slideBy: '<?php echo JTContentSliderHelper::safeHtml($params->get('slideBy', 'page')); ?>',
    autoplay:<?php echo $params->get('autoPlay') == 'true' ? 'true' : 'false'; ?>,
    autoplaySpeed:<?php echo (int)$params->get('autoplaySpeed', 300); ?>,
    smartSpeed:<?php echo (int)$params->get('smartSpeed', 300); ?>,
    autoplayTimeout:<?php echo (int)$params->get('autoplayTimeout', 4000); ?>,
    autoplayHoverPause:<?php echo $params->get('PauseOnHover') == 'true' ? 'true' : 'false'; ?>,
    mouseDrag: <?php echo $params->get('mouseDrag') == 'true' ? 'true' : 'false'; ?>,
    touchDrag: <?php echo $params->get('touchDrag') == 'true' ? 'true' : 'false'; ?>,
    navSpeed:<?php echo (int)$params->get('navSpeed', 600); ?>,
    dotsSpeed:<?php echo (int)$params->get('dotsSpeed', 600); ?>,
    responsive: {
      0: {
        autoplay:<?php echo $params->get('autoPlay') == 'true' ? 'true' : 'false'; ?>,
        autoplaySpeed:<?php echo (int)$params->get('autoplaySpeed', 300); ?>,
        smartSpeed:<?php echo (int)$params->get('smartSpeed', 300); ?>,
        autoplayTimeout:<?php echo (int)$autoplayscreenTimeout; ?>,
        items: <?php echo (int)$params->get('slideColumnxs', 1); ?>,
        rows: <?php echo (int)$params->get('slideRowxs', 4); ?>
      },
      768: {
        autoplay:<?php echo $params->get('autoPlay') == 'true' ? 'true' : 'false'; ?>,
        autoplaySpeed:<?php echo (int)$params->get('autoplaySpeed', 300); ?>,
        smartSpeed:<?php echo (int)$params->get('smartSpeed', 300); ?>,
        autoplayTimeout:<?php echo (int)$autoplayscreenTimeout; ?>,
        items: <?php echo (int)$params->get('slideColumnsm', 2); ?>,
        rows:<?php echo (int)$params->get('slideRowsm', 3); ?>
      },
      991: {
        autoplay:<?php echo $params->get('autoPlay') == 'true' ? 'true' : 'false'; ?>,
        autoplaySpeed:<?php echo (int)$params->get('autoplaySpeed', 300); ?>,
        smartSpeed:<?php echo (int)$params->get('smartSpeed', 300); ?>,
        autoplayTimeout:<?php echo (int)$autoplayscreenTimeout; ?>,
        items:<?php echo (int)$params->get('slideColumn', 3); ?>,
        rows:<?php echo (int)$params->get('slideRow', 1); ?>
      }
    },
    // ADA Compliance callbacks
onInitialized: function(event) {
      addAccessibilityAttributes();
      updateOffscreenTabindex();
    },
    onChanged: function(event) {
      addAccessibilityAttributes();
      updateOffscreenTabindex();
    },
    onTranslated: function(event) {
      addAccessibilityAttributes();
      updateOffscreenTabindex();
    }
  };

  // ADA Compliance: Add proper ARIA labels to nav buttons and dots
  function addAccessibilityAttributes() {
    var carousel = jQuery('.jtcs<?php echo $module->id; ?>');
    var prevBtn = carousel.find('.owl-prev');
    var nextBtn = carousel.find('.owl-next');
    var dots    = carousel.find('.owl-dot');

    if (prevBtn.length) {
      prevBtn.attr({
        'aria-label': '<?php echo Text::_('MOD_JTCS_PREVIOUS_SLIDE', true); ?>',
        'role': 'button',
        'tabindex': '0'
      });
      // Owl adds class="disabled" (not the HTML disabled attribute) at boundaries
      // — keep it focusable so Tab reaches it; aria-disabled conveys the state
      if (prevBtn.hasClass('disabled')) {
        prevBtn.attr('aria-disabled', 'true');
      } else {
        prevBtn.removeAttr('aria-disabled');
      }
    }

    if (nextBtn.length) {
      nextBtn.attr({
        'aria-label': '<?php echo Text::_('MOD_JTCS_NEXT_SLIDE', true); ?>',
        'role': 'button',
        'tabindex': '0'
      });
      if (nextBtn.hasClass('disabled')) {
        nextBtn.attr('aria-disabled', 'true');
      } else {
        nextBtn.removeAttr('aria-disabled');
      }
    }

    dots.each(function(index) {
      jQuery(this).attr({
        'aria-label': '<?php echo Text::_('MOD_JTCS_GO_TO_SLIDE', true); ?> ' + (index + 1),
        'role': 'button',
        'tabindex': '0'
      });
    });

    // Mark the first visible active slide
    var activeSlide = carousel.find('.owl-item.active .slide');
    carousel.find('.slide').removeAttr('aria-current');
    if (activeSlide.length) {
      activeSlide.first().attr('aria-current', 'true');
    }

    // Hide icons from screen readers
    carousel.find('i[class*="fa"]').attr('aria-hidden', 'true');
  }

  // ADA FIX: Remove focusable items from off-screen / cloned slides so Tab
  // only visits links in the currently visible page, preventing misalignment
  // and eliminating the extra keystrokes needed to escape the carousel.
  function updateOffscreenTabindex() {
    var carousel = jQuery('.jtcs<?php echo $module->id; ?>');

    // Make everything inside non-active or cloned items unfocusable.
    // NOTE: .owl-nav and .owl-dots sit outside .owl-item so they are
    // never touched here — their tabindex is managed by addAccessibilityAttributes().
    carousel.find('.owl-item').each(function() {
      var item = jQuery(this);
      var isVisible = item.hasClass('active') && !item.hasClass('cloned');
      var focusables = item.find('a, button, input, select, textarea, [tabindex]');
      if (isVisible) {
        // Restore focusability for visible slides
        focusables.each(function() {
          var el = jQuery(this);
          var saved = el.data('saved-tabindex');
          if (saved !== undefined) {
            if (saved === '0') {
              el.removeAttr('tabindex');
            } else {
              el.attr('tabindex', saved);
            }
            el.removeData('saved-tabindex');
          } else if (el.attr('tabindex') === '-1') {
            el.removeAttr('tabindex');
          }
        });
      } else {
        // Remove from tab order for off-screen / cloned items
        focusables.each(function() {
          var el = jQuery(this);
          if (el.attr('tabindex') !== '-1') {
            el.data('saved-tabindex', el.attr('tabindex') || '0');
            el.attr('tabindex', '-1');
          }
        });
      }
    });
  }

  // ADA FIX: Keyboard arrow navigation on the carousel region itself,
  // without calling owlInstance.to() on focus (which caused misalignment).
  var carouselEl = jQuery('.jtcs<?php echo $module->id; ?>');
  carouselEl.on('keydown', function(e) {
    var owlInstance = jQuery(this).data('owl.carousel');
    if (!owlInstance) return;
    if (e.key === 'ArrowLeft')  { e.preventDefault(); owlInstance.prev(); }
    if (e.key === 'ArrowRight') { e.preventDefault(); owlInstance.next(); }
  });

  // Enter/Space on nav buttons and dots
  carouselEl.on('keydown', '.owl-prev, .owl-next, .owl-dot', function(e) {
    if (e.key === 'Enter' || e.key === ' ') {
      e.preventDefault();
      jQuery(this).trigger('click');
    }
  });

  // ADA: Show nav buttons and pause autoplay when keyboard focus enters carousel
  carouselEl.on('focusin', function() {
    var owlInstance = jQuery(this).data('owl.carousel');
    if (owlInstance) owlInstance.trigger('stop.owl.autoplay');
    // Fallback for browsers with limited :focus-within support
    jQuery(this).addClass('keyboard-focused');
  });

  // ADA: Hide nav buttons again when focus leaves the carousel entirely
  carouselEl.on('focusout', function() {
    var self = this;
    // Use setTimeout so the class isn't removed before the next element receives focus
    setTimeout(function() {
      if (!jQuery(self).find(':focus').length) {
        jQuery(self).removeClass('keyboard-focused');
      }
    }, 100);
  });

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

        if (rowsNb != previousRowsNb || colsNb != previousColsNb) {
          var reInit = false;
          if (carousel) {
            carousel.trigger('destroy.owl.carousel');
            carousel = undefined;
            slides = el.find('[data-slide-index]').detach().appendTo(el);
            el.find('.fake-col-wrapper').remove();
            reInit = true;
          }

          var perPage = rowsNb * colsNb;
          var pageIndex = Math.floor(slidesNb / perPage);
          var fakeColsNb = pageIndex * colsNb + (slidesNb >= (pageIndex * perPage + colsNb) ? colsNb : (slidesNb % colsNb));

          var count = 0;
          for (var i = 0; i < fakeColsNb; i++) {
            var fakeCol = jQuery('<div class="fake-col-wrapper"></div>').appendTo(el);
            for (var j = 0; j < rowsNb; j++) {
              var index = Math.floor(count / perPage) * perPage + (i % colsNb) + j * colsNb;
              if (index < slidesNb) {
                slides.filter('[data-slide-index=' + index + ']').detach().appendTo(fakeCol);
              }
              count++;
            }
          }

          previousRowsNb = rowsNb;
          previousColsNb = colsNb;

          if (reInit) {
            carousel = el.owlCarousel(carouselOptions);
          }
        }
      };

      jQuery(window).on('resize', updateCarousel);
      updateCarousel();
    }
  }

  carousel = el.owlCarousel(carouselOptions);
});

if (typeof lightbox !== 'undefined') {
  lightbox.option({
    fadeDuration:<?php echo (int)$params->get('fadeDuration', 300); ?>,
    fitImagesInViewport:<?php echo $params->get('fitImagesInViewport') == 'true' ? 'true' : 'false'; ?>,
    imageFadeDuration: <?php echo (int)$params->get('imageFadeDuration', 300); ?>,
    positionFromTop: <?php echo (int)$params->get('positionFromTop', 150); ?>,
    resizeDuration: <?php echo (int)$params->get('resizeDuration', 150); ?>
  });
}
</script>
