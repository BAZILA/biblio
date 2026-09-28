<?php
/**
 * @package     mod_jt_contentslider
 * @subpackage  Site
 * @copyright   Copyright (C)  JoomlaTema
 * @license     GNU/GPL v2 or later
 */

namespace Joomla\Module\JTContentSlider\Site\Helper;

\defined('_JEXEC') or die;

use Joomla\CMS\Access\Access;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Router\Route;
use Joomla\Component\Content\Site\Model\ArticlesModel;
use Joomla\Registry\Registry;
use Joomla\Component\Content\Site\Helper\RouteHelper;
use Joomla\CMS\Date\Date;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

/**
 * Get base URL - compatible with Joomla 5/6
 */
if (!function_exists('Joomla\Module\JTContentSlider\Site\Helper\getBaseUrlHelper')) {
    function getBaseUrlHelper() {
        $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        $script = $_SERVER['SCRIPT_NAME'] ?? '';
        $base = dirname($script);
        if ($base == '/' || $base == '\\') {
            $base = '';
        }
        return $scheme . '://' . $host . $base . '/';
    }
}

/**
 * Image thumbnail generator utility
 */
class ThumbImage
{
    /**
     * Create thumbnail image with proper aspect ratio handling
     * 
     * @param string $src Source image path
     * @param string $dest Destination thumbnail path
     * @param int $targetWidth Target width in pixels
     * @param int|null $targetHeight Target height in pixels (null for aspect ratio)
     * @return bool Success status
     */
    public function createThumbnail($src, $dest, $targetWidth, $targetHeight = null)
    {
        if (!file_exists($src)) {
            return false;
        }

        $type = @exif_imagetype($src);
        if ($type === false) {
            return false;
        }

        $handlers = [
            IMAGETYPE_JPEG => ['load' => 'imagecreatefromjpeg', 'save' => 'imagejpeg', 'quality' => 100],
            IMAGETYPE_PNG  => ['load' => 'imagecreatefrompng', 'save' => 'imagepng', 'quality' => 7],
            IMAGETYPE_GIF  => ['load' => 'imagecreatefromgif', 'save' => 'imagegif'],
            IMAGETYPE_WEBP => ['load' => 'imagecreatefromwebp', 'save' => 'imagewebp', 'quality' => 90],
        ];

        if (!isset($handlers[$type])) {
            return false;
        }

        try {
            $image = @call_user_func($handlers[$type]['load'], $src);
            if (!$image) {
                return false;
            }

            $width = imagesx($image);
            $height = imagesy($image);

            // Calculate dimensions maintaining aspect ratio if needed
            if ($targetHeight == null) {
                $ratio = $width / $height;
                if ($width > $height) {
                    $targetHeight = floor($targetWidth / $ratio);
                } else {
                    $targetHeight = $targetWidth;
                    $targetWidth = floor($targetWidth * $ratio);
                }
            }

            $thumbnail = imagecreatetruecolor($targetWidth, $targetHeight);
            if (!$thumbnail) {
                imagedestroy($image);
                return false;
            }

            // Preserve transparency for PNG and GIF
            if ($type == IMAGETYPE_GIF || $type == IMAGETYPE_PNG) {
                imagecolortransparent($thumbnail, imagecolorallocate($thumbnail, 0, 0, 0));
                if ($type == IMAGETYPE_PNG) {
                    imagealphablending($thumbnail, false);
                    imagesavealpha($thumbnail, true);
                }
            }

            imagecopyresampled($thumbnail, $image, 0, 0, 0, 0, $targetWidth, $targetHeight, $width, $height);

            $result = false;
            if ($type == IMAGETYPE_GIF) {
                $result = call_user_func($handlers[$type]['save'], $thumbnail, $dest);
            } else {
                $result = call_user_func($handlers[$type]['save'], $thumbnail, $dest, $handlers[$type]['quality']);
            }


            return $result;
        } catch (\Exception $e) {
            return false;
        }
    }
}

/**
 * Helper class for JT Content Slider
 */
abstract class JTContentSliderHelper
{
    /**
     * Safe htmlspecialchars that handles non-string values
     * 
     * @param mixed $value Value to escape
     * @param string $default Default value if input is invalid
     * @return string Escaped string
     */
    public static function safeHtml($value, $default = '')
    {
        if (is_array($value) || is_object($value)) {
            return $default;
        }
        return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
    }

