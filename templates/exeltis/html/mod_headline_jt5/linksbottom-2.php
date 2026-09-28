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
error_reporting(E_ALL ^ E_NOTICE  ^ E_DEPRECATED ^ E_WARNING);
//HTMLHelper::_('behavior.framework');
//if($modal){HTMLHelper::_('behavior.modal');}
$document = Factory::getDocument();

if ($params->get('navigation_type')=='true') {
$showall_padding='9';
}
else if ($params->get('navigation_type')=='thumbnails') {
$showall_padding=$showall_padding;
}
$limit_items=$params->get('limit_items');
//$itemwidth=100/$limit_items;
////////////////
$layout_static=$params->get( 'layout' ) == 'static';
$news_thumb_width = "width:".($params->get('news_thumb_width') -5)."px";
$moduleheight=$params->get('moduleheight');
$modulewidth=$params->get('modulewidth');
$newsbg=$params->get('newsbg');
$panelWidthCss = "width:".($params->get('news_thumb_width') -5)."px";
$enableLink = $params->get('jt5_link_limage');
$titlecolor= $params->get('titlecolor');
$titlecolor2= $params->get('titlecolor2');
$textcolor= $params->get('textcolor');
$textcolor2=$params->get('textcolor2');
$readoncolor=$params->get('readoncolor');
$readoncolor2=$params->get('readoncolor2');
$readonposition=$params->get('readonposition');
$readonposition2=$params->get('readonposition2');
$textposition=$params->get('textposition');
$previous_text=$params->get('previous_text');
$next_text=$params->get('next_text');
$imagefloat=$params->get('imagefloat');
$imagefloat2=$params->get('imagefloat2');
$singlerowfloat=$params->get('singlerowfloat');
$imagemargin=$params->get('imagemargin');
$imagemargin2=$params->get('imagemargin2');
$enablefade=$params->get('enablefade');
$showreadone=$params->get('showreadone')==1;
$showreadone2=$params->get('showreadone2')==1;
$showintrotext=$params->get('showintrotext')==1;
$showintrotext2=$params->get('showintrotext2')==1;
$showtitle=$params->get('show_title')==1;
$show_all_text=$params->get('show_all_text');
$readon_text=$params->get('readon_text');
$padding=$params->get('padding');
$little_thumbwidth=$params->get('little_thumbwidth');
$little_thumbheight=$params->get('little_thumbheight');
$moduleheight2=(intval($moduleheight) - ($little_thumbheight+26));

