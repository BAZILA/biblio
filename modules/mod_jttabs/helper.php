<?php
/**
 * @package     mod_jttabs
 * @copyright   Copyright (C) 2007 - 2023 http://www.joomlatema.net, Inc. All rights reserved.
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 * @author      JoomlaTema.Net
 * @link        http://www.joomlatema.net
 **/
// no direct access
defined('_JEXEC') or die ('Restricted access');

use Joomla\CMS\Factory;
use Joomla\CMS\Router\Route;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\Component\Content\Site\Helper\RouteHelper;

class modJtTabsHelper
{
    public static function getArticle($id, $params)
    {
        $db = Factory::getDbo();
        $query = $db->getQuery(true)
            ->select($db->quoteName(['id', 'title', 'introtext', 'fulltext', 'images', 'catid']))
            ->from($db->quoteName('#__content'))
            ->where($db->quoteName('state') . ' = 1')
            ->where($db->quoteName('id') . ' = ' . (int) $id);
        
        $db->setQuery($query);
        
        try {
            $article = $db->loadObject();
        } catch (RuntimeException $e) {
            Factory::getApplication()->enqueueMessage($e->getMessage(), 'error');
            return;
        }
        
        if (empty($article->id)) {
            return;
        }
        
        // Generate the correct article URL using Joomla's routing helper
        $articleUrl = Route::_(RouteHelper::getArticleRoute($article->id, $article->catid));
        
        // Retrieve images stored in JSON format
        $images = json_decode($article->images);
        $introImage = isset($images->image_intro) && !empty($images->image_intro) ? $images->image_intro : null;
        $fullTextImage = isset($images->image_fulltext) && !empty($images->image_fulltext) ? $images->image_fulltext : null;
        
        // Get the selected image type from admin settings
        $imageType = $params->get('image_type', 'intro');
        $selectedImage = ($imageType === 'full') ? $fullTextImage : $introImage;
        
        // Display article title and content
        echo '<h2>' . htmlspecialchars($article->title) . '</h2>';
        
        if ($selectedImage) {
            echo '<figure class="jttabs-img"><img src="' . htmlspecialchars($selectedImage) . '" alt="' . htmlspecialchars($article->title) . '" style="max-width: 100%; height: auto;"></figure>';
        }
        
        echo $article->introtext;
        
        // Read More button
        if (!empty($article->fulltext)) {
            echo '<a class="btn btn-primary" href="' . $articleUrl . '">' . Text::_('MOD_JTTABS_READ_MORE') . '</a>';
        }
    }
}