    /**
     * Safe HTML for icon markup - allows only <i> and <span> tags for icon fonts
     * Strips all other HTML and dangerous attributes
     * 
     * @param mixed $value Value to sanitize
     * @param string $default Default value if input is invalid
     * @return string Sanitized HTML safe for icons
     */
    public static function safeIconHtml($value, $default = '')
    {
        if (is_array($value) || is_object($value)) {
            return $default;
        }
        
        $value = (string)$value;
        
        // Allow only <i> and <span> tags (common for icon fonts like FontAwesome, Material Icons)
        $value = strip_tags($value, '<i><span>');
        
        // Remove any event handlers and dangerous attributes
        $value = preg_replace('/\s*on\w+\s*=\s*["\'][^"\']*["\']/i', '', $value);
        $value = preg_replace('/\s*javascript:\s*/i', '', $value);
        $value = preg_replace('/\s*data-[a-z-]+\s*=\s*["\'][^"\']*["\']/i', '', $value);
        
        return $value;
    }

    /**
     * Get all subcategories recursively
     * 
     * @param array|int $categoryIds Parent category IDs
     * @param object|null $user User object for access checks
     * @param int $depth Maximum recursion depth
     * @return array All category IDs including subcategories
     */
    public static function getSubcategories($categoryIds, $user = null, $depth = 10)
    {
        if (empty($categoryIds) || $depth <= 0) {
            return (array) $categoryIds;
        }

        $db = Factory::getDbo();
        $user = $user ?: Factory::getUser();
        $authorised = Access::getAuthorisedViewLevels($user->id);

        $allCategoryIds = [];
        $processedIds = [];
        $toProcess = (array) $categoryIds;

        while (!empty($toProcess) && $depth > 0) {
            $currentBatch = [];

            foreach ($toProcess as $catId) {
                $catId = (int) $catId;
                if ($catId > 0 && !in_array($catId, $processedIds)) {
                    $currentBatch[] = $catId;
                    $processedIds[] = $catId;
                    $allCategoryIds[] = $catId;
                }
            }

            if (empty($currentBatch)) {
                break;
            }

            $query = $db->getQuery(true)
                ->select($db->quoteName('id'))
                ->from($db->quoteName('#__categories'))
                ->where($db->quoteName('parent_id') . ' IN (' . implode(',', $currentBatch) . ')')
                ->where($db->quoteName('published') . ' = 1')
                ->where($db->quoteName('extension') . ' = ' . $db->quote('com_content'));

            if (!empty($authorised)) {
                $query->where($db->quoteName('access') . ' IN (' . implode(',', $authorised) . ')');
            }

            $db->setQuery($query);

            try {
                $toProcess = $db->loadColumn();
            } catch (\RuntimeException $e) {
                Factory::getApplication()->enqueueMessage($e->getMessage(), 'error');
                $toProcess = [];
            }

            $depth--;
        }

        return array_unique($allCategoryIds);
    }