$multiplerowswidth=$params->get('multiplerowswidth');
$singlerowwidth = 100- $multiplerowswidth ;
$borderbottom=$params->get('borderbottom');
$multiplerows_padding=$params->get('multiplerows_padding');
$multiplerowsblock_padding=$params->get('multiplerowsblock_padding');
$showtitle2=$params->get('showtitle2')==1;
$linktitle=$params->get('linktitle')==1;
$non_linktitle=$params->get('linktitle')==0;
$linktitle2=$params->get('linktitle2')==1;
$non_linktitle2=$params->get('linktitle2')==0;
if ($params->get('singlerowfloat')=='left') {
$multiplerowfloat='right';
}
if ($params->get('singlerowfloat')=='right') {
$multiplerowfloat='left';}
//
?>
<style type="text/css">
#paginate-jt5slider<?php echo $module->id; ?> .multiplerows{border-bottom:<?php echo $borderbottom;?>; padding:<?php echo $multiplerows_padding;?>;}
</style>
<!---------------------------STATIC DISPLAY--------------------------------------------------------->
<?php if($params->get( 'layout_type' ) == 'static'){?>
<div id="jt5_headline-news_wrapper" class="exeltis linksbottom linksbottom-2" style="padding:<?php echo $padding;?>; width:<?php echo $modulewidth;?>;height:<?php echo $moduleheight;?>;background:none;">
<?php if($showall == 1) : ?><div class="show_all_cont"><?php  foreach ($list as $row):?><?php endforeach; ?>	
<div class="show_all_inner"><div class="jt5_pagination"  id="paginate-jt5_slider<?php echo $module->id; ?>" style="text-align:right;">
<a class="show_all" title="<?php echo $row->category_title; ?>"href="<?php echo $row->categoryLink;?>"> <?php echo JText::_($show_all_text); ?> <i class="fa fa-angle-double-right" aria-hidden="true"></i></a></div></div>
</div>
<?php endif; ?>
<div class="innerwrapper">
<div id="singlerowblock" style="float:none;position: relative;width:auto;	overflow: hidden;">
<div class="jt5_headline-news<?php echo $module->id; ?>" id="headline" style="padding:<?php echo $params->get('singleblock_padding');?>;">
		<div class="maskjt5<?php echo $module->id; ?>">
			<div id="jt5slider<?php echo $module->id; ?>" class="jt5sliderwrapper">
			<div class="contentdiv">
			<?php  foreach ($list as $row):?><?php endforeach; ?>	
			<?php $row = reset($list); ?>
			<?php if( $row->thumbnail): ?><div class="bt-imagecover">
						<span class="hjt5-dateonimage"><?php echo JText::sprintf('VERTICAL_CREATEDON', $row->date); ?>
						</span>
<figure class="hjt5-image" style="margin:<?php echo $imagemargin ;?>;  float:<?php echo $align_image;?>;">
<?php if($enableLink>0){?>
<a target="<?php echo $openTarget; ?>" class="hjt5-image-link" title="<?php echo $row->title;?>" href="<?php echo $row->link;?>">
<img class="<?php echo $imgClass ?>"  src="<?php echo $row->thumbnail; ?>" alt="<?php echo $row->title?>"  style="float:<?php echo $imagefloat; ?>;margin:<?php echo $imagemargin; ?> width:<?php echo $thumbWidth ;?>px; " title="<?php echo $row->title?>" /></a>
		<?php }else{?>
		<img class="<?php echo $imgClass ?>"  src="<?php echo $row->thumbnail; ?>" alt="<?php echo $row->title?>"  style="float:<?php echo $imagefloat; ?>;margin:<?php echo $imagemargin; ?> width:<?php echo $thumbWidth ;?>px; " title="<?php echo $row->title?>" />
		<?php }?>
		<?php endif ; ?>
</figure>
<div class="overlay"><div class="hover-icons">
		<a class="bt-icon icon-url exeltis" title="<?php echo $row->title; ?>" href="<?php echo $row->link;?>" target="<?php echo $openTarget; ?>"><i class="fa fa-link"></i></a><a class="bt-icon icon-lightbox modal" exeltis title="<?php echo $row->title; ?>" href="<?php echo $row->mainImage;?>"> <i class="fa fa-search"></i></a>
		</div></div>
		</div>

<?php if($showtitle and $linktitle ) : ?>
<h4><a class="toc"  style="color:inherit;" target="<?php echo $openTarget; ?>"
						title="<?php echo $row->title; ?>"
						href="<?php echo $row->link;?>"><?php echo $row->title_cut; ?></a></h4>
<?php endif; ?>					
<?php if($showtitle and $non_linktitle ) : ?>
<h4 style="color:inherit;"><?php echo $row->title_cut; ?></h4>
<?php endif; ?>
<?php if( $showAuthor || $showDate || $show_category_name): ?>
					<div class="hjt5-author-date">
					<?php if( $showAuthor ): ?>
						<span class="hjt5-author small"><i class="fa fa-user" aria-hidden="true"></i> <?php 	echo JText::sprintf('VERTICAL_CREATEDBY' ,
						HTMLHelper::_('link',Route::_($row->authorLink),$row->author)); ?>
						</span>
						<?php endif; ?>
						<?php if( $showDate ): ?>
						<span class="hjt5-date small"><i class="fa fa-clock-o" aria-hidden="true"></i> <?php echo JText::sprintf('VERTICAL_CREATEDON', $row->date); ?>
						</span>
						<?php endif; ?>
						<?php if( $show_category_name ): ?>
<?php if($show_category_name_as_link) : ?>
<a class="hjt5-category" target="<?php echo $openTarget; ?>"
						title="<?php echo $row->category_title; ?>"
						href="<?php echo $row->categoryLink;?>"><span class="hjt5-category small"><i class="fa fa-folder-open" aria-hidden="true">&nbsp;</i><?php echo $row->category_title; ?>
			</span></a>
<?php else :?>
<span class="hjt5-category small"><i class="fa fa-folder-open" aria-hidden="true">&nbsp;</i><?php echo $row->category_title; ?> </span>
<?php endif; ?>
<?php endif; ?>
					</div>
					
					<?php endif; ?>

<?php if( $showintrotext ): ?><p class="srowintrotext" style="color:inherit;"><?php echo $row->description; ?></p><?php endif; ?>

<?php if($showreadone) : ?><p style="text-align:<?php echo $readonposition;?>">	<a class="srowreadone btn btn-primary " style="color:<?php echo $readoncolor;?>;" target="<?php echo $openTarget; ?>"
							title="<?php echo $row->title;?>"
							href="<?php echo $row->link;?>"><?php echo JText::_($readon_text); ?>  <i class="fa fa-angle-double-right" aria-hidden="true"></i>
						</a></p><?php endif; ?>
						
</div><!--contentdiv-->		
</div><!--jt5slider-->
</div><!--maskjt5-->
</div><!--jt5_headline-news-->
</div><!--singlerowblock-->
<div style="clear:both"></div>
<div id="multiplerowblock" style="float:none;width:auto;">
		<div id="paginate-jt5slider<?php echo $module->id; ?>" class="jt5_headline-pagination" style="position:relative;padding:<?php echo $multiplerowsblock_padding;?>;">
		<ul class="jt5-linksbottom-2">
				<?php unset($list[0]) ;  foreach ($list as $row) :?>
				<li>
		<div class="multiplerows">
			<?php if($row->thumbnail){?>
			<?php if($enableLink>0){?>
			<a href="<?php echo $row->link;?>"><span><img class="littleimg" src="<?php echo $row->mainImage; ?>" style="width:<?php echo $little_thumbwidth; ?>px; height:<?php echo $little_thumbheight; ?>px;float:<?php echo $imagefloat2; ?>;margin:<?php echo $imagemargin2; ?>" /></span></a>
					<?php }else{?>
					<img class="littleimg" src="<?php echo $row->thumbnail; ?>" style="width:<?php echo $little_thumbwidth; ?>px; height:<?php echo $little_thumbheight; ?>px;float:<?php echo $imagefloat2; ?>;margin:<?php echo $imagemargin2; ?>" />
							<?php }?>
							<?php }?>
<?php if($showtitle2) : ?>
				<?php if($linktitle2 ) {?><a href="<?php echo $row->link;?>" class="toc" style="color:inherit;"><h4><?php echo $row->title_cut2; ?></h4></a>
				<?php }else{?><h4 class="toc" style="color:inherit;"><?php echo $row->title_cut2; ?><?php }?></h4>				
			<?php endif; ?>
			<?php if( $params->get('show_author2')|| $params->get('show_date2')): ?>
					<div class="hjt5-author-date">
					<?php if( $params->get('show_author2') ): ?>
						<span class="hjt5-author small"><i class="fa fa-user" aria-hidden="true"></i> <?php 	echo JText::sprintf('VERTICAL_CREATEDBY' ,
						HTMLHelper::_('link',Route::_($row->authorLink),$row->author)); ?>
						</span>
						<?php endif; ?>
						<?php if(  $params->get('show_date2') ): ?>
						<span class="hjt5-date small"><i class="fa fa-clock-o" aria-hidden="true"></i> <?php echo JText::sprintf('VERTICAL_CREATEDON', $row->date); ?>
						</span>
						<?php endif; ?>
					</div>
					<?php endif; ?>
				<?php if($showintrotext2) : ?><p class="mrowsintrotext" style="color:inherit;"><?php echo $row->description2; ?></p><?php endif; ?>
				<?php if($showreadone2) : ?><p class="mrowsreadone" style="text-align:<?php echo $readonposition2;?>;"><a href="<?php echo $row->link;?>" class="mrowsreadone" style="color:<?php echo $readoncolor2;?>;"><?php echo JText::_($readon_text); ?></a></p><?php endif; ?>
			<div></div><div style="clear:both"></div></div>
			</li><?php endforeach; ?>
 </ul>
		
</div>
<div></div><div style="clear:both"></div></div><!--innerwrapper-->
<div></div><div style="clear:both"></div></div></div><!--jt5_headline-news_wrapper-->

<!-------------------------------------SLIDER DISPLAY-------------------------------------------------------------------------------------------->
<?php }else{?>
<div id="jt5_headline-news_wrapper" class="exeltis linksbottom linksbottom-2" style="padding:<?php echo $padding;?>; width:<?php echo $modulewidth;?>;height:<?php echo $moduleheight;?>;background:none;">
<?php if($showall == 1) : ?><div class="show_all_cont"><?php  foreach ($list as $row):?><?php endforeach; ?>	
<div class="show_all_inner"><div class="jt5_pagination"  id="paginate-jt5_slider<?php echo $module->id; ?>" style="text-align:right;">
<a class="show_all" title="<?php echo $row->category_title; ?>"href="<?php echo $row->categoryLink;?>"> <?php echo JText::_($show_all_text); ?> <i class="fa fa-angle-double-right" aria-hidden="true"></i></a></div></div>
</div>
<?php endif; ?>
<div class="innerwrapper">
<div id="singlerowblock" style="float:none;position: relative;width:auto;	overflow: hidden;">
<div class="jt5_headline-news<?php echo $module->id; ?>" id="headline" style="padding:<?php echo $params->get('singleblock_padding');?>;">
		<div class="maskjt5<?php echo $module->id; ?>">
			<div id="jt5slider<?php echo $module->id; ?>" class="jt5sliderwrapper">
			<?php  foreach ($list as $row):?><div class="contentdiv">
			<?php if( $row->thumbnail): ?><div class="bt-imagecover">
						<span class="hjt5-dateonimage"><?php echo JText::sprintf('VERTICAL_CREATEDON', $row->date); ?>
						</span>
<figure class="hjt5-image" style="margin:<?php echo $imagemargin ;?>;  float:<?php echo $align_image;?>;">
<?php if($enableLink>0){?>
<a target="<?php echo $openTarget; ?>" class="hjt5-image-link" title="<?php echo $row->title;?>" href="<?php echo $row->link;?>">
<img class="<?php echo $imgClass ?>"  src="<?php echo $row->thumbnail; ?>" alt="<?php echo $row->title?>"  style="float:<?php echo $imagefloat; ?>;margin:<?php echo $imagemargin; ?> width:<?php echo $thumbWidth ;?>px; " title="<?php echo $row->title?>" /></a>
		<?php }else{?>
		<img class="<?php echo $imgClass ?>"  src="<?php echo $row->thumbnail; ?>" alt="<?php echo $row->title?>"  style="float:<?php echo $imagefloat; ?>;margin:<?php echo $imagemargin; ?> width:<?php echo $thumbWidth ;?>px; " title="<?php echo $row->title?>" />
		<?php }?>
		<?php endif ; ?>
</figure></div>
<div class="bt-title-text-container">
<?php if($showtitle and $linktitle ) : ?>
<h4><a class="toc"  style="color:inherit;" target="<?php echo $openTarget; ?>"
						title="<?php echo $row->title; ?>"
						href="<?php echo $row->link;?>"><?php echo $row->title_cut; ?></a></h4>
<?php endif; ?>					
<?php if($showtitle and $non_linktitle ) : ?>
<h4 style="color:inherit;"><?php echo $row->title_cut; ?></h4>
<?php endif; ?>

<?php if( $showAuthor || $showDate || $show_category_name): ?>
					<div class="hjt5-author-date">
					<?php if( $showAuthor ): ?>
						<span class="hjt5-author small"><i class="fa fa-user" aria-hidden="true"></i> <?php 	echo JText::sprintf('VERTICAL_CREATEDBY' ,
						HTMLHelper::_('link',Route::_($row->authorLink),$row->author)); ?>
						</span>
						<?php endif; ?>
						<?php if( $showDate ): ?>
						<span class="hjt5-date small"><i class="fa fa-clock-o" aria-hidden="true"></i> <?php echo JText::sprintf('VERTICAL_CREATEDON', $row->date); ?>
						</span>
						<?php endif; ?>
						<?php if( $show_category_name ): ?>
<?php if($show_category_name_as_link) : ?>
<a class="hjt5-category" target="<?php echo $openTarget; ?>"
						title="<?php echo $row->category_title; ?>"
						href="<?php echo $row->categoryLink;?>"><span class="hjt5-category small"><i class="fa fa-folder-open" aria-hidden="true">&nbsp;</i><?php echo $row->category_title; ?>
			</span></a>
<?php else :?>
<span class="hjt5-category small"><i class="fa fa-folder-open" aria-hidden="true">&nbsp;</i><?php echo $row->category_title; ?> </span>
<?php endif; ?>
<?php endif; ?>
					</div>
					
					<?php endif; ?>

<?php if( $showintrotext ): ?><p class="srowintrotext" style="color:inherit;"><?php echo $row->description; ?></p><?php endif; ?>

<?php if($showreadone) : ?><p style="text-align:<?php echo $readonposition;?>">	<a class="srowreadone btn btn-primary" style="color:<?php echo $readoncolor;?>;" target="<?php echo $openTarget; ?>"
							title="<?php echo $row->title;?>"
							href="<?php echo $row->link;?>"><?php echo JText::_($readon_text); ?>  <i class="fa fa-angle-double-right" aria-hidden="true"></i>
						</a></p><?php endif; ?>	
						</div>		<!--//bt-title-text-container-->
</div><!--contentdiv-->	<?php endforeach; ?>	
</div><!--jt5slider-->
</div><!--maskjt5-->
</div><!--jt5_headline-news-->
</div><!--singlerowblock-->
<div id="multiplerowblock" style="float:none;width:auto;">
		<div id="paginate-jt5slider<?php echo $module->id; ?>" class="jt5_headline-pagination" style=" position:relative; padding:<?php echo $multiplerowsblock_padding;?>;">
		<ul class="jt5-linksbottom-2">
		<?php foreach ($list as $row) :?>
		<li>
		<div class="multiplerows">
			<?php if($row->thumbnail){?>
			<?php if($enableLink>0){?>
			<a href="<?php echo $row->link;?>"><span><img class="littleimg" src="<?php echo $row->mainImage; ?>" style="width:<?php echo $little_thumbwidth; ?>px; height:<?php echo $little_thumbheight; ?>px;float:<?php echo $imagefloat2; ?>;margin:<?php echo $imagemargin2; ?>" /></span></a>
					<?php }else{?>
					<img class="littleimg" src="<?php echo $row->thumbnail; ?>" style="width:<?php echo $little_thumbwidth; ?>px; height:<?php echo $little_thumbheight; ?>px;float:<?php echo $imagefloat2; ?>;margin:<?php echo $imagemargin2; ?>" />
							<?php }?>
							<?php }?>
				<?php if($showtitle2) : ?>
				<?php if($linktitle2 ) {?><h4><a href="<?php echo $row->link;?>" class="toc" style="color:inherit;"><?php echo $row->title_cut2; ?></a></h4>
				<?php }else{?><h4 class="toc" style="color:inherit;"><?php echo $row->title_cut2; ?><?php }?></h4>				
			<?php endif; ?>	
			<?php if( $params->get('show_author2')|| $params->get('show_date2')): ?>
					<div class="hjt5-author-date">
					<?php if( $params->get('show_author2') ): ?>
						<span class="hjt5-author small"><i class="fa fa-user" aria-hidden="true"></i> <?php 	echo JText::sprintf('VERTICAL_CREATEDBY' ,
						HTMLHelper::_('link',Route::_($row->authorLink),$row->author)); ?>
						</span>
						<?php endif; ?>
						<?php if(  $params->get('show_date2') ): ?>
						<span class="hjt5-date small"><i class="fa fa-clock-o" aria-hidden="true"></i> <?php echo JText::sprintf('VERTICAL_CREATEDON', $row->date); ?>
						</span>
						<?php endif; ?>
					</div>
					<?php endif; ?>
					<?php if($showintrotext2) : ?><p class="mrowsintrotext" style="color:inherit;"><?php echo $row->description2; ?></p><?php endif; ?>
					<?php if($showreadone2) : ?><p class="mrowsreadone" style="text-align:<?php echo $readonposition2;?>;"><a href="<?php echo $row->link;?>" class="mrowsreadone" style="color:<?php echo $readoncolor2;?>;"><?php echo JText::_($readon_text); ?></a></p><?php endif; ?>
			<div style="clear:both;"></div> </div></li><?php endforeach; ?>
			</ul>
		</div>
</div>
<script type="text/javascript">
<!--
/* <![CDATA[ */
	window.addEvent('domready',function(){
		featuredcontentslider5.init({
	id: "jt5slider<?php echo $module->id; ?>",  //id of main slider DIV
	contentsource: ["inline", ""],  //Valid values: ["inline", ""] or ["ajax", "path_to_file"]
	toc: "markup",  //Valid values: "#increment", "markup", ["label1", "label2", etc]
	nextprev: ["<?php echo $previous_text; ?>", "<?php echo $next_text; ?>"],  //labels for "prev" and "next" links. Set to "" to hide.
	revealtype: "<?php echo $params->get('news_event_type'); ?>", //Behavior of pagination links to reveal the slides: "click" or "mouseover"
	enablefade: [<?php echo $enablefade; ?>, 0.2],  //[true/false, fadedegree]
	autorotate: [<?php echo $autorun; ?>, <?php echo $timming; ?>],  //[true/false, pausetime]
	onChange: function(previndex, curindex, contentdivs){  //event handler fired whenever script changes slide
		//previndex holds index of last slide viewed b4 current (0=1st slide, 1=2nd etc)
		//curindex holds index of currently shown slide (0=1st slide, 1=2nd etc)
			}
		});
	});
/* ]]> */
-->
</script>

<div></div><div style="clear:both"></div></div><!--innerwrapper-->
<div></div><div style="clear:both"></div></div>
<?php } ?>


