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
namespace Joomla\Module\JTCarousel\Site\helper;
use Joomla\CMS\Language\Text;

defined('_JEXEC') or die;

use Joomla\String\StringHelper;
use Joomla\CMS\Uri\Uri;

abstract class jtcarouselhelper
{
	public static function getImages(&$params, $folder)
	{
		// Image format to load
		$type1 = "JPG";
		$type2 = "PNG";
		$type3 = "GIF";
		$type4 = "JPEG";
		$type5 = "BMP";
		$type6 = "XXX";

		$files	= array();
		$images	= array();
			
		$dir = JPATH_BASE . '/' . $folder;
		
		// Check if directory exists
		if (is_dir($dir))
		{
			if ($handle = opendir($dir))
			{
				while (false !== ($file = readdir($handle)))
				{
					if ($file !== '.' && $file !== '..' && $file !== 'CVS' && $file !== 'index.html')
					{
						$files[] = $file;
					}
				}
			}

			closedir($handle);

			$i = 0;

			foreach ($files as $img)
			{
				if ( !is_dir($dir . '/' . $img) && (preg_match('/' . $type1 . '/', strtoupper($img)) ||
														preg_match('/' . $type2 . '/', strtoupper($img)) ||
															preg_match('/' . $type3 . '/', strtoupper($img)) ||
																preg_match('/' . $type4 . '/', strtoupper($img)) ||
																	preg_match('/' . $type5 . '/', strtoupper($img)) ||
																		preg_match('/' . $type6 . '/', strtoupper($img))) )
				{
					$images[$i] = new \stdClass;
					$images[$i]->name   = $img;
					$images[$i]->folder = $folder;
					$i++;
				}
			}
		}
		else
		{
			$dir = str_replace('/', '\\', $dir);
			echo \Text::_('No directory <br>' . $dir . '<br>');
		}
		
		return $images;
	}

	public static function getFolder(&$params)
	{
		$folder	= $params->get('jtc_folder' , 'modules/mod_jt_carousel/assets/images');
		$LiveSite = Uri::base();

		// If folder includes livesite info, remove
		if (StringHelper::strpos($folder, $LiveSite) === 0)
		{
			$folder = str_replace($LiveSite, '', $folder);
		}

		// If folder includes absolute path, remove
		if (StringHelper::strpos($folder, JPATH_SITE) === 0)
		{
			$folder = str_replace(JPATH_BASE, '', $folder);
		}

		return str_replace(array('\\', '/'), DIRECTORY_SEPARATOR, $folder);
	}
}