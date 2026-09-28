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
use Joomla\CMS\Form\FormField;
use Joomla\CMS\Language\Text;

class JFormFieldAsset extends FormField
{
    protected $type = 'Asset';

    protected function getInput()
    {
        $document = Factory::getDocument();
        $wa = $document->getWebAssetManager();

        // Get base URL helper
        $getBaseUrl = function() {
            if (defined('JURI::base')) {
                return JURI::base();
            }
            $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
            $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
            $script = $_SERVER['SCRIPT_NAME'] ?? '';
            $base = dirname($script);
            if ($base == '/' || $base == '\\') {
                $base = '';
            }
            return $scheme . '://' . $host . $base . '/';
        };
        $baseUrl = $getBaseUrl();

        // In Joomla 4+, we use Web Asset Manager
        // Register and use colorpicker assets
        $wa->registerAndUseScript(
            'mod_verticalscroll_colorpicker',
            $baseUrl . $this->element['path'] . 'js/colorpicker/colorpicker.js',
            [],
            ['defer' => true]
        );

        $wa->registerAndUseScript(
            'mod_verticalscroll_jt',
            $baseUrl . $this->element['path'] . 'js/jt.js',
            [],
            ['defer' => true]
        );

        // Add CSS
        $wa->registerAndUseStyle(
            'mod_verticalscroll_jt_css',
            $baseUrl . $this->element['path'] . 'css/jt.css'
        );

        $wa->registerAndUseStyle(
            'mod_verticalscroll_colorpicker_css',
            $baseUrl . $this->element['path'] . 'js/colorpicker/colorpicker.css'
        );

        return null;
    }
}