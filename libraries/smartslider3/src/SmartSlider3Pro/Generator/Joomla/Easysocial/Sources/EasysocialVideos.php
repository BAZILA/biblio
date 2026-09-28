<?php

namespace Nextend\SmartSlider3Pro\Generator\Joomla\Easysocial\Sources;

use Nextend\Framework\Database\Database;
use Nextend\Framework\Form\Container\ContainerTable;
use Nextend\Framework\Form\Element\MixedField\GeneratorOrder;
use Nextend\Framework\Form\Element\Select\Filter;
use Nextend\Framework\Form\Element\Text;
use Nextend\Framework\Parser\Common;
use Nextend\SmartSlider3\Generator\AbstractGenerator;
use Nextend\SmartSlider3Pro\Generator\Joomla\Easysocial\Elements\EasysocialCategories;

class EasysocialVideos extends AbstractGenerator {

    protected $layout = 'article';

    public function getDescription() {
        return sprintf(n2_('Creates slides from %1$s content.'), 'EasySocial ' . n2_('Videos'));
    }

    public function renderFields($container) {
        parent::renderFields($container);

        $filterGroup = new ContainerTable($container, 'filter', n2_('Filter'));

        $source = $filterGroup->createRow('source-row');
        new EasysocialCategories($source, 'easysocialcategories', n2_('Categories'), 0, array(
            'isMultiple' => true,
            'size'       => 10,
            'table'      => 'social_videos_categories'
        ));

        $limit = $filterGroup->createRow('limit-row');
        new Filter($limit, 'featured', n2_('Featured'), 0);

        new Text($limit, 'allowed-users', n2_('Allowed user IDs'), '', array(
            'tipLabel'       => n2_('Allowed user IDs'),
            'tipDescription' => n2_('Pull posts only from these users. Separate them by comma.')
        ));

        new Text($limit, 'banned-users', n2_('Banned user IDs'), '', array(
            'tipLabel'       => n2_('Banned user IDs'),
            'tipDescription' => n2_('Do not pull posts from these users. Separate them by comma.')
        ));

        $orderGroup = new ContainerTable($container, 'order-group', n2_('Order'));
        $order      = $orderGroup->createRow('order-row');
        new GeneratorOrder($order, 'easysocialorder', 'created|*|desc', array(
            'options' => array(
                ''        => n2_('None'),
                'title'   => n2_('Title'),
                'created' => n2_('Creation time'),
                'id'      => 'ID'
            )
        ));
    }

    protected function _getData($count, $startIndex) {

        $where = array(
            "state = '1'"
        );

        $category = array_map('intval', explode('||', $this->data->get('easysocialcategories', '')));

        if (!in_array('0', $category)) {
            $where[] = 'category_id IN (' . implode(',', $category) . ')';
        }

        switch ($this->data->get('featured', 0)) {
            case 1:
                $where[] = 'featured = 1';
                break;
            case -1:
                $where[] = 'featured = 0';
                break;
        }

        $allowedUsers = $this->data->get('allowed-users', '');
        if (!empty($allowedUsers)) {
            $where[] = "user_id IN (" . $allowedUsers . ")";
        }

        $bannedUsers = $this->data->get('banned-users', '');
        if (!empty($bannedUsers)) {
            $where[] = "user_id NOT IN (" . $bannedUsers . ")";
        }

        $query = "SELECT * FROM #__social_videos WHERE " . implode(' AND ', $where) . "  ";

        $order = Common::parse($this->data->get('easysocialorder', 'created|*|desc'));
        if ($order[0]) {
            $query .= 'ORDER BY ' . $order[0] . ' ' . $order[1] . ' ';
        }

        $query .= 'LIMIT ' . $startIndex . ', ' . $count;

        $result = Database::queryAll($query);

        $data = array();
        for ($i = 0; $i < count($result); $i++) {
            $r = array(
                'video'       => $result[$i]['path'],
                'title'       => $result[$i]['title'],
                'description' => $result[$i]['description'],
                'hits'        => $result[$i]['hits'],
                'thumbnail'   => !empty($result[$i]['thumbnail']) ? '$/' . $result[$i]['thumbnail'] : '',
                'id'          => $result[$i]['id']
            );

            $data[] = $r;
        }

        return $data;
    }
}
