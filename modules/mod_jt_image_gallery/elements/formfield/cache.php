<?php
/**
* @title		JT Image Gallery module
* @website		http://www.joomlatema.net
* @copyright	Copyright (C) 2008 -2023 JoomlaTema.net. All rights reserved.
* @license		http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
**/
// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Form\FormField;
use Joomla\CMS\Filesystem\Folder;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

use Joomla\CMS\Router\Route;

jimport('joomla.form.formfield');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $delete = filter_input(INPUT_POST, 'delete', FILTER_DEFAULT, FILTER_REQUIRE_SCALAR);
if ($delete !== null) {
    $delete = htmlspecialchars($delete, ENT_QUOTES, 'UTF-8');
}

    if (!empty($delete)) {
        // Find the file
        $folder_path = JPATH_SITE .'/cache/mod_jt_image_gallery/thumbs';

        // List of name of files inside specified folder
        $files = glob($folder_path.'/*.{jpg,png,gif,webp}', GLOB_BRACE);

        foreach ($files as $file) {
            if (is_file($file)) {
                // Delete the given file
                unlink($file); 
            }
        }
    }
}

class JFormFieldCache extends FormField {
	protected $type = 'Cache';
	// getLabel() left out
	public function getInput() {
		return '
<form id="formGoBack" action="" method="post">
    <input type="hidden" name="formGoBack" class="btn btn-primary" ></input>
</form>
 <form method="post">
  <input type="hidden" value="delete" name="delete" />
 <input class="button-cancel btn btn-danger" type="submit" value="'.Text::_('JTIMG_GALLERY_THUMBNAIL_DELETE_BUTTON').'" /></input>
 </form>';
	}
}
      
?>