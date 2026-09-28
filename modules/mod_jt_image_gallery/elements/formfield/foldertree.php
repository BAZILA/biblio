<?php
/**
* @title        JT Image Gallery module
* @website      http://www.joomlatema.net
* @copyright    Copyright (C) 2008 - 2025 JoomlaTema.net. All rights reserved.
* @license      http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
**/

defined('_JEXEC') or die;

use Joomla\CMS\Form\FormField;
use Joomla\Filesystem\Folder;
use Joomla\CMS\HTML\HTMLHelper;

class JFormFieldFolderTree extends FormField
{
    protected $type = 'FolderTree';

    protected function getInput()
    {
        $options = [];

        // --> Güvenlik: XML içinde belirtilen dizin degeri kullanici girdisi sayilabilir.
        //     Burada sadece basit normalizasyon yapiyoruz. Daha ileri kontroller istenirse ekleyelim.
        $directory = (string) $this->element['directory'];
        $directory = trim($directory);

        // Prevent attempts at directory traversal
        $directory = str_replace(['..', './', '\\'], '', $directory);

        // Build absolute path
        $path = JPATH_ROOT . DIRECTORY_SEPARATOR . ltrim($directory, DIRECTORY_SEPARATOR);

        // Optional filter and maxLevel from XML (default values)
        $filter = (string) $this->element['filter'] ?? '.';
        $maxLevel = (int) ($this->element['maxLevel'] ?? 50);

        // Ensure path exists and is a dir
        if (!is_dir($path)) {
            // Return an empty select with a message option
            $options[] = HTMLHelper::_('select.option', '', Text::_('JLIB_FORM_FIELD_FOLDERTREE_NO_DIR'));
            $attributes = 'class="form-select" multiple="multiple"';
            return HTMLHelper::_('select.genericlist', $options, $this->name . '[]', $attributes, 'value', 'text', $this->value);
        }

        // Get folder tree using Joomla\Filesystem\Folder
        // listFolderTree signature: listFolderTree($path, $filter, $maxLevel = 3, $level = 0, $parent = 0)
        $folders = Folder::listFolderTree($path, $filter, $maxLevel);

        foreach ($folders as $folder) {
            // Normalize relname to use forward slashes and remove leading /
            $rel = str_replace(DIRECTORY_SEPARATOR, '/', $folder['relname']);
            $rel = ltrim($rel, '/');
            $options[] = HTMLHelper::_('select.option', $rel, $rel);
        }

        $attributes = 'class="form-select" multiple="multiple"';

        return HTMLHelper::_('select.genericlist', $options, $this->name . '[]', $attributes, 'value', 'text', (array) $this->value);
    }
}