    /**
     * Get list of articles with subcategory support
     * 
     * @param Registry $params Module parameters
     * @param ArticlesModel $model Articles model
     * @return array List of articles
     */
    public static function getList(Registry $params, ArticlesModel $model)
    {
        $db   = Factory::getDbo();
        $user = Factory::getUser();

        // Set application parameters in model
        $app       = Factory::getApplication();
        $appParams = $app->getParams();
        $model->setState('params', $appParams);

        // Set the filters based on the module params
        $model->setState('list.start', (int) $params->get('num_intro_skip', 0));
        $model->setState('list.limit', (int) $params->get('count', 5));
        $model->setState('filter.published', 1);

        // This module does not use tags data
        $model->setState('load_tags', false);

        // Access filter
        $access     = !ComponentHelper::getParams('com_content')->get('show_noauth');
        $authorised = Access::getAuthorisedViewLevels($user->get('id'));
        $model->setState('filter.access', $access);

        // Category filter with subcategory support
        $selectedCategories = $params->get('catid', array());
        $includeSubcategories = $params->get('include_subcategories', 0);
        $subcatDepth = (int) $params->get('subcategories_depth', 10);

        if ($includeSubcategories && !empty($selectedCategories)) {
            // Get all subcategories recursively
            $allCategories = self::getSubcategories($selectedCategories, $user, $subcatDepth);
            $model->setState('filter.category_id', $allCategories);
        } else {
            // Use only selected categories (original behavior)
            $model->setState('filter.category_id', $selectedCategories);
        }

        // User filter
        $userId = $user->get('id');

        switch ($params->get('user_id'))
        {
            case 'by_me':
                $model->setState('filter.author_id', (int) $userId);
                break;
            case 'not_me':
                $model->setState('filter.author_id', $userId);
                $model->setState('filter.author_id.include', false);
                break;

            case 'created_by':
                $model->setState('filter.author_id', $params->get('author', array()));
                break;

            case '0':
                break;

            default:
                $model->setState('filter.author_id', (int) $params->get('user_id'));
                break;
        }

        // Filter by language
        $model->setState('filter.language', $app->getLanguageFilter());

        // Featured switch
        $featured = $params->get('show_featured', '');

        if ($featured === '')
        {
            $model->setState('filter.featured', 'show');
        }
        elseif ($featured)
        {
            $model->setState('filter.featured', 'only');
        }
        else
        {
            $model->setState('filter.featured', 'hide');
        }

        // Set ordering
        $order_map = array(
            'a.created'       => 'a.created',
            'a.publish_up'    => 'a.publish_up',
            'a.ordering'      => 'a.ordering',
            'fp.ordering'     => 'fp.ordering',
            'a.hits'          => 'a.hits',
            'a.title'         => 'a.title',
            'a.id'            => 'a.id',
            'a.alias'         => 'a.alias',
            'modified'        => 'a.modified',
            'a.publish_down'  => 'a.publish_down',
            'rating_count'    => 'rating_count',
            'random'          => $db->getQuery(true)->Rand(),
        );

        $ordering = $order_map[$params->get('ordering', 'a.publish_up')] ?? 'a.publish_up';

        $model->setState('list.ordering', $ordering);
        $model->setState('list.direction', $params->get('article_ordering_direction', 'ASC'));

        $items = $model->getItems();

        foreach ($items as &$item)
        {
            $item->slug = $item->id . ':' . $item->alias;

            if ($access || in_array($item->access, $authorised))
            {
                // We know that user has the privilege to view the article
                $item->link = Route::_(RouteHelper::getArticleRoute($item->slug, $item->catid, $item->language));
            }
            else
            {
                $item->link = Route::_('index.php?option=com_users&view=login');
            }
        }

        return $items;
    }
    
    /**
     * Display formatted date
     * 
     * @param int $show_date Show date flag
     * @param int $show_date_type Date format type
     * @param string $created_date Creation date
     * @param string $custom_date_format Custom date format
     */
    public static function getDate($show_date, $show_date_type, $created_date, $custom_date_format)
    {
        $date = new Date($created_date);
        if ($show_date == 1)
        {
            switch($show_date_type) {
                case 1:
                    echo "<span class=\"jtc_introdate\">";
                    echo HTMLHelper::_('date', $date, Text::_('l, d F Y H:i'));
                    echo "<br/></span>";
                    break;
                case 2:
                    echo "<span class=\"jtc_introdate\">";
                    echo HTMLHelper::_('date', $date, Text::_('d F Y'));
                    echo "<br/></span>";
                    break;
                case 3:
                    echo "<span class=\"jtc_introdate\">";
                    echo HTMLHelper::_('date', $date, Text::_('H:i'));
                    echo "<br/></span>";
                    break;
                case 4:
                    echo "<span class=\"jtc_introdate\">";
                    echo HTMLHelper::_('date', $date, Text::_('D, M jS Y'));
                    echo "<br/></span>";
                    break;
                case 5:
                    echo "<span class=\"jtc_introdate\">";
                    echo HTMLHelper::_('date', $date, Text::_('l, F jS Y H:i'));
                    echo "<br/></span>";
                    break;
                case 6:
                    echo "<span class=\"jtc_introdate\">";
                    echo HTMLHelper::_('date', $date, Text::_($custom_date_format));
                    echo "<br/></span>";
                    break;
                default:
                    echo "<span class=\"jtc_introdate\">";
                    echo HTMLHelper::_('date', $date, Text::_('l, d F Y'));
                    echo "<br/></span>";
                    break;
            }
        }
    }
    
