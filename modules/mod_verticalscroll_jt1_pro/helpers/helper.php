<?php
/**
 * @package    Vertical scroller JT1 Pro Module for Joomla! 4.x/5.x/6.x
 * @version    3.0
 * @author     muratyil
 * @copyright  (C) 2013- muratyil
 * @license    GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;

/**
 * Helper class for VerticalScroller JT Pro module
 */
class modVJTProHeadLineHelper
{
    /**
     * Get base URL - compatible with Joomla 4/5/6
     *
     * @return string
     */
    private static function getBaseUrl()
    {
        // Try different methods for maximum compatibility
        if (defined('JURI::base')) {
            return JURI::base();
        }
        
        // Fallback to building URL from server variables
        $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        $script = $_SERVER['SCRIPT_NAME'] ?? '';
        $base = dirname($script);
        
        if ($base == '/' || $base == '\\') {
            $base = '';
        }
        
        return $scheme . '://' . $host . $base . '/';
    }

    /**
     * Get list articles
     *
     * @param   object  $params  Module parameters
     * @param   object  $module  Module object
     *
     * @return  array   List of items
     */
    public static function getList(&$params, $module)
    {
        $baseUrl = self::getBaseUrl();
        
        $thumbPath = JPATH_BASE . '/cache/' . $module->module . '/';
        $thumbUrl = str_replace(JPATH_BASE . '/', $baseUrl, $thumbPath);
        $defaultThumb = $baseUrl . 'modules/' . $module->module . '/tmpl/images/no-image.jpg';

        // Create cache directory if it doesn't exist
        if (!is_dir($thumbPath)) {
            if (!mkdir($thumbPath, 0755, true)) {
                // If mkdir fails, try to continue anyway
                error_log('Could not create cache directory: ' . $thumbPath);
            }
        }

        // Get source from params
        $source = $params->get('source', 'category');
        
        // Only support content source (no K2)
        if ($source == 'category' || $source == 'article_ids' || $source == 'joomla_tags') {
            $source = 'content';
        } else {
            $source = 'content';
        }

        $path = JPATH_SITE . '/modules/mod_verticalscroll_jt1_pro/classes/' . $source . '.php';
        
        if (!file_exists($path)) {
            return [];
        }

        require_once $path;
        
        $objectName = 'Bt' . ucfirst($source) . 'DataSource';
        
        if (!class_exists($objectName)) {
            return [];
        }

        $object = new $objectName($params);

        // Set images path and render thumb, then get list
        $items = $object->setThumbPathInfo($thumbPath, $thumbUrl, $defaultThumb)
            ->setImagesRendered([
                'thumbnail' => [
                    (int)$params->get('thumbnail_width', 60),
                    (int)$params->get('thumbnail_height', 60)
                ]
            ])
            ->getList();

        return $items;
    }
}