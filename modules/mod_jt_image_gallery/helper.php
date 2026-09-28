<?php
/**
* @title        JT Image Gallery module
* @website      http://www.joomlatema.net
* @copyright    Copyright (C) 2008 -2025 JoomlaTema.net. All rights reserved.
* @license      http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
**/
defined('_JEXEC') or die;
use Joomla\CMS\Uri\Uri;

class ModJTImageGalleryHelper
{
    public static function getImages($params)
    {
        $image_folders = $params->get('image_folders', array('/images/'));
        $thumb_width = $params->get('thumb_width', 150);
        $thumb_height = $params->get('thumb_height', 150);
        $limit = $params->get('limititems', 0);
        $order_by = $params->get('order_by', 'title');
        $order_dir = $params->get('order_dir', 'ASC');

        $image_files = array();

        if (!is_array($image_folders)) {
            $image_folders = array($image_folders);
        }

        foreach ($image_folders as $image_folder) {
            $folder = JPATH_SITE . '/' . trim($image_folder, '/');

            if (is_dir($folder)) {
                $dir = opendir($folder);
                $files = array();
                while (($file = readdir($dir)) !== false) {
                    if ($file[0] == '.' || $file == 'index.html') {
                        continue;
                    }

                    $image_path = $folder . '/' . $file;

                    if (is_file($image_path) && self::isSupportedImage($image_path)) {
                        $files[] = array(
                            'url' => Uri::root() . ltrim(str_replace(JPATH_SITE, '', $image_path), '/'),
                            'thumb' => self::createThumbnail($params, $image_path, $thumb_width, $thumb_height),
                            'title' => pathinfo($file, PATHINFO_FILENAME),
                            'date_modified' => filemtime($image_path),
                            'date_created' => filectime($image_path)
                        );
                    }
                }
                closedir($dir);

                // sorting
                if ($order_by == 'title') {
                    usort($files, function ($a, $b) {
                        return strcasecmp($a['title'], $b['title']);
                    });
                } elseif ($order_by == 'date_modified') {
                    usort($files, function ($a, $b) use ($order_dir) {
                        return ($order_dir == 'ASC')
                            ? $a['date_modified'] - $b['date_modified']
                            : $b['date_modified'] - $a['date_modified'];
                    });
                } elseif ($order_by == 'date_created') {
                    usort($files, function ($a, $b) use ($order_dir) {
                        return ($order_dir == 'ASC')
                            ? $a['date_created'] - $b['date_created']
                            : $b['date_created'] - $a['date_created'];
                    });
                } elseif ($order_by == 'random') {
                    shuffle($files);
                }

                if ($limit > 0) {
                    $files = array_slice($files, 0, $limit);
                }

                foreach ($files as $file) {
                    $image_files[] = array(
                        'url' => $file['url'],
                        'thumb' => $file['thumb'],
                        'title' => $file['title']
                    );
                }
            }
        }

        return $image_files;
    }

    private static function isSupportedImage($image_path)
    {
        $supported_extensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        $file_extension = strtolower(pathinfo($image_path, PATHINFO_EXTENSION));

        if (!in_array($file_extension, $supported_extensions)) {
            return false;
        }

        $image_info = @getimagesize($image_path);
        if ($image_info === false) {
            return false;
        }

        $image_type = $image_info[2];
        return in_array($image_type, [IMAGETYPE_JPEG, IMAGETYPE_PNG, IMAGETYPE_GIF, IMAGETYPE_WEBP]);
    }

    private static function createThumbnail($params, $image_path, $thumb_width, $thumb_height)
{
    $thumb_type    = $params->get('thumb_type', '.jpg');
    $thumb_quality = (int) $params->get('thumb_quality', 85);

    $image_info = getimagesize($image_path);

    if (!$image_info) {
        return '';
    }

    $source_width  = $image_info[0];
    $source_height = $image_info[1];
    $image_type    = $image_info[2];

    // Allow "auto"
    $widthAuto  = strtolower((string) $thumb_width) === 'auto';
    $heightAuto = strtolower((string) $thumb_height) === 'auto';

    if ($widthAuto && $heightAuto) {
        $thumb_width  = $source_width;
        $thumb_height = $source_height;
    } elseif ($widthAuto) {
        $thumb_height = (int) $thumb_height;
        $thumb_width  = (int) round(($source_width / $source_height) * $thumb_height);
    } elseif ($heightAuto) {
        $thumb_width  = (int) $thumb_width;
        $thumb_height = (int) round(($source_height / $source_width) * $thumb_width);
    } else {
        $thumb_width  = (int) $thumb_width;
        $thumb_height = (int) $thumb_height;
    }

    $thumb_folder = JPATH_SITE . '/cache/mod_jt_image_gallery/thumbs/';

    if (!is_dir($thumb_folder)) {
        mkdir($thumb_folder, 0755, true);
    }

    $thumb_hash = md5($image_path . '_' . $thumb_width . 'x' . $thumb_height . $thumb_type);
    $thumb_file = $thumb_folder . $thumb_hash . $thumb_type;

    if (!is_file($thumb_file)) {

        switch ($image_type) {
            case IMAGETYPE_JPEG:
                $source_image = imagecreatefromjpeg($image_path);
                break;

            case IMAGETYPE_PNG:
                $source_image = imagecreatefrompng($image_path);
                break;

            case IMAGETYPE_GIF:
                $source_image = imagecreatefromgif($image_path);
                break;

            case IMAGETYPE_WEBP:
                $source_image = imagecreatefromwebp($image_path);
                break;

            default:
                return '';
        }

        $thumb_image = imagecreatetruecolor($thumb_width, $thumb_height);

        // Preserve transparency
        if (in_array($image_type, [IMAGETYPE_PNG, IMAGETYPE_GIF, IMAGETYPE_WEBP])) {
            imagealphablending($thumb_image, false);
            imagesavealpha($thumb_image, true);

            $transparent = imagecolorallocatealpha($thumb_image, 0, 0, 0, 127);
            imagefilledrectangle(
                $thumb_image,
                0,
                0,
                $thumb_width,
                $thumb_height,
                $transparent
            );
        }

        imagecopyresampled(
            $thumb_image,
            $source_image,
            0,
            0,
            0,
            0,
            $thumb_width,
            $thumb_height,
            $source_width,
            $source_height
        );

        switch ($thumb_type) {
            case '.png':
                imagepng($thumb_image, $thumb_file);
                break;

            case '.gif':
                imagegif($thumb_image, $thumb_file);
                break;

            case '.webp':
                imagewebp($thumb_image, $thumb_file, $thumb_quality);
                break;

            default:
                imagejpeg($thumb_image, $thumb_file, $thumb_quality);
                break;
        }

        imagedestroy($source_image);
        imagedestroy($thumb_image);
    }

    return Uri::root() . ltrim(str_replace(JPATH_SITE, '', $thumb_file), '/');
}
}
?>