    /**
     * Get original image path
     * 
     * @param int $item_id Article ID
     * @param string $article_images Article images JSON
     * @param string $title Article title
     * @param string $introtext Article intro text
     * @param string $modulebase Module base name
     * @return string Original image path
     */
    public static function getOrgImage($item_id, $article_images, $title, $introtext, $modulebase)
    {
        $baseUrl = getBaseUrlHelper();
        $thumb_name = str_replace([':', '\\', '/', '*', '\'', '"'], '', str_replace(' ', '_', strtolower($item_id)));
        
        if ($article_images) {
            $images = json_decode($article_images);
            
            // Find Article's Image
            if (!empty($images->image_intro)) { 
                $orig_image = strstr($images->image_intro, '#', true);
                if ($orig_image == null) {
                    $orig_image = $images->image_intro;
                }
            } elseif (empty($images->image_intro) && !empty($images->image_fulltext)) { 
                $orig_image = strstr($images->image_fulltext, '#', true);
                if ($orig_image == null) {
                    $orig_image = $images->image_fulltext;
                }
            } else {
                // Find first image in the article
                $html = $introtext;
                $pattern = '/<img .*?src="([^"]+)"/si';

                if (preg_match($pattern, $html, $match)) {
                    $orig_image = $match[1];
                } else {
                    $orig_image = "";
                }
            }
            
            // Replace %20 character for image's name with space
            $orig_image = str_replace('%20', ' ', $orig_image);

            // Ignore external images
            if (strpos($orig_image, 'http') !== false) {
                $orig_image = "";
            }

            // If article contains an image then use it
            if ($orig_image != "") {
                return $orig_image;
            } else {
                return $baseUrl . 'modules/' . $modulebase . '/tmpl/assets/images/default.jpg';
            }
        }
        
        return $baseUrl . 'modules/' . $modulebase . '/tmpl/assets/images/default.jpg';
    }
    
    /**
     * Get image caption
     * 
     * @param int $item_id Article ID
     * @param string $article_images Article images JSON
     * @param string $introtext Article intro text
     * @param int $use_caption Use caption flag
     * @return string Image caption
     */
    public static function getCaption($item_id, $article_images, $introtext, $use_caption)
    {
        $caption = '';
        
        if ($article_images && $use_caption) {
            $images = json_decode($article_images);
            
            if (!empty($images->image_intro)) { 
                $caption = $images->image_intro_caption ?? '';
                if (!$caption) {
                    $caption = $images->image_fulltext_caption ?? '';
                }
            }
        }
        
        return $caption;
    }
    
    /**
     * Truncate string by character count
     * 
     * @param string $text Text to truncate
     * @param int $strip_tags Strip HTML tags flag
     * @param string $allowed_tags Allowed HTML tags
     * @param string $replacer End string replacer
     * @param int $limit Character limit
     * @return string Truncated text
     */
    public static function substring($text, $strip_tags, $allowed_tags = '', $replacer = '...', $limit = 200)
    {
        if ($strip_tags) {
            $text = strip_tags($text, $allowed_tags);
        }

        $limit = (int) $limit;

        if (function_exists('mb_strlen')) {
            if (mb_strlen($text) <= $limit) {
                return $text;
            }
            $text = mb_substr($text, 0, $limit);
        } else {
            if (strlen($text) <= $limit) {
                return $text;
            }
            $text = substr($text, 0, $limit);
        }

        return $text . $replacer;
    }

    /**
     * Truncate string by word count
     * 
     * @param string $text Text to truncate
     * @param int $strip_tags Strip HTML tags flag
     * @param string $allowed_tags Allowed HTML tags
     * @param string $replacer End string replacer
     * @param int $limit Word limit
     * @return string Truncated text
     */
    public static function substrword($text, $strip_tags, $allowed_tags = '', $replacer = '...', $limit = 200)
    {
        if ($strip_tags) {
            $text = strip_tags($text, $allowed_tags);
        }

        $tmp = explode(" ", $text);
        $limit = (int) $limit;

        if (count($tmp) <= $limit) {
            return $text;
        }

        $text = implode(" ", array_slice($tmp, 0, $limit)) . $replacer;

        return $text;
    }

