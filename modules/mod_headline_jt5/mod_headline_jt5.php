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
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Helper\ModuleHelper;

require_once JPATH_SITE.'/modules/mod_headline_jt5/helpers/helper.php';
////////////
$document = Factory::getDocument();
$moduleId = $module->id;
/////////////////
$target = $params->get( 'target', "" );
$loadjquery=$params->get( 'loadjquery', "1" );
$speed = $params->get( 'speed', "500" );
$pause= $params->get( 'pause', "500" );
$mousePause= $params->get( 'mousePause', "false" );
$height= $params->get( 'height', "150" );
//
$height2= $params->get( 'height2', "150" );
$background2=$params->get( 'background2', "#f0f0f0" );
$padding2=$params->get( 'padding2', "5" );
//Get list content
$list = modJT5HeadLineHelper::getList( $params, $module );
$list2 = modJT5HeadLineHelper::getList( $params, $module );
//var_dump($list);

// ROW * COL
$itemsPerRow = 1;
$itemsPerCol = 1;
$moduleclass_sfx = $params->get('moduleclass_sfx');
$imgClass = $params->get('hovereffect');
$modal = $params->get('modalbox');

//Num of item display
$maxPages = $itemsPerRow*$itemsPerCol;

//Get pages list array
$pages = array_chunk( $list, $maxPages  );

//Get total pages
$totalPages = count($pages);

// calculate width of each row. (percent)
$itemWidth = 100/$itemsPerRow;

$tmp = $params->get( 'module_height', 'auto' );
$moduleHeight   =  ( $tmp=='auto' ) ? 'auto' :(int)$tmp.'px';
$tmp = $params->get( 'module_width', 'auto' );
$moduleWidth    =  ( $tmp=='auto') ? 'auto': ((int)$tmp).'px';
$moduleWidthWrapper = ( $tmp=='auto') ? 'auto': (int)$tmp.'px';

//Get Open target
$openTarget 	= $params->get( 'open_target', '_parent' );

//auto_start
$auto_start 	= $params->get('auto_start',1);

//butlet and next back
$next_back 		= $params->get( 'next_back', 0 );
$butlet 		= $params->get( 'butlet', 1 );



//Option for content
$showTitle = $params->get( 'show_title', '1' );

$show_category_name = $params->get( 'show_category_name', 0 );
$show_category_name_as_link = $params->get( 'show_category_name_as_link', 0 );

$showDate = $params->get( 'show_date', '0' );
$showAuthor = $params->get( 'show_author', '0' );


//Option for image
$thumbWidth    = (int)$params->get( 'thumbnail_width', 200 );
$thumbHeight   = (int)$params->get( 'thumbnail_height', 150 );

$image_crop = $params->get( 'image_crop', '0' );
$show_image = $params->get( 'show_image', '0' );
$effect = $params->get('next_back_effect', 'slider') .','.$params->get('bullet_effect', 'slider');
$slideEasing = $params->get('effect', 'easeInQuad');
$preloadImg = URI::root().'/modules/mod_headline_jt5/tmpl/images/loading.gif';
$paging = $params->get( 'butlet', 0 )>0 ?  'true':'false';
$play = $auto_start?(int)$params->get('interval', '5000'):0;
$hoverPause = $params->get( 'pause_hover', 1 )>0? 'true':'false';
$duration = (int)$params->get('duration', '1000');
$autoHeight = $params->get( 'auto_height', 0 )>0?'true':'false';
$fadeSpeed = (int)$params->get('duration', '1000');
$modid = '#headline_jt5'.$module->id;
$news_thumb_width = "width:".($params->get('news_thumb_width') -5)."px";
$moduleheight=$params->get('moduleheight');
$modulewidth=$params->get('modulewidth');
$newsbg=$params->get('newsbg');
$panelWidthCss = "width:".($params->get('news_thumb_width') -5)."px";
$enableLink = $params->get('jt3_link_limage');
$titlecolor= $params->get('titlecolor');
$textcolor= $params->get('textcolor');
$readoncolor=$params->get('readoncolor');
$readonposition=$params->get('readonposition');
$previous_text=$params->get('previous_text');
$next_text=$params->get('next_text');
$imagefloat=$params->get('imagefloat');
$imagemargin=$params->get('imagemargin');
$enablefade=$params->get('enablefade');
$showreadone=$params->get('show_readmore')==1;
$showintrotext=$params->get('showintrotext')==1;
$showtitle=$params->get('show_title')==1;
$show_all_text=$params->get('show_all_text');
$readon_text=$params->get('readon_text');
$k2_readon_text=$params->get('k2_readon_text');
$padding=$params->get('padding');
$little_thumbwidth=$params->get('little_thumbwidth');
$little_thumbheight=$params->get('little_thumbheight');
$little_thumbbg=$params->get('little_thumbbg');
$little_thumbpadding=$params->get('little_thumbpadding');
$showall_padding=(int)( (float)$little_thumbheight-12)/2;
$showall=$params->get('showall');
$autorun = $params->get('news_autorun');
$timming =  $params->get('timming') ; 
$fontsize=$params->get('fontsize');
if ( $showall == 1 ) {
$widthshow1 = $params->get('showallwidth');
$widthshow2 = 'auto';
$showallpadding=$widthshow1;
	}
	
else if($showall == 0){
$widthshow1 = '';
$widthshow2 = '100%';
$showallpadding='5px';
	}
///////////////////
////////////////////

$touchScreen = $params->get('touch_screen', 0);

$header = $document->getHeadData();
		$mainframe = Factory::getApplication();
		$template = $mainframe->getTemplate();

		if(file_exists(JPATH_BASE.'/templates/'.$template.'/html/mod_headline_jt5/css/headline_jt5.css.php?id='.$moduleId))
		{
			$document->addStyleSheet(  URI::root().'templates/'.$template.'/html/mod_headline_jt5/css/headline_jt5.css.php?id='.$moduleId);
			$document->addStyleSheet(URI::root().'modules/mod_headline_jt5/tmpl/css/jt5_news.css');
		}
		else{
			$document->addStyleSheet(URI::root().'modules/mod_headline_jt5/tmpl/css/headline_jt5.css.php?id='.$moduleId);
			$document->addStyleSheet(URI::root().'modules/mod_headline_jt5/tmpl/css/jt5_news.css');
			
		}

		$loadJquery = true;
		switch($params->get('loadJquery',"auto")){
			case "0":
				$loadJquery = false;
				break;
			case "1":
				$loadJquery = true;
				break;
			case "auto":

				foreach($header['scripts'] as $scriptName => $scriptData)
				{
					if(substr_count($scriptName,'/jquery'))
					{
						$loadJquery = false;
						break;
					}
				}
			break;
		}
		
	
		$document->addScript(URI::root().'modules/mod_headline_jt5/tmpl/js/jt5headline.js');
		
	
//Get tmpl
$align_image = strtolower($params->get( 'image_align', "center" ));
$equalHeight = $params->get( 'equalHeight', 0 ) > 0 && $align_image =='center' ?'true':'false';

/////////////////



require( ModuleHelper::getLayoutPath($module->module, $params->get('layout', 'default')));	
?>

