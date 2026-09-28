<?php

namespace Nextend\SmartSlider3Pro\Generator\Joomla\Ignitegallery\Sources;

use Igniteg\Component\Igallery\Administrator\Helper\FileHelper;
use Joomla\CMS\Filesystem\File;
use Nextend\Framework\Database\Database;
use Nextend\Framework\Form\Container\ContainerTable;
use Nextend\Framework\Form\Element\MixedField\GeneratorOrder;
use Nextend\Framework\Notification\Notification;
use Nextend\Framework\Parser\Common;
use Nextend\Framework\ResourceTranslator\ResourceTranslator;
use Nextend\Framework\Url\Url;
use Nextend\SmartSlider3\Generator\AbstractGenerator;
use Nextend\SmartSlider3Pro\Generator\Joomla\Ignitegallery\Elements\IgnitegalleryCategories;

class IgnitegalleryImages extends AbstractGenerator {

    protected $layout = 'image_extended';

    public function getDescription() {
        return sprintf(n2_('Creates slides from %1$s content.'), 'Ignite Gallery');
    }

    public function renderFields($container) {
        parent::renderFields($container);

        $filterGroup = new ContainerTable($container, 'filter', n2_('Filter'));

        $source = $filterGroup->createRow('source-row');

        new IgnitegalleryCategories($source, 'ignitegallerysourcecategory', n2_('Category'), 0, array(
            'isMultiple' => true
        ));

        $orderGroup = new ContainerTable($container, 'order-group', n2_('Order'));
        $order      = $orderGroup->createRow('order-row');
        new GeneratorOrder($order, 'ignitegalleryorder', 'con.date|*|desc', array(
            'options' => array(
                ''             => n2_('None'),
                'con.filename' => n2_('Filename'),
                'cat_title'    => n2_('Category'),
                'con.ordering' => n2_('Ordering'),
                'con.hits'     => n2_('Hits'),
                'con.date'     => n2_('Creation time')
            )
        ));
    }

