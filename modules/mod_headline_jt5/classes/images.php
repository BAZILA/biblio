<?php
/**
 * JT3 Image helper (Joomla 6 compatible)
 * ENHANCED: Better Windows compatibility and error handling
 */

defined('_JEXEC') or die;

use Joomla\CMS\Filesystem\File;
use Joomla\CMS\Filesystem\Folder;

jimport('joomla.filesystem.file');

if (!class_exists('JT3ImageHelper'))
{
    class JT3ImageHelper
    {
        public static function getImageCreateFunction($type)
        {
            $type = strtolower($type);
            switch ($type) {
                case 'jpeg':
                case 'jpg':
                    return 'imagecreatefromjpeg';
                case 'png':
                    return 'imagecreatefrompng';
                case 'bmp':
                    return 'imagecreatefrombmp';
                case 'gif':
                    return 'imagecreatefromgif';
                case 'vnd.wap.wbmp':
                    return 'imagecreatefromwbmp';
                case 'xbm':
                    return 'imagecreatefromxbm';
                case 'webp':
                    return function_exists('imagecreatefromwebp') ? 'imagecreatefromwebp' : 'imagecreatefromjpeg';
                default:
                    return 'imagecreatefromjpeg';
            }
        }

        public static function getImageSaveFunction($ext)
        {
            $type = strtolower($ext);
            switch ($type) {
                case 'jpeg':
                case 'jpg':
                    return 'imagejpeg';
                case 'png':
                    return 'imagepng';
                case 'bmp':
                    return 'imagebmp';
                case 'gif':
                    return 'imagegif';
                case 'vnd.wap.wbmp':
                    return 'imagewbmp';
                case 'xbm':
                    return 'imagexbm';
                case 'webp':
                    return function_exists('imagewebp') ? 'imagewebp' : 'imagejpeg';
                default:
                    return 'imagejpeg';
            }
        }

        /**
         * Resize and save an image
         * ENHANCED: Better error handling and Windows compatibility
         *
         * @param string $imgSrc filesystem path
         * @param string $imgDest filesystem path
         * @param int $dWidth
         * @param int $dHeight
         * @param bool $crop
         * @param int $quality
         * @return bool Success status
         */
        public static function resize($imgSrc, $imgDest, $dWidth, $dHeight, $crop = true, $quality = 90)
        {
            // Validate source file exists
            if (!file_exists($imgSrc)) {
                error_log("JT3 Image Error: Source file not found: {$imgSrc}");
                return false;
            }

            // Get image info
            $info = @getimagesize($imgSrc);
            if (!$info) {
                error_log("JT3 Image Error: Cannot read image info: {$imgSrc}");
                return false;
            }

            $sWidth = $info[0];
            $sHeight = $info[1];
            
            if (!$sWidth || !$sHeight) {
                error_log("JT3 Image Error: Invalid image dimensions: {$sWidth}x{$sHeight}");
                return false;
            }

            // Calculate crop dimensions
            if ($crop) {
                if ($sHeight / $sWidth > $dHeight / $dWidth) {
                    $width = $sWidth;
                    $height = (int) round(($dHeight * $sWidth) / $dWidth);
                    $sx = 0;
                    $sy = (int) round(($sHeight - $height) / 3);
                } else {
                    $height = $sHeight;
                    $width = (int) round(($sHeight * $dWidth) / $dHeight);
                    $sx = (int) round(($sWidth - $width) / 2);
                    $sy = 0;
                }
            } else {
                $sx = 0; 
                $sy = 0;
                $width = $sWidth; 
                $height = $sHeight;
            }

            $mime = $info['mime'];
            $ext = str_replace('image/', '', $mime);

            $imageCreateFunc = self::getImageCreateFunction($ext);

            // Create source image resource
            if (is_callable($imageCreateFunc)) {
                $sImage = @call_user_func($imageCreateFunc, $imgSrc);
            } else {
                $sImage = @$imageCreateFunc($imgSrc);
            }
            
            if (!$sImage) {
                error_log("JT3 Image Error: Cannot create image resource from: {$imgSrc} (type: {$ext})");
                return false;
            }

            // Create destination image
            $dImage = imagecreatetruecolor($dWidth, $dHeight);
            if (!$dImage) {
                imagedestroy($sImage);
                error_log("JT3 Image Error: Cannot create destination image");
                return false;
            }

            // Handle transparency for PNG/GIF
            if ($ext === 'png' || $ext === 'gif') {
                imagealphablending($dImage, false);
                imagesavealpha($dImage, true);
                $transparent = imagecolorallocatealpha($dImage, 255, 255, 255, 127);
                imagefilledrectangle($dImage, 0, 0, $dWidth, $dHeight, $transparent);
            } else {
                // Fill white background for jpeg
                $white = imagecolorallocate($dImage, 255, 255, 255);
                imagefilledrectangle($dImage, 0, 0, $dWidth, $dHeight, $white);
            }

            // Resample the image
            $resampleSuccess = imagecopyresampled(
                $dImage, $sImage, 
                0, 0, $sx, $sy, 
                $dWidth, $dHeight, $width, $height
            );

            if (!$resampleSuccess) {
                imagedestroy($sImage);
                imagedestroy($dImage);
                error_log("JT3 Image Error: imagecopyresampled failed");
                return false;
            }

            // Ensure destination directory exists
            $destDir = dirname($imgDest);
            if (!is_dir($destDir)) {
                if (!Folder::create($destDir)) {
                    imagedestroy($sImage);
                    imagedestroy($dImage);
                    error_log("JT3 Image Error: Cannot create destination directory: {$destDir}");
                    return false;
                }
            }

            // Save the image
            $destExt = pathinfo($imgDest, PATHINFO_EXTENSION);
            $imageSaveFunc = self::getImageSaveFunction($destExt);

            $saveSuccess = false;
            try {
                if ($destExt === 'png') {
                    // PNG: compression level 0-9 (9 = best compression)
                    $saveSuccess = $imageSaveFunc($dImage, $imgDest, 9);
                } elseif ($destExt === 'gif') {
                    $saveSuccess = $imageSaveFunc($dImage, $imgDest);
                } elseif (in_array($destExt, ['jpg', 'jpeg'])) {
                    $saveSuccess = $imageSaveFunc($dImage, $imgDest, (int) $quality);
                } elseif ($destExt === 'webp' && function_exists('imagewebp')) {
                    $saveSuccess = $imageSaveFunc($dImage, $imgDest, (int) $quality);
                } else {
                    // Default: save as jpeg
                    $saveSuccess = imagejpeg($dImage, $imgDest, (int) $quality);
                }
            } catch (Exception $e) {
                error_log("JT3 Image Error: Exception saving image: " . $e->getMessage());
            }

            // Clean up memory
            imagedestroy($sImage);
            imagedestroy($dImage);

            if (!$saveSuccess) {
                error_log("JT3 Image Error: Failed to save image to: {$imgDest}");
                return false;
            }

            // Verify file was created
            if (!file_exists($imgDest)) {
                error_log("JT3 Image Error: Image saved but file doesn't exist: {$imgDest}");
                return false;
            }

            error_log("JT3 Image Success: Created {$dWidth}x{$dHeight} thumbnail: {$imgDest}");
            return true;
        }

        /**
         * Create image if not exists or not matching size
         * ENHANCED: Better validation and error handling
         */
        public static function createImage($imgSrc, $imgDest, $width, $height, $crop = true, $quality = 90)
        {
            // Check if thumbnail already exists with correct dimensions
            if (file_exists($imgDest)) {
                $info = @getimagesize($imgDest);
                if ($info && isset($info[0]) && isset($info[1])) {
                    if ($info[0] == $width && $info[1] == $height) {
                        error_log("JT3 Image: Using existing thumbnail: {$imgDest}");
                        return true;
                    } else {
                        error_log("JT3 Image: Existing thumbnail has wrong size ({$info[0]}x{$info[1]}), recreating...");
                        // Delete old thumbnail with wrong dimensions
                        @unlink($imgDest);
                    }
                }
            }

            // Create new thumbnail
            return self::resize($imgSrc, $imgDest, $width, $height, $crop, $quality);
        }
    }
}