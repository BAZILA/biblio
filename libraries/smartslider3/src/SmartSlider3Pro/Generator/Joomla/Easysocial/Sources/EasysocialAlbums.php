<?php

namespace Nextend\SmartSlider3Pro\Generator\Joomla\Easysocial\Sources;

use ES;
use Nextend\Framework\Database\Database;
use Nextend\Framework\Form\Container\ContainerTable;
use Nextend\Framework\Form\Element\MixedField\GeneratorOrder;
use Nextend\Framework\Form\Element\Select\Filter;
use Nextend\Framework\Form\Element\Text;
use Nextend\Framework\Parser\Common;
use Nextend\SmartSlider3\Generator\AbstractGenerator;
use Nextend\SmartSlider3Pro\Generator\Joomla\Easysocial\Elements\EasysocialCategories;

class EasysocialAlbums extends AbstractGenerator {

    protected $layout = 'image';

    public function getDescription() {
        return sprintf(n2_('Creates slides from %1$s content.'), 'EasySocial ' . n2_('Albums'));
    }

    public function renderFields($container) {
        parent::renderFields($container);

        $filterGroup = new ContainerTable($container, 'filter', n2_('Filter'));

        $source = $filterGroup->createRow('source-row');
        new EasysocialCategories($source, 'easysocialgroups', n2_('Groups'), 0, array(
            'isMultiple'  => true,
            'size'        => 10,
            'table'       => 'social_clusters',
            'clusterType' => 'group',
            'orderBy'     => 'id'
        ));
        new EasysocialCategories($source, 'easysocialevents', n2_('Events'), 0, array(
            'isMultiple'  => true,
            'size'        => 10,
            'table'       => 'social_clusters',
            'clusterType' => 'event',
            'orderBy'     => 'id'
        ));
        new EasysocialCategories($source, 'easysocialpages', n2_('Pages'), 0, array(
            'isMultiple'  => true,
            'size'        => 10,
            'table'       => 'social_clusters',
            'clusterType' => 'page',
            'orderBy'     => 'id'
        ));


        $limit = $filterGroup->createRow('limit-row');
        new Filter($limit, 'featured', n2_('Featured'), 0);
        new Text($limit, 'albumtitle', 'Album title', '*');
        new Filter($limit, 'avatarandcover', 'Include avatar and cover images', 0);


        new Text($limit, 'allowed-users', n2_('Allowed user IDs'), '', array(
            'tipLabel'       => n2_('Allowed user IDs'),
            'tipDescription' => n2_('Separate them by comma.'),
            'tipLink'        => 'https://smartslider.helpscoutdocs.com/article/1887-joomla-easysocial-generator#allowed-user-ids-50'
        ));

        new Text($limit, 'banned-users', n2_('Banned user IDs'), '', array(
            'tipLabel'       => n2_('Allowed user IDs'),
            'tipDescription' => n2_('Separate them by comma.'),
            'tipLink'        => 'https://smartslider.helpscoutdocs.com/article/1887-joomla-easysocial-generator#banned-user-ids-51'
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

        $groups = array_map('intval', explode('||', $this->data->get('easysocialgroups', '0')));
        $events = array_map('intval', explode('||', $this->data->get('easysocialevents', '0')));
        $pages  = array_map('intval', explode('||', $this->data->get('easysocialpages', '0')));

        if (!in_array('0', $groups) && !in_array('0', $events) && !in_array('0', $pages)) {
            $clusters = array_merge($groups, $events, $pages);
        } else {
            $cluster_helper = array();
            if (!in_array('0', $groups)) {
                $cluster_helper = array_merge($cluster_helper, $groups);
            }
            if (!in_array('0', $events)) {
                $cluster_helper = array_merge($cluster_helper, $events);
            }
            if (!in_array('0', $pages)) {
                $cluster_helper = array_merge($cluster_helper, $pages);
            }
            $clusters = $cluster_helper;
        }

        if (in_array('0', $groups) && in_array('0', $events) && in_array('0', $pages)) {
            $all = "OR uid IN (SELECT id FROM #__social_clusters WHERE cluster_type = 'group' OR cluster_type = 'event' OR cluster_type = 'page')";
        } else if (in_array('0', $groups) && in_array('0', $events)) {
            $all = "OR uid IN (SELECT id FROM #__social_clusters WHERE cluster_type = 'group' OR cluster_type = 'event')";
        } else if (in_array('0', $groups) && in_array('0', $pages)) {
            $all = "OR uid IN (SELECT id FROM #__social_clusters WHERE cluster_type = 'group' OR cluster_type = 'page')";
        } else if (in_array('0', $events) && in_array('0', $pages)) {
            $all = "OR uid IN (SELECT id FROM #__social_clusters WHERE cluster_type = 'event' OR cluster_type = 'page')";
        } else if (in_array('0', $pages)) {
            $all = "OR uid IN (SELECT id FROM #__social_clusters WHERE cluster_type = 'page')";
        } else if (in_array('0', $events)) {
            $all = "OR uid IN (SELECT id FROM #__social_clusters WHERE cluster_type = 'event')";
        } else if (in_array('0', $groups)) {
            $all = "OR uid IN (SELECT id FROM #__social_clusters WHERE cluster_type = 'group')";
        }

        $albumWhere = array("1=1");

        if (!empty($clusters)) {
            $albumWhere[] = "(uid IN (" . implode(',', $clusters) . ") " . $all . ")";
        }

        if ($this->data->get('avatarandcover', '0') == '0') {
            $albumWhere[] = "title = 'COM_EASYSOCIAL_ALBUMS_PROFILE_AVATAR' OR title = 'COM_EASYSOCIAL_ALBUMS_PROFILE_COVER'";
        } elseif ($this->data->get('avatarandcover', '0') == '-1') {
            $albumWhere[] = "title <> 'COM_EASYSOCIAL_ALBUMS_PROFILE_AVATAR' AND title <> 'COM_EASYSOCIAL_ALBUMS_PROFILE_COVER'";
        }

        $albumTitle = $this->data->get('albumtitle', '*');
        if ($albumTitle != '*' && !empty($albumTitle)) {
            $albumWhere[] = "title = '" . $albumTitle . "'";
        }

        $allowedUsers = $this->data->get('allowed-users', '');
        if (!empty($allowedUsers)) {
            $albumWhere[] = "user_id IN (" . $allowedUsers . ")";
        }

        $bannedUsers = $this->data->get('banned-users', '');
        if (!empty($bannedUsers)) {
            $albumWhere[] = "user_id NOT IN (" . $bannedUsers . ")";
        }

        $where = array(
            "album_id IN (SELECT id FROM #__social_albums WHERE  " . implode(' AND ', $albumWhere) . ")",
            "state = 1"
        );

        switch ($this->data->get('featured', 0)) {
            case 1:
                $where[] = 'featured = 1';
                break;
            case -1:
                $where[] = 'featured = 0';
                break;
        }

        $query = "SELECT
                  id, title
                  FROM #__social_photos
                  WHERE " . implode(' AND ', $where);


        $order = Common::parse($this->data->get('easysocialorder', 'created|*|desc'));
        if ($order[0]) {
            $query .= ' ORDER BY ' . $order[0] . ' ' . $order[1] . ' ';
        }

        $query .= " LIMIT " . $startIndex . ", " . $count;

        $result = Database::queryAll($query);
        $data   = array();

        // EasySocial quote: "Prior to ES 2.0, we no longer use square and featured as image variation". This is why the photos are returning thumbnail and large images.
        $photo = ES::table('Photo');
        for ($i = 0; $i < count($result); $i++) {
            $photo->load($result[$i]['id']);
            $r = array(
                'title'     => $result[$i]['title'],
                'image'     => $photo->getSource('original'),
                'thumbnail' => $photo->getSource('thumbnail'),
                'square'    => $photo->getSource('square'),
                'featured'  => $photo->getSource('featured'),
                'large'     => $photo->getSource('large'),
                'stock'     => $photo->getSource('stock')
            );

            $data[] = $r;
        }

        return $data;
    }
}