    protected function _getData($count, $startIndex) {
        require_once(JPATH_ADMINISTRATOR . '/components/com_igallery/defines.php');
        if (version_compare(IG_VERSION, '4.8', '<')) {
            Notification::error(n2_('Update your Ignite Gallery! Only Ignite Gallery 4.8+ versions are supported.'));

            return null;
        } else {

            $categories = array_map('intval', explode('||', $this->data->get('ignitegallerysourcecategory', '')));

            $query = 'SELECT ';
            $query .= 'con.id, ';
            $query .= 'con.filename, ';
            $query .= 'con.description, ';
            $query .= 'con.alt_text, ';
            $query .= 'con.link, ';
            $query .= 'con.hits, ';
            $query .= 'con.rotation, ';
            $query .= 'con.filesys, ';
            $query .= 'con.src, ';

            $query .= 'con.gallery_id, ';
            $query .= 'cat.name AS cat_title, ';
            $query .= 'cat.alias AS cat_alias, ';
            $query .= 'cat.id AS cat_id, ';
            $query .= 'cat.folder AS cat_folder, ';

            $query .= 'pro.thumb_width, ';
            $query .= 'pro.thumb_height, ';
            $query .= 'pro.crop_thumbs, ';
            $query .= 'pro.img_quality, ';
            $query .= 'pro.round_fill, ';
            $query .= 'pro.round_thumb ';

            $query .= 'FROM #__igallery_img AS con ';

            $query .= 'LEFT JOIN #__igallery AS cat ON cat.id = con.gallery_id ';

            $query .= 'LEFT JOIN #__igallery_profiles AS pro ON pro.id = cat.profile ';

            $where = array('con.published = 1 ');
            if (count($categories) > 0 && !in_array('0', $categories)) {
                $where[] = 'con.gallery_id IN (' . implode(',', $categories) . ') ';
            }

            if (count($where)) {
                $query .= ' WHERE ' . implode(' AND ', $where);
            }

            $order = Common::parse($this->data->get('ignitegalleryorder', 'con.date|*|desc'));
            if ($order[0]) {
                $query .= 'ORDER BY ' . $order[0] . ' ' . $order[1] . ' ';
            }

            $query .= 'LIMIT ' . $startIndex . ', ' . $count . ' ';

            $result = Database::queryAll($query);

            $data = array();

            for ($i = 0; $i < count($result); $i++) {
                $isNewFilesystem = $result[$i]['filesys'];

                if ($isNewFilesystem) {
                    $folderName = $result[$i]['cat_folder'];
                } else {
                    $increment  = FileHelper::getIncrementFromFilename($result[$i]['filename']);
                    $folderName = FileHelper::getFolderName($increment);
                }

                $sourceFile = IG_ORIG_PATH . '/' . $folderName . '/' . $result[$i]['filename'];

                if (!empty($result[$i]['src'])) {
                    $sourceFile = JPATH_SITE . '/' . $result[$i]['src'];
                }
                $result[$i]['original_image'] = ResourceTranslator::urlToResource(Url::pathToUri($sourceFile));

                $size = getimagesize($sourceFile);

                if ($size !== false) {
                    $imageArray = FileHelper::originalToResized($result[$i]['filename'], $folderName, $result[$i]['src'], $size[0], $size[1], 100, 0, $result[$i]['rotation'], 0, 0, 0);

                    $result[$i]['image'] = ResourceTranslator::urlToResource(IG_IMAGE_HTML_RESIZE . $imageArray['folderName'] . '/' . $imageArray['fullFileName']);

                    $thumbnailArray = FileHelper::originalToResized($result[$i]['filename'], $folderName, $result[$i]['src'], $result[$i]['thumb_width'], $result[$i]['thumb_height'], $result[$i]['img_quality'], $result[$i]['crop_thumbs'], $result[$i]['rotation'], $result[$i]['round_thumb'], $result[$i]['round_fill']);

                    $result[$i]['thumbnail'] = ResourceTranslator::urlToResource(IG_IMAGE_HTML_RESIZE . $thumbnailArray['folderName'] . '/' . $thumbnailArray['fullFileName']);
                } else {
                    $result[$i]['image'] = $result[$i]['thumbnail'] = $result[$i]['original_image'];
                }

                $filename = File::stripExt($result[$i]['filename']);
                if (!$isNewFilesystem) {
                    $searchRef = strrpos($filename, '-');
                    if ($searchRef !== false) {
                        $filename = substr($filename, 0, $searchRef);
                    }
                }
                $result[$i]['url']          = $result[$i]['image_url'] = 'index.php?option=com_igallery&view=category&igid=' . $result[$i]['gallery_id'] . '&i=' . $filename;
                $result[$i]['category_url'] = 'index.php?option=com_igallery&view=category&igid=' . $result[$i]['gallery_id'];
                if (!empty($result[$i]['link'])) {
                    $result[$i]['url'] = $result[$i]['link'];
                }
                $result[$i]['url_label'] = n2_('View');
                if (!empty($result[$i]['alt_text'])) {
                    $result[$i]['title'] = $result[$i]['alt_text'];
                } else {
                    $result[$i]['title'] = $result[$i]['filename'];
                }

                $r = array(
                    'image'          => $result[$i]['image'],
                    'thumbnail'      => $result[$i]['thumbnail'],
                    'original_image' => $result[$i]['original_image'],
                    'title'          => $result[$i]['title'],
                    'description'    => $result[$i]['description'],
                    'url'            => $result[$i]['url'],
                    'url_label'      => $result[$i]['url_label'],
                    'filename'       => $result[$i]['filename'],
                    'image_url'      => $result[$i]['image_url'],
                    'hits'           => $result[$i]['hits'],
                    'category_title' => $result[$i]['cat_title'],
                    'category_url'   => $result[$i]['category_url'],
                    'id'             => $result[$i]['id']
                );

                $data[] = $r;
            }

            return $data;
        }
    }

}