    /**
     * Get or generate thumbnail image
     * 
     * @param int $item_id Article ID
     * @param string $article_images Article images JSON
     * @param string $thumb_folder Thumbnail folder path
     * @param int $show_default_thumb Show default thumbnail flag
     * @param int $thumb_width Thumbnail width
     * @param mixed $thumb_height Thumbnail height (empty string for aspect ratio)
     * @param string $title Article title
     * @param string $introtext Article intro text
     * @param string $modulebase Module base name
     * @return string Thumbnail HTML
     */
    public static function getThumbnail($item_id, $article_images, $thumb_folder, $show_default_thumb, $thumb_width, $thumb_height, $title, $introtext, $modulebase)
    {
        $baseUrl = getBaseUrlHelper();
        $thumb_name = str_replace([':', '\\', '/', '*', '\'', '"'], '', str_replace(' ', '_', strtolower($item_id)));
        $thumb_name = md5($thumb_width . $thumb_height) . '_' . $thumb_name;

        $thumbPath  = JPATH_BASE . $thumb_folder . $thumb_name . '.jpg';
        $folderPath = JPATH_BASE . $thumb_folder;

        // Create thumbnail folder if not exist
        if (!is_dir($folderPath)) {
            @mkdir($folderPath, 0755, true);
            @file_put_contents($folderPath . '/index.html', '');
        }

        // If thumbnail does not exist, generate it
        if (!is_file($thumbPath)) {
            $images = json_decode($article_images);

            // Find Article's Image
            if (!empty($images->image_intro)) {
                $orig_image = strstr($images->image_intro, '#', true);
                if ($orig_image == null) {
                    $orig_image = $images->image_intro;
                }
            } elseif (empty($images->image_intro) && !empty($images->image_fulltext)) {
                $orig_image = strstr($images->image_fulltext, '#', true);
                if ($orig_image == null) {
                    $orig_image = $images->image_fulltext;
                }
            } else {
                // Find first image in intro text
                $html = $introtext;
                $pattern = '/<img .*?src="([^"]+)"/si';
                if (preg_match($pattern, $html, $match)) {
                    $orig_image = $match[1];
                } else {
                    $orig_image = '';
                }
            }

            // Replace %20 character for image's name with space
            $orig_image = str_replace('%20', ' ', $orig_image);

            // Ignore external images
            if (strpos($orig_image, 'http') !== false) {
                $orig_image = '';
            }

            // Generate thumbnail if an image exists
            if ($orig_image != '' && file_exists($orig_image)) {
                $thumb_img = new ThumbImage();
                $imgext = explode('.', $orig_image);
                $ext = end($imgext);
                
                $success = $thumb_img->createThumbnail(
                    $orig_image, 
                    $folderPath . $thumb_name . '.' . $ext, 
                    $thumb_width, 
                    $thumb_height === '' ? null : (int)$thumb_height
                );
                
                if ($success) {
                    $thumb_img = '<img class="jtcs-image" src="' . $baseUrl . $thumb_folder . $thumb_name . '.' . $ext . '" alt="' . htmlspecialchars($title, ENT_QUOTES, 'UTF-8') . '" width="' . $thumb_width . '"/>';
                } else {
                    $thumb_img = '';
                }
            } else {
                // No image -> default or empty
                if ($show_default_thumb) {
                    $default_thumb = JPATH_BASE . '/modules/' . $modulebase . '/tmpl/assets/images/default.jpg';
                    if (file_exists($default_thumb)) {
                        $thumb_img_obj = new ThumbImage();
                        $thumb_img_obj->createThumbnail(
                            $default_thumb, 
                            $thumbPath, 
                            $thumb_width, 
                            $thumb_height === '' ? null : (int)$thumb_height
                        );
                        $thumb_img = '<img class="jtcs-image" src="' . $baseUrl . $thumb_folder . $thumb_name . '.jpg" alt="' . htmlspecialchars($title, ENT_QUOTES, 'UTF-8') . '" width="' . $thumb_width . '"/>';
                    } else {
                        $thumb_img = '';
                    }
                } else {
                    $thumb_img = '';
                }
            }
        } else {
            // Thumbnail already exists
            $thumb_img = '<img class="jtcs-image" src="' . $baseUrl . $thumb_folder . $thumb_name . '.jpg" width="' . $thumb_width . '" alt="' . htmlspecialchars($title, ENT_QUOTES, 'UTF-8') . '" />';
        }

        return $thumb_img;
    }
}