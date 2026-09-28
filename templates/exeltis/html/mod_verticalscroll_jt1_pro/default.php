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
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Factory;
if($modal){HTMLHelper::_('behavior.modal');}
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
.jt_static_content a:link,.jt_static_content a:visited, .jt_static_content a:hover,.morearticle a:link,.morearticle a:visited,.morearticle a:hover{
font-size:<?php echo $fontsize; ?>px;
font-weight:bold;
background:none;
text-decoration:none;
outline:none;
color:<?php echo $titlecolor; ?>;}
.jt_static_content ul{
padding:0;
margin:0;}
.jt_static_content li{
list-style:none;}
.jt_header a:link,.jt_header a:visited, .jt_header a:hover{
font-size:<?php echo $fontsize; ?>px;
font-weight:bold;
text-decoration:none;
color:<?php echo $titlecolor; ?>;}
.morearticle a.readon:link,.morearticle a.readon:visited,.morearticle a.readon:hover{color:<?php echo $morearticles_color; ?>;
font-size:<?php echo $morearticles_fontsize; ?>px;}
li.jt_static_<?php echo $module->id; ?>{margin:<?php echo $params->get('margin', '2'); ?>px; display:block !important}
.vertical-scroller<?php echo $moduleId; ?> .navigate{top:<?php echo $params->get('navtop_position'); ?>;right:<?php echo $params->get('navright_position'); ?>;;}
</style>
<div class="vertical-scroller<?php echo $module->id; ?> exeltis" style="padding:<?php echo $padding;?>; width:<?php echo $params->get('modulewidth');?>;  background-color:<?php echo $params->get('newsbg');?>">
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
						href="<?php echo $row->link;?>"> <h4><?php echo $row->title_cut; ?> </h4></a>
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
						<span class="vjt1-author small"><?php 	echo JText::sprintf('VERTICAL_CREATEDBY' ,
						HTMLHelper::_('link',Route::_($row->authorLink),$row->author)); ?>
						</span>
						<?php endif; ?>
						<?php if( $showDate ): ?>
						<span class="vjt1-date small"><?php echo JText::sprintf('VERTICAL_CREATEDON', $row->date); ?>
						</span>
						<?php endif; ?>
					</div>
					<?php endif; ?>
						<?php if( $show_intro ): ?>
					<div class="vjt1-introtext">
					<span class="introtext"><?php echo $row->description; ?></span>
					<?php if( $showreadone ) : ?>
					<span class="readmore" style="float:<?php echo $readonposition;?>">
						<a target="<?php echo $openTarget; ?>"
							title="<?php echo $row->title;?>"
							href="<?php echo $row->link;?>"><?php echo JText::_($readon_text); ?>
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
</div>

<?php 
if($params->get('showall') == 1) 
{	echo "<div class='show_all'>".$params->get('show_all_text'). " ";
	foreach ($morecatlinks as $morecatlink => $val) 
	{
		echo $morecatlink. '&nbsp;&nbsp;';
	}
	echo "</div>";
}
?>

</div>

<script>
jQuery(function() {
jQuery('#scroller<?php echo $module->id; ?>').vTicker('init', {
speed: <?php echo $params->get('animationSpeed');?>,
mousePause: <?php echo $params->get('pauseOnHover');?>,
height:<?php echo $params->get('height');?>,
animate: <?php echo $params->get('animate');?>,
startPaused: <?php echo $params->get('startPaused');?>,
showItems: <?php echo $params->get('newsPerPage');?>,
margin: 0,
padding: 0,
    autoplay: <?php echo $params->get('autoplay');?>, // Add autoplay option
    autoplaySpeed:  <?php echo $params->get('AutoplayInterval');?> // Add autoplay speed option

});

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