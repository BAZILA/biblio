<?php

namespace Nextend\SmartSlider3Pro\Generator\Joomla\Phocagallery\Sources;

use Nextend\Framework\Database\Database;
use Nextend\Framework\Form\Container\ContainerTable;
use Nextend\Framework\Form\Element\MixedField\GeneratorOrder;
use Nextend\Framework\Form\Element\Text;
use Nextend\Framework\Parser\Common;
use Nextend\Framework\ResourceTranslator\ResourceTranslator;
use Nextend\Framework\Url\Url;
use Nextend\SmartSlider3\Generator\AbstractGenerator;
use Nextend\SmartSlider3Pro\Generator\Joomla\Phocagallery\Elements\PhocagalleryCategories;
use Nextend\SmartSlider3Pro\Generator\Joomla\Phocagallery\Elements\PhocagalleryTags;

class PhocagalleryImages extends AbstractGenerator {

    protected $layout = 'image_extended';

    public function getDescription() {
        return sprintf(n2_('Creates slides from %1$s content.'), 'Phoca Gallery');
    }

    public function renderFields($container) {
        parent::renderFields($container);

        $filterGroup = new ContainerTable($container, 'filter', n2_('Filter'));

        $source = $filterGroup->createRow('source-row');
        new PhocagalleryCategories($source, 'phocagallerysourcecategories', n2_('Category'), 0, array(
            'isMultiple' => true
        ));
        new PhocagalleryTags($source, 'phocagallerysourcetags', n2_('Tag'), 0, array(
            'isMultiple' => true
        ));


        $limit = $filterGroup->createRow('limit-row');
        new Text($limit, 'phocagallerysourcelanguage', n2_('Language'), '*');

        $orderGroup = new ContainerTable($container, 'order-group', n2_('Order'));
        $order      = $orderGroup->createRow('order-row');
        new GeneratorOrder($order, 'phocagalleryorder', 'con.date|*|desc', array(
            'options' => array(
                ''             => n2_('None'),
                'con.title'    => n2_('Title'),
                'cat_title'    => n2_('Category title'),
                'con.ordering' => n2_('Ordering'),
                'con.hits'     => n2_('Hits'),
                'con.date'     => n2_('Date')
            )
        ));
    }

    protected function _getData($count, $startIndex) {

        $categories = array_map('intval', explode('||', $this->data->get('phocagallerysourcecategories', '')));
        $tags       = array_map('intval', explode('||', $this->data->get('phocagallerysourcetags', '')));

        $query = 'SELECT ';
        $query .= 'con.id, ';
        $query .= 'con.title, ';
        $query .= 'con.alias, ';
        $query .= 'con.filename, ';
        $query .= 'con.description, ';
        $query .= 'con.hits, ';

        $query .= 'con.catid, ';
        $query .= 'cat.title AS cat_title, ';
        $query .= 'cat.description AS cat_description, ';
        $query .= 'cat.alias AS cat_alias ';

        $query .= 'FROM #__phocagallery AS con ';

        $query .= 'LEFT JOIN #__phocagallery_categories AS cat ON cat.id = con.catid ';

        $where = array(
            'con.published = 1 ',
            'con.approved = 1 '
        );
        if (count($categories) > 0 && !in_array('0', $categories)) {
            $where[] = 'con.catid IN (' . implode(',', $categories) . ') ';
        }

        if (count($tags) > 0 && !in_array('0', $tags)) {
            $where[] = 'con.id IN (SELECT imgid FROM #__phocagallery_tags_ref WHERE tagid IN (' . implode(',', $tags) . ')) ';
        }

        $language = $this->data->get('phocagallerysourcelanguage', '*');
        if ($language) {
            $where[] = 'con.language = ' . Database::quote($language) . ' ';
        }

        if (count($where)) {
            $query .= ' WHERE ' . implode(' AND ', $where);
        }

        $order = Common::parse($this->data->get('phocagalleryorder', 'con.date|*|desc'));
        if ($order[0]) {
            $query .= 'ORDER BY ' . $order[0] . ' ' . $order[1] . ' ';
        }

        $query .= 'LIMIT ' . $startIndex . ', ' . $count;

        $result = Database::queryAll($query);

        $data = array();
        $uri  = Url::getBaseUri();
        for ($i = 0; $i < count($result); $i++) {
            $image  = ResourceTranslator::urlToResource($uri . "/images/phocagallery/" . $result[$i]['filename']);
            $r      = array(
                'image'                => $image,
                'thumbnail'            => $image,
                'title'                => $result[$i]['title'],
                'description'          => $result[$i]['description'],
                'url'                  => 'index.php?option=com_phocagallery&view=detail&catid=' . $result[$i]['catid'] . ':' . $result[$i]['cat_alias'] . '&id=' . $result[$i]['id'] . ':' . $result[$i]['alias'],
                'url_label'            => n2_('View image'),
                'filename'             => $result[$i]['filename'],
                'category_title'       => $result[$i]['cat_title'],
                'category_description' => $result[$i]['cat_description'],
                'category_url'         => 'index.php?option=com_phocagallery&view=category&id=' . $result[$i]['catid'] . ':' . $result[$i]['cat_alias'],
                'hits'                 => $result[$i]['hits'],
                'id'                   => $result[$i]['id']
            );
            $data[] = $r;
        }

        return $data;
    }
}