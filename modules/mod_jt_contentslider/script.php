<?php

/**
 * @package     mod_jt_contentslider
 * @copyright   Copyright (C) 2007 - 2021 http://www.joomlatema.net, Inc. All rights reserved.
 * @license		http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 * @author     	JoomlaTema.Net
 * @link 		http://www.joomlatema.net
 **/

// No direct access to this file
defined('_JEXEC') or die;

class mod_jt_contentsliderInstallerScript
{
	public function install($parent) 
    {
		$modulebase = "mod_jt_contentslider";
		//$thumb_folder_0 ="/images/thumbnails/";
		//$thumb_folder ="/images/thumbnails/".$modulebase."/";
		
		$thumb_folder_0 ="/cache/".$modulebase."/";
		$thumb_folder ="/cache/".$modulebase."/";
		
		

		// Create thumbnail folder if not existif (!JFolder::exists(JPATH_ROOT.$thumb_folder))
		
		 
		{
			JFolder::create(JPATH_ROOT.$thumb_folder);
			JFile::write(JPATH_ROOT.$thumb_folder_0.'/index.html', "");
			JFile::write(JPATH_ROOT.$thumb_folder.'/index.html', "");
		}
        echo '
		<p style="border-radius:4px;display:block;border:1px solid #BCE8F1;padding:10px 15px;background:#D8EDF7;color:#31718F;font-weight:400;"> <strong><a style="color:#333;text-decoration:underline;" href="index.php?option=com_modules&view=modules&filter_search=&filter_module=mod_jt_contentslider">Open Module Manager</a></strong> to publish the module.</p><br/>';
    }

	function update($parent) 
	{
		$modulebase = "mod_jt_contentslider";
		$thumb_folder_0 ="/cache/".$modulebase."/";
		$thumb_folder ="/cache/".$modulebase."/";

		// Create thumbnail folder if not exist
		if (!JFolder::exists(JPATH_BASE.$thumb_folder)) {
			JFolder::create(JPATH_BASE.$thumb_folder);
			JFile::write(JPATH_BASE.$thumb_folder_0.'/index.html', "");
			JFile::write(JPATH_BASE.$thumb_folder.'/index.html', "");
		}
		echo '
		<p style="border-radius:4px;display:block;border:1px solid #BCE8F1;padding:10px 15px;background:#D8EDF7;color:#31718F;font-weight:400;">The module has been updated <strong><a style="color:#333;text-decoration:underline;" href="index.php?option=com_modules&view=modules&filter_search=&filter_module=mod_jt_contentslider">Open Module Manager</a></strong> to manage the module.</p><br/>';
	}
}