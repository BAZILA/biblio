<?php
/**
 * @package Headline JT5 Module for Joomla!
 * @version $Id: 1.0 
 * @author muratyil
 * @Copyright (C) 2013- muratyil
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
**/

use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\Filesystem\Folder;

// no direct access
defined('_JEXEC') or die('Restricted access');

class modJT5HeadLineHelper {

	/**
	 * Get list articles
	 * Ver 1 : only form content
	 */
	public static function getList( &$params, $module ){
		$thumbPath = JPATH_BASE . '/cache/' .$module->module.'/';
		$thumbUrl  = str_replace(JPATH_BASE.'/', Uri::base(), $thumbPath);
		$defaultThumb = Uri::base().'modules/'.$module->module.'/tmpl/images/no-image.jpg';	
		
		if( !is_dir($thumbPath) ) {
			Folder::create( $thumbPath, 0755 );
		}
		
		//Get source form params
		$source 	= $params->get('source','category');

		if($source == 'category' || $source == 'article_ids' || $source == 'joomla_tags')
		{
			$source = 'content';
		}
		else if($source == 'k2_category' || $source == 'k2_article_ids' || $source == 'k2_tags')
		{
			$source = 'k2';
		}
		else{
			$source = 'content';
		}

		$path = JPATH_SITE.'/modules/mod_headline_jt5/classes/'.$source.".php";

		require_once $path;
		$objectName = "Jt".ucfirst($source)."DataSource";
	 	$object = new $objectName($params);
		
		//3 step
		//1.set images path
		//2.Render thumb
		//3.Get List
	 	$items = $object->setThumbPathInfo($thumbPath,$thumbUrl,$defaultThumb)
			->setImagesRendered( array( 'thumbnail' =>
										array( (int)$params->get( 'thumbnail_width', 60 ), (int)$params->get( 'thumbnail_height', 60 ))
									) )
			->getList();
  		return $items;
	}
}
?>