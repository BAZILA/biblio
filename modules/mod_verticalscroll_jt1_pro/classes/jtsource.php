<?php
/**
 * @package    Vertical scroller JT1 Pro Module for Joomla! 4.x/5.x/6.x
 * @version    3.0
 * @author     muratyil
 * @copyright  (C) 2013- muratyil
 * @license    GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

defined('_JEXEC') or die;

use Joomla\CMS\Date\Date;
use Joomla\CMS\Image\Image;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

// Include image helper
require_once __DIR__ . '/images.php';

/**
 * JT3 Data Source Base Class
 */
if (!class_exists('JT3Source')) {
    abstract class JT3Source
    {
        /**
         * @var string Thumbnail path
         */
        public $_thumbnailPath = '';

        /**
         * @var string Thumbnail URL
         */
        public $_thumbnaiURL = '';

        /**
         * @var string Default thumbnail
         */
        public $_defaultThumb = '';

        /**
         * @var array Images rendered
         */
        public $_imagesRendered = ['thumbnail' => [], 'mainImage' => []];

        /**
         * @var object Parameters
         */
        public $_params = [];

        /**
         * Constructor
         *
         * @param object $params Parameters
         */
        public function __construct($params = null)
        {
            $this->_params = $params;
        }

        /**
         * Set thumbnail path info
         *
         * @param string $path Path
         * @param string $url  URL
         * @param string $defaultThumb Default thumbnail
         *
         * @return $this
         */
        public function setThumbPathInfo($path, $url, $defaultThumb)
        {
            $this->_thumbnailPath = $path;
            $this->_thumbnaiURL = $url;
            $this->_defaultThumb = $defaultThumb;
            return $this;
        }

        /**
         * Set images rendered
         *
         * @param array $name Image names
         *
         * @return $this
         */
        public function setImagesRendered($name = [])
        {
            $this->_imagesRendered = $name;
            return $this;
        }

        /**
         * Render thumbnail
         *
         * @param string  $path    Image path
         * @param integer $width   Width
         * @param integer $height  Height
         * @param boolean $isThumb Is thumbnail
         * @param integer $quality Quality
         *
         * @return string
         */
        public function renderThumb($path, $width = 280, $height = 150, $isThumb = true, $quality = 100)
        {
            if ($isThumb) {
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
                
                $path = str_replace($baseUrl, '', $path);
                $imagSource = JPATH_SITE . '/' . $path;
                $imagSource = urldecode($imagSource);
                
                $imgClean = HTMLHelper::cleanImageURL($imagSource);
                if ($imgClean->url != '') {
                    $imagSource = $imgClean->url;
                }
                
                $tmp = explode('/', $imagSource);
                $imageName = md5($path . $width . $height) . '-' . $tmp[count($tmp) - 1];
                $thumbPath = $this->_thumbnailPath . $imageName;
                
                if (file_exists($imagSource)) {
                    if (!file_exists($thumbPath)) {
                        // Create thumb
                        VJTImageHelper::createImage($imagSource, $thumbPath, $width, $height, true, $quality);
                    }
                    return $this->_thumbnaiURL . $imageName;
                } else {
                    if (!file_exists($thumbPath)) {
                        // Try to load image from external source
                        if ($this->_CreateImageUsingCurl($path, $thumbPath, 30)) {
                            VJTImageHelper::createImage($thumbPath, $thumbPath, $width, $height, true, $quality);
                            return $this->_thumbnaiURL . $imageName;
                        }
                    } else {
                        return $this->_thumbnaiURL . $imageName;
                    }
                }
            }
            
            // Return path
            return $path;
        }

        /**
         * Create image using cURL
         *
         * @param string  $url               URL
         * @param string  $thumbPath         Thumbnail path
         * @param integer $maxImageLoadTime  Max load time
         *
         * @return boolean
         */
        private static function _CreateImageUsingCurl($url, $thumbPath, $maxImageLoadTime = 30)
        {
            $curl = false;
            if (function_exists('curl_init')) {
                $curl = curl_init();
            }
            
            if ($curl) {
                curl_setopt($curl, CURLOPT_URL, $url);
                curl_setopt($curl, CURLOPT_HEADER, false);
                curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, $maxImageLoadTime);
                curl_setopt($curl, CURLOPT_MAXREDIRS, 11);
                curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
                $contents = curl_exec($curl);
                curl_close($curl);
                file_put_contents($thumbPath, $contents);
                return true;
            }
            
            return false;
        }

        /**
         * Parse images from article content
         *
         * @param object $row Article object
         *
         * @return object
         */
        public function parseImages($row)
        {
            $introtext = true;
            if (trim($row->fulltext) && $this->_params->get('checkimg_fulltext', 0)) {
                $text = $row->introtext . $row->fulltext;
                $introtext = false;
            } else {
                $text = $row->introtext;
            }
            
            $row->thumbnail = $this->_defaultThumb;
            $row->mainImage = $this->_defaultThumb;

            $regex = "/\<img.+src\s*=\s*\"([^\"]*)\"[^\>]*\>/Us";
            
            if (!$this->_params->get('check_image_exist', 0)) {
                preg_match($regex, $text, $matches);
                $images = (count($matches)) ? $matches : [];
                if (count($images)) {
                    $row->mainImage = $images[1];
                    $row->thumbnail = $images[1];
                    if ($introtext) {
                        $row->introtext = str_replace($images[0], '', $row->introtext);
                    }
                }
            } else {
                preg_match_all($regex, $text, $matches);
                foreach ($matches[1] as $key => $match) {
                    @$url = getimagesize($match);
                    if (is_array($url)) {
                        $row->mainImage = $match;
                        $row->thumbnail = $match;
                        if ($introtext) {
                            $row->introtext = str_replace($matches[0][$key], '', $row->introtext);
                        }
                        break;
                    }
                }
            }

            return $row;
        }

        /**
         * Create thumb and save link to item
         *
         * @param object  $item    Item object
         * @param boolean $isThumb Is thumbnail
         * @param integer $quality Quality
         *
         * @return object
         */
        public function generateImages($item, $isThumb = true, $quality = 100)
        {
            $item = $this->parseImages($item);

            foreach ($this->_imagesRendered as $key => $value) {
                if ($item->{$key} && $image = $this->renderThumb($item->{$key}, $value[0], $value[1], $isThumb, $quality)) {
                    $item->{$key} = $image;
                }
            }
            
            return $item;
        }

        /**
         * Get a substring with the max length setting
         *
         * @param string  $text      Text
         * @param integer $length    Limit characters
         * @param string  $replacer  Replacer
         * @param boolean $isStrips  Strip tags
         * @param string  $stringtags Allowed tags
         *
         * @return string
         */
        public static function substring($text, $length = 100, $replacer = '...', $isStrips = true, $stringtags = '')
        {
            if ($isStrips) {
                $text = preg_replace('/\<p.*\>/Us', '', $text);
                $text = str_replace('</p>', '<br/>', $text);
                $text = strip_tags($text, $stringtags);
            }

            if (function_exists('mb_strlen')) {
                if (mb_strlen($text) < $length) {
                    return $text;
                }
                $text = mb_substr($text, 0, $length);
            } else {
                if (strlen($text) < $length) {
                    return $text;
                }
                $text = substr($text, 0, $length);
            }

            return $text . $replacer;
        }

        /**
         * Get a substring with the max word setting
         *
         * @param string  $text      Text
         * @param integer $length    Limit words
         * @param string  $replacer  Replacer
         * @param boolean $isStrips  Strip tags
         * @param string  $stringtags Allowed tags
         *
         * @return string
         */
        public static function substrword($text, $length = 100, $replacer = '...', $isStrips = true, $stringtags = '')
        {
            if ($isStrips) {
                $text = preg_replace('/\<p.*\>/Us', '', $text);
                $text = str_replace('</p>', '<br/>', $text);
                $text = strip_tags($text, $stringtags);
            }
            
            $tmp = explode(' ', $text);

            if (count($tmp) < $length) {
                return $text;
            }

            $text = implode(' ', array_slice($tmp, 0, $length)) . $replacer;

            return $text;
        }

        /**
         * Get list article (abstract function)
         *
         * @return array
         */
        abstract public function getList();
    }
}