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
use Joomla\CMS\Helper\ModuleHelper;
use Joomla\CMS\HTML\HTMLHelper;

// Load jQuery framework
HTMLHelper::_('jquery.framework');

// Load helper
require_once __DIR__ . '/helpers/helper.php';

// Get application and document
$app = Factory::getApplication();
$document = Factory::getDocument();
$wa = $document->getWebAssetManager();
$moduleId = $module->id;

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

// Get parameters
$target = $params->get('target', '');
$speed = $params->get('speed', '500');
$pause = $params->get('pause', '500');
$mousePause = $params->get('mousePause', 'false');
$height = $params->get('height', '150');
$height2 = $params->get('height2', '150');
$background2 = $params->get('background2', '#f0f0f0');
$padding2 = $params->get('padding2', '5');

// Get list content
$list = modVJTProHeadLineHelper::getList($params, $module);

// ROW * COL
$itemsPerRow = 1;
$itemsPerCol = 1;
$moduleclass_sfx = $params->get('moduleclass_sfx');
$imgClass = $params->get('hovereffect');
$modal = $params->get('modalbox');

// Num of item display
$maxPages = $itemsPerRow * $itemsPerCol;

// Get pages list array
$pages = array_chunk($list, $maxPages);

// Get total pages
$totalPages = count($pages);

// Calculate width of each row (percent)
$itemWidth = 100 / $itemsPerRow;

$tmp = $params->get('module_height', 'auto');
$moduleHeight = ($tmp == 'auto') ? 'auto' : (int)$tmp . 'px';
$tmp = $params->get('module_width', 'auto');
$moduleWidth = ($tmp == 'auto') ? 'auto' : ((int)$tmp) . 'px';
$moduleWidthWrapper = ($tmp == 'auto') ? 'auto' : (int)$tmp . 'px';

// Get Open target
$openTarget = $params->get('open_target', '_parent');

// Auto start
$auto_start = $params->get('auto_start', 1);

// Bullet and next back
$next_back = $params->get('next_back', 0);
$butlet = $params->get('butlet', 1);

// Option for content
$showTitle = $params->get('show_title', '1');
$show_category_name = $params->get('show_category_name', 0);
$show_category_name_as_link = $params->get('show_category_name_as_link', 0);
$showDate = $params->get('show_date', '0');
$showAuthor = $params->get('show_author', '0');
$show_intro = $params->get('show_intro', '0');

// Option for image
$thumbWidth = (int)$params->get('thumbnail_width', 200);
$thumbHeight = (int)$params->get('thumbnail_height', 150);
$image_crop = $params->get('image_crop', '0');
$show_image = $params->get('show_image', '0');
$effect = $params->get('next_back_effect', 'slider') . ',' . $params->get('bullet_effect', 'slider');
$slideEasing = $params->get('effect', 'easeInQuad');
$preloadImg = $baseUrl . 'modules/mod_verticalscroll_jt1_pro/tmpl/images/loading.gif';
$paging = $params->get('butlet', 0) > 0 ? 'true' : 'false';
$play = $auto_start ? (int)$params->get('interval', '5000') : 0;
$hoverPause = $params->get('pause_hover', 1) > 0 ? 'true' : 'false';
$duration = (int)$params->get('duration', '1000');
$autoHeight = $params->get('auto_height', 0) > 0 ? 'true' : 'false';
$fadeSpeed = (int)$params->get('duration', '1000');
$modid = '#verticalscroll_jt1' . $module->id;
$news_thumb_width = 'width:' . ($params->get('news_thumb_width') - 5) . 'px';
$moduleheight = $params->get('moduleheight');
$modulewidth = $params->get('modulewidth');
$newsbg = $params->get('newsbg');
$panelWidthCss = 'width:' . ($params->get('news_thumb_width') - 5) . 'px';
$enableLink = $params->get('jt3_link_limage');
$titlecolor = $params->get('titlecolor');
$textcolor = $params->get('textcolor');
$readoncolor = $params->get('readoncolor');
$readonposition = $params->get('readonposition');
$previous_text = $params->get('previous_text');
$next_text = $params->get('next_text');
$imagefloat = $params->get('imagefloat');
$imagemargin = $params->get('imagemargin');
$enablefade = $params->get('enablefade');
$showreadone = $params->get('show_readmore') == 1;
$showintrotext = $params->get('showintrotext') == 1;
$showtitle = $params->get('show_title') == 1;
$show_all_text = $params->get('show_all_text');
$readon_text = $params->get('readon_text');
$padding = $params->get('padding');
$little_thumbwidth = $params->get('little_thumbwidth');
$little_thumbheight = $params->get('little_thumbheight');
$little_thumbbg = $params->get('little_thumbbg');
$little_thumbpadding = $params->get('little_thumbpadding');
$showall_padding = (int)((float)$little_thumbheight - 12) / 2;
$showall = $params->get('showall');
$timming = $params->get('timming');
$fontsize = $params->get('fontsize');

if ($showall == 1) {
    $widthshow1 = $params->get('showallwidth');
    $widthshow2 = 'auto';
    $showallpadding = $widthshow1;
} else {
    $widthshow1 = '';
    $widthshow2 = '100%';
    $showallpadding = '5px';
}

$touchScreen = $params->get('touch_screen', 0);

// Get template
$template = $app->getTemplate();

// Add stylesheets
if (file_exists(JPATH_BASE . '/templates/' . $template . '/html/mod_verticalscroll_jt1_pro/css/verticalscroll_jt1.css.php')) {
    $wa->registerAndUseStyle(
        'mod_verticalscroll_jt1_custom',
        'templates/' . $template . '/html/mod_verticalscroll_jt1_pro/css/verticalscroll_jt1.css.php?id=' . $moduleId,
        [],
        [],
        []
    );
} else {
    $wa->registerAndUseStyle(
        'mod_verticalscroll_jt1',
        'modules/mod_verticalscroll_jt1_pro/tmpl/css/verticalscroll_jt1.css.php?id=' . $moduleId,
        [],
        [],
        []
    );
}

$wa->registerAndUseStyle(
    'mod_verticalscroll_jt1_fontawesome',
    'modules/mod_verticalscroll_jt1_pro/tmpl/css/font-awesome.css',
    [],
    [],
    []
);

// Check if jQuery should be loaded
$loadJquery = true;
switch ($params->get('loadJquery', 'auto')) {
    case '0':
        $loadJquery = false;
        break;
    case '1':
        $loadJquery = true;
        break;
    case 'auto':
        // In Joomla 4+, jQuery is loaded via HTMLHelper::_('jquery.framework')
        $loadJquery = false;
        break;
}

// Add JavaScript
$wa->registerAndUseScript(
    'mod_verticalscroll_jt1_vertical',
    'modules/mod_verticalscroll_jt1_pro/tmpl/js/jquery.vertical.js',
    [],
    ['defer' => true],
    ['jquery']
);

// Get tmpl
$align_image = strtolower($params->get('image_align', 'center'));
$equalHeight = $params->get('equalHeight', 0) > 0 && $align_image == 'center' ? 'true' : 'false';

// Load layout
require ModuleHelper::getLayoutPath($module->module, $params->get('layout', 'default'));