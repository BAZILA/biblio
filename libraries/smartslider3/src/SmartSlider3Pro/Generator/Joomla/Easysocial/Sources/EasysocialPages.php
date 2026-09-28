<?php

namespace Nextend\SmartSlider3Pro\Generator\Joomla\Easysocial\Sources;

use ES;
use FRoute;
use Nextend\Framework\Database\Database;
use Nextend\Framework\Form\Container\ContainerTable;
use Nextend\Framework\Form\Element\MixedField\GeneratorOrder;
use Nextend\Framework\Form\Element\Select;
use Nextend\Framework\Form\Element\Select\Filter;
use Nextend\Framework\Form\Element\Text;
use Nextend\Framework\Parser\Common;
use Nextend\SmartSlider3\Generator\AbstractGenerator;
use Nextend\SmartSlider3\Platform\Joomla\ImageFallback;
use Nextend\SmartSlider3Pro\Generator\Joomla\Easysocial\Elements\EasysocialCategories;

class EasysocialPages extends AbstractGenerator {

    protected $layout = 'article';

    public function getDescription() {
        return sprintf(n2_('Creates slides from %1$s content.'), 'EasySocial ' . n2_('Pages'));
    }

    public function renderFields($container) {
        parent::renderFields($container);

        $filterGroup = new ContainerTable($container, 'filter', n2_('Filter'));

        $source = $filterGroup->createRow('source-row');
        new EasysocialCategories($source, 'easysocialcategories', n2_('Categories'), 0, array(
            'isMultiple' => true,
            'size'       => 10,
            'table'      => 'social_clusters_categories',
            'typeDb'     => 'page'
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

        new Select($limit, 'accesstype', n2_('Type'), 0, array(
            'options' => array(
                '0' => n2_('All'),
                '1' => n2_('Public'),
                '2' => n2_('Private'),
                '3' => n2_('Invite only')
            )
        ));

        new Select($limit, 'notification', n2_('Notification'), 0, array(
            'options' => array(
                '0' => n2_('All'),
                '1' => n2_('Both'),
                '2' => n2_('Email only'),
                '3' => n2_('Internal only'),
                '4' => n2_('None')
            )
        ));

        $orderGroup = new ContainerTable($container, 'order-group', n2_('Order'));
        $order      = $orderGroup->createRow('order-row');
        new GeneratorOrder($order, 'easysocialorder', 'a.created|*|desc', array(
            'options' => array(
                ''          => n2_('None'),
                'a.title'   => n2_('Title'),
                'a.created' => n2_('Creation time'),
                'a.hits'    => n2_('Hits'),
                'a.id'      => 'ID'
            )
        ));

    }

    protected function _getData($count, $startIndex) {

        $where = array(
            "a.cluster_type = 'page'",
            "a.state = '1'"
        );

        $category = array_map('intval', explode('||', $this->data->get('easysocialcategories', '')));

        if (!in_array('0', $category)) {
            $where[] = 'a.category_id IN (' . implode(',', $category) . ')';
        }

        switch ($this->data->get('featured', 0)) {
            case 1:
                $where[] = 'a.featured = 1';
                break;
            case -1:
                $where[] = 'a.featured = 0';
                break;
        }

        $typeDb = $this->data->get('accesstype', 0);
        if ($typeDb != 0) {
            $where[] = 'a.type = ' . $typeDb;
        }

        $typeDb = $this->data->get('notification', 0);
        if ($typeDb != 0) {
            $where[] = 'a.notification = ' . $typeDb;
        }

        $allowedUsers = $this->data->get('allowed-users', '');
        if (!empty($allowedUsers)) {
            $where[] = "a.creator_uid IN (" . $allowedUsers . ")";
        }

        $bannedUsers = $this->data->get('banned-users', '');
        if (!empty($bannedUsers)) {
            $where[] = "a.creator_uid NOT IN (" . $bannedUsers . ")";
        }

        $query = "SELECT
                  a.title, a.description, a.created, a.hits, a.category_id, a.id, a.alias,                  
                  c.small, c.medium, c.square, c.large, c.uid,
                  (SELECT photo_id FROM #__social_covers WHERE uid = a.id and type='page' LIMIT 1) AS photo_id
                  FROM #__social_clusters AS a
                  LEFT JOIN #__social_avatars AS c ON c.uid = a.id
                  WHERE " . implode(' AND ', $where) . "  ";

        $order = Common::parse($this->data->get('easysocialorder', 'a.created|*|desc'));
        if ($order[0]) {
            $query .= 'ORDER BY ' . $order[0] . ' ' . $order[1] . ' ';
        }

        $query .= 'LIMIT ' . $startIndex . ', ' . $count;

        $result = Database::queryAll($query);

        if (!class_exists('FRoute')) {
            $file = JPATH_ADMINISTRATOR . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_easysocial' . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'easysocial.php';
            if (file_exists($file)) {
                require_once($file);
            }
            require_once(JPATH_ADMINISTRATOR . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_easysocial' . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'router.php');
        }

        $urlOptions = array(
            'layout'   => 'item',
            'external' => false,
            'sef'      => true
        );

        $avatar = ES::table('Avatar');
        $photo  = ES::table('Photo');

        $data = array();
        for ($i = 0; $i < count($result); $i++) {
            $urlOptions['id'] = $result[$i]['id'];
            $photo->load($result[$i]['photo_id']);
            $avatar->load(array(
                'uid'  => $result[$i]['uid'],
                'type' => 'page'
            ));
            $r = array(
                'title'       => $result[$i]['title'],
                'description' => $result[$i]['description']
            );

            $r['thumbnail'] = $photo->getSource('thumbnail');
            $r['image']     = ImageFallback::fallback(array(
                $photo->getSource('original'),
                $photo->getSource('large')
            ));

            if ($r['thumbnail'] == '' && $r['image'] != '') {
                $thumbnail      = $photo->getSource('thumbnail');
                $r['thumbnail'] = !empty($thumbnail) ? $thumbnail : $r['image'];
            }

            $r += array(
                'large_image'         => $photo->getSource('large'),
                'avatar_small_image'  => $avatar->getSource('small'),
                'avatar_medium_image' => $avatar->getSource('medium'),
                'avatar_square_image' => $avatar->getSource('square'),
                'avatar_large_image'  => $avatar->getSource('large'),
                'url'                 => FRoute::pages($urlOptions, true),
                'hits'                => $result[$i]['hits'],
                'creation_time'       => $result[$i]['created'],
                'alias'               => $result[$i]['alias'],
                'category_id'         => $result[$i]['category_id'],
                'id'                  => $result[$i]['id']
            );

            $data[] = $r;
        }

        return $data;
    }
}