<?php
/**
 * @package    Vertical scroller JT1 Pro Module for Joomla! 4.x/5.x/6.x
 * @version    3.0
 * @author     muratyil
 * @copyright  (C) 2013- muratyil
 * @license    GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

defined('_JEXEC') or die;

if (!class_exists('VJTImageHelper')) {
    class VJTImageHelper
    {
        /**
         * Get image create function based on type
         *
         * @param   string  $type  Image type
         *
         * @return  string
         */
        static function getImageCreateFunction($type)
        {
            switch ($type) {
                case 'jpeg':
                case 'jpg':
                    $imageCreateFunc = 'imagecreatefromjpeg';
                    break;

                case 'png':
                    $imageCreateFunc = 'imagecreatefrompng';
                    break;

                case 'bmp':
                    $imageCreateFunc = 'imagecreatefrombmp';
                    break;

                case 'gif':
                    $imageCreateFunc = 'imagecreatefromgif';
                    break;

                case 'vnd.wap.wbmp':
                    $imageCreateFunc = 'imagecreatefromwbmp';
                    break;

                case 'xbm':
                    $imageCreateFunc = 'imagecreatefromxbm';
                    break;

                default:
                    $imageCreateFunc = 'imagecreatefromjpeg';
            }

            return $imageCreateFunc;
        }

        /**
         * Get image save function based on type
         *
         * @param   string  $type  Image type
         *
         * @return  string
         */
        static function getImageSaveFunction($type)
        {
            switch ($type) {
                case 'jpeg':
                case 'jpg':
                    $imageSaveFunc = 'imagejpeg';
                    break;

                case 'png':
                    $imageSaveFunc = 'imagepng';
                    break;

                case 'bmp':
                    $imageSaveFunc = 'imagebmp';
                    break;

                case 'gif':
                    $imageSaveFunc = 'imagegif';
                    break;

                case 'vnd.wap.wbmp':
                    $imageSaveFunc = 'imagewbmp';
                    break;

                case 'xbm':
                    $imageSaveFunc = 'imagexbm';
                    break;

                default:
                    $imageSaveFunc = 'imagejpeg';
            }

            return $imageSaveFunc;
        }

        /**
         * Resize image
         *
         * @param   string   $imgSrc   Source image path
         * @param   string   $imgDest  Destination image path
         * @param   integer  $dWidth   Destination width
         * @param   integer  $dHeight  Destination height
         * @param   boolean  $crop     Crop image
         * @param   integer  $quality  Image quality
         *
         * @return  void
         */
        static function resize($imgSrc, $imgDest, $dWidth, $dHeight, $crop = true, $quality = 100)
        {
            $info = @getimagesize($imgSrc, $imageinfo);
            
            if (!$info) {
                return;
            }

            $sWidth = $info[0];
            $sHeight = $info[1];
            
            if (!$sWidth) {
                return;
            }

            if ($sHeight / $sWidth > $dHeight / $dWidth) {
                $width = $sWidth;
                $height = round(($dHeight * $sWidth) / $dWidth);
                $sx = 0;
                $sy = round(($sHeight - $height) / 3);
            } else {
                $height = $sHeight;
                $width = round(($sHeight * $dWidth) / $dHeight);
                $sx = round(($sWidth - $width) / 2);
                $sy = 0;
            }

            if (!$crop) {
                $sx = 0;
                $sy = 0;
                $width = $sWidth;
                $height = $sHeight;
            }

            $ext = str_replace('image/', '', $info['mime']);
            $imageCreateFunc = self::getImageCreateFunction($ext);
            
            // Get file extension from destination
            $destExt = pathinfo($imgDest, PATHINFO_EXTENSION);
            $imageSaveFunc = self::getImageSaveFunction($destExt);

            if (!function_exists($imageCreateFunc)) {
                return;
            }

            $sImage = @$imageCreateFunc($imgSrc);
            
            if (!$sImage) {
                return;
            }

            $dImage = imagecreatetruecolor($dWidth, $dHeight);

            // Make transparent
            if ($ext == 'png') {
                imagealphablending($dImage, false);
                imagesavealpha($dImage, true);
                $transparent = imagecolorallocatealpha($dImage, 255, 255, 255, 127);
                imagefilledrectangle($dImage, 0, 0, $dWidth, $dHeight, $transparent);
            }

            imagecopyresampled($dImage, $sImage, 0, 0, $sx, $sy, $dWidth, $dHeight, $width, $height);

            // Save image directly to file (FTP support removed for Joomla 4+)
            if ($ext == 'png') {
                $imageSaveFunc($dImage, $imgDest, 9);
            } elseif ($ext == 'gif') {
                $imageSaveFunc($dImage, $imgDest);
            } else {
                $imageSaveFunc($dImage, $imgDest, $quality);
            }

            // Clean up
            imagedestroy($dImage);
            imagedestroy($sImage);
        }

        /**
         * Create image thumbnail
         *
         * @param   string   $imgSrc   Source image path
         * @param   string   $imgDest  Destination image path
         * @param   integer  $width    Width
         * @param   integer  $height   Height
         * @param   boolean  $crop     Crop image
         * @param   integer  $quality  Image quality
         *
         * @return  void
         */
        static function createImage($imgSrc, $imgDest, $width, $height, $crop = true, $quality = 100)
        {
            if (file_exists($imgDest)) {
                $info = @getimagesize($imgDest, $imageinfo);
                // Image is already created
                if ($info && ($info[0] == $width) && ($info[1] == $height)) {
                    return;
                }
            }
            
            self::resize($imgSrc, $imgDest, $width, $height, $crop, $quality);
        }
    }
}