<?php
/**
 * @package Vertical scroller JT1 Pro Module for Joomla! 2.5
 * @version $Id: 1.0 
 * @author muratyil
 * @Copyright (C) 2013- muratyil
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
**/
// no direct access
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Factory;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Language\Text;

$document = Factory::getDocument();
if ($params->get('navigation_type')=='true') {
$showall_padding='9';
}
else if ($params->get('navigation_type')=='thumbnails') {
$showall_padding=$showall_padding;
}
$limit_items=$params->get('limit_items');
$itemwidth=100/$limit_items;

?>
<style type="text/css"> 
.vertical-scroller<?php echo $moduleId; ?> .navigate{top:<?php echo $params->get('navtop_position'); ?>;right:<?php echo $params->get('navright_position'); ?>;}
#scroller<?php echo $module->id; ?> ul li{ display:block; clear:both}
</style>

<div class="vertical-scroller<?php echo $module->id; ?>" style="padding:<?php echo $padding;?>; width:<?php echo $params->get('modulewidth');?>;  background-color:<?php echo $params->get('newsbg');?>">
<?php if($params->get('ShowNavigation')==1){?>
<div class="navigate">
<?php if($params->get('NavigationType')=='arrow'){?>
<a  id="prev-animate<?php echo $module->id; ?>"><span class="arrow-border"><i class="fa <?php echo $params->get('previcon');?>" aria-hidden="true"></i></span></a>
<?php if($params->get('ShowPause')==1):?><button id="pause<?php echo $module->id; ?>">Pause</button><?php endif; ?>
<a  id="next-animate<?php echo $module->id; ?>"><span class="arrow-border"><i class="fa <?php echo $params->get('nexticon');?>" aria-hidden="true"></i></span></a>
<?php }?>
<?php if($params->get('NavigationType')=='text'){?>
<a  id="prev-animate<?php echo $module->id; ?>"><?php echo $params->get('prev_text');?></a>
<?php if($params->get('ShowPause')==1):?><button id="pause<?php echo $module->id; ?>">Pause</button><?php endif; ?>
<a  id="next-animate<?php echo $module->id; ?>"><?php echo $params->get('next_text');?></a>
<?php }?>
<?php if($params->get('NavigationType')=='textandarrow'){?>
<a  id="prev-animate<?php echo $module->id; ?>"><i class="fa <?php echo $params->get('previcon');?>" aria-hidden="true"></i><?php echo $params->get('prev_text');?></a>
<?php if($params->get('ShowPause')==1):?><button id="pause<?php echo $module->id; ?>">Pause</button><?php endif; ?>
<a  id="next-animate<?php echo $module->id; ?>"><?php echo $params->get('next_text');?><i class="fa <?php echo $params->get('nexticon');?>" aria-hidden="true"></i>
</a>
<?php }?>

</div>
<?php }?>

<div id="scroller<?php echo $module->id; ?>">
<?php if ($params->get('source') == 'custom_items') : ?>
<?php 
// Collect non-empty custom items
$customItems = array();
for ($i = 1; $i <= 40; $i++) {
    $item = $params->get('item' . $i);
    // Check if item is not empty (handles null, false, empty string, and whitespace)
    // Use null coalescing operator to convert null to empty string before trim
    if (!empty(trim($item ?? ''))) {
        $customItems[] = $item;
    }
}
$totalCustomItems = count($customItems);
?>
<ul>
<?php foreach ($customItems as $customItem) : ?>
<li><div class="scroller" style="padding:<?php echo $params->get('block_padding');?>"><?php echo $customItem; ?></div></li>
<?php endforeach; ?>
</ul>
<?php else : ?>
<?php $totalCustomItems = 0; // Not used for non-custom items ?>
<ul>
<?php foreach ($list as $row)  : $row->categlist = Route::_('index.php?option=com_content&view=category&id='.$row->catid)?>
<li>
<div class="scroller" style="padding:<?php echo $params->get('block_padding');?>">
<?php if( $row->thumbnail && $align_image != "center"): ?>
<a target="<?php echo $openTarget; ?>" class="vscr-image-link" title="<?php echo $row->title;?>" href="<?php echo $row->link;?>">
<figure class="vscr-image" style="margin:<?php echo $imagemargin ;?>;  float:<?php echo $align_image;?>;"><img class="<?php echo $imgClass ?>"  src="<?php echo $row->thumbnail; ?>" alt="<?php echo $row->title?>"  style=" width:<?php echo $thumbWidth ;?>px; " title="<?php echo $row->title?>" /></figure>
</a> 
<?php endif ; ?>
<?php if( $show_category_name ): ?>
<?php if($show_category_name_as_link) : ?>
<a class="vjt1-category" target="<?php echo $openTarget; ?>"
						title="<?php echo $row->category_title; ?>"
						href="<?php echo $row->categoryLink;?>"> <?php echo $row->category_title; ?>
			</a>
<?php else :?>
<span class="vjt1-category"> <?php echo $row->category_title; ?> </span>
<?php endif; ?>
<?php endif; ?>
<?php if( $showtitle ): ?>
<a class="vjt1-title" target="<?php echo $openTarget; ?>"
						title="<?php echo $row->title; ?>"
						href="<?php echo $row->link;?>">  <<?php echo htmlspecialchars($params->get('TitleClass'), ENT_QUOTES, 'UTF-8'); ?>><?php echo $row->title_cut; ?> </<?php echo htmlspecialchars($params->get('TitleClass'), ENT_QUOTES, 'UTF-8'); ?>></a>
<?php endif; ?>					
					<?php if( $row->thumbnail && $align_image == "center" ): ?>
					<div class="vjt1-center">
					<a target="<?php echo $openTarget; ?>"
						class="vscr-image-link"
						title="<?php echo $row->title;?>" href="<?php echo $row->link;?>">
						<img <?php echo $imgClass ?> src="<?php echo $row->thumbnail; ?>" alt="<?php echo $row->title?>"  style="margin:<?php echo $imagemargin ;?>;width:<?php echo $thumbWidth ;?>px;" title="<?php echo $row->title?>" />
					</a>
					</div>
					<?php endif ; ?>
					<?php if( $showAuthor || $showDate ): ?>
					<div class="vjt1-extra">
					<?php if( $showAuthor ): ?>
						<span class="vjt1-author small"><?php 	echo Text::sprintf('VERTICAL_CREATEDBY' ,
						JHtml::_('link',JRoute::_($row->authorLink),$row->author)); ?>
						</span>
						<?php endif; ?>
						<?php if( $showDate ): ?>
						<span class="vjt1-date small"><?php echo Text::sprintf('VERTICAL_CREATEDON', $row->date); ?>
						</span>
						<?php endif; ?>
					</div>
					<?php endif; ?>
						<?php if( $show_intro ): ?>
					<div class="vjt1-introtext">
					<span class="introtext"><?php echo $row->description; ?></span>
					<?php if( $showreadone ) : ?>
					<span class="vsc-readmore" style="display:flex;justify-content:<?php echo $readonposition;?>">
						<a target="<?php echo $openTarget; ?>"
							title="<?php echo $row->title;?>"
							href="<?php echo $row->link;?>"><?php echo Text::_($readon_text); ?>
						</a>
					</span>
					<?php endif; ?>
					</div>
					<?php endif; ?>
	<?php $morecatlink = "<a href=".Route::_('index.php?option=com_content&view=category&id='.$row->catid).">".$row->category_title."</a>";
		$morecatlinks[$morecatlink] = true;
		if (isset($morecatlinks[$morecatlink])) {
			continue;
		}
		  ?> <div></div><div style="clear:both"></div></div></li>
<?php endforeach; ?>
</ul>
 
<?php endif; ?>


</div>

<?php 

if ($params->get('showall') == 1 && $params->get('source') == 'category')
{	echo "<div class='show_all'>".$params->get('show_all_text'). " ";
if (isset($morecatlinks[$morecatlink])) {
    $morecatlinks[$morecatlink] = true;
	foreach ($morecatlinks as $morecatlink => $val) 
	{
		echo $morecatlink. '&nbsp;&nbsp;';
	}
	echo "</div>";
	}
}
?>

</div>


<script>
jQuery(function() {
<?php 
$showItemsCount = ($params->get('source') == 'custom_items' && $totalCustomItems > 0) 
    ? min($totalCustomItems, $params->get('newsPerPage')) 
    : $params->get('newsPerPage');
?>
jQuery('#scroller<?php echo $module->id; ?>').vTicker('init', {
    speed: <?php echo $params->get('animationSpeed');?>,
    mousePause: <?php echo $params->get('pauseOnHover');?>,
    height: <?php echo $params->get('height');?>,
    animate: <?php echo $params->get('animate');?>,
    startPaused: <?php echo $params->get('startPaused');?>,
    showItems: <?php echo $showItemsCount;?>,
    margin: 0,
    padding: 0
});

<?php if ($params->get('autoplay') === 'false') : ?>
jQuery('#scroller<?php echo $module->id; ?>').vTicker('pause', true);
jQuery('#pause<?php echo $module->id; ?>').text('Play')
    .removeClass('unpaused').addClass('paused');
<?php endif; ?>

jQuery('#pause<?php echo $module->id; ?>').click(function() { 
    $this = jQuery(this);
    if($this.text() == 'Pause') {
      jQuery('#scroller<?php echo $module->id; ?>').vTicker('pause', true);
      $this.text('Play');
	  $(this).removeClass('unpaused');
	  $(this).addClass('paused');
	
    }
    else {
      jQuery('#scroller<?php echo $module->id; ?>').vTicker('pause', false);
      $this.text('Pause');
	  $(this).removeClass('paused');
	   $(this).addClass('unpaused');
	
    }
	
  });
 jQuery('#next-animate<?php echo $module->id; ?>').click(function() { 
    jQuery('#scroller<?php echo $module->id; ?>').vTicker('next', {animate:true});
  });
  jQuery('#prev-animate<?php echo $module->id; ?>').click(function() { 
    jQuery('#scroller<?php echo $module->id; ?>').vTicker('prev', {animate:true});
  });
});

</script>