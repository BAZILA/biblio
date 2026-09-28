<?php

namespace Nextend\SmartSlider3Pro\Generator\Joomla\Rseventspro\Sources;

use DateTime;
use DateTimeZone;
use Joomla\CMS\Factory;
use Nextend\Framework\Database\Database;
use Nextend\Framework\Form\Container\ContainerTable;
use Nextend\Framework\Form\Element\MixedField\GeneratorOrder;
use Nextend\Framework\Form\Element\Select\Filter;
use Nextend\Framework\Form\Element\Text;
use Nextend\Framework\Form\Element\Textarea;
use Nextend\Framework\Form\Joomla\Element\Select\MenuItems;
use Nextend\Framework\Parser\Common;
use Nextend\SmartSlider3\Generator\AbstractGenerator;
use Nextend\SmartSlider3\Platform\Joomla\ImageFallback;
use Nextend\SmartSlider3Pro\Generator\Joomla\Rseventspro\Elements\RseventsproCategories;
use Nextend\SmartSlider3Pro\Generator\Joomla\Rseventspro\Elements\RseventsproGroups;
use Nextend\SmartSlider3Pro\Generator\Joomla\Rseventspro\Elements\RseventsproLocations;
use Nextend\SmartSlider3Pro\Generator\Joomla\Rseventspro\Elements\RseventsproTags;
use rseventsproHelper;
use RseventsproHelperRoute;


class RseventsproEvents extends AbstractGenerator {

    protected $layout = 'event';

    public function getDescription() {
        return sprintf(n2_('Creates slides from %1$s content.'), 'RSEvents!Pro');
    }

    public function renderFields($container) {
        parent::renderFields($container);

        $filterGroup = new ContainerTable($container, 'filter', n2_('Filter'));

        $source = $filterGroup->createRow('source-row');
        new RseventsproCategories($source, 'sourcecategories', n2_('Category'), 0, array(
            'isMultiple' => true
        ));
        new RseventsproGroups($source, 'sourcegroups', n2_('Group'), 0, array(
            'isMultiple' => true
        ));
        new RseventsproLocations($source, 'sourcelocations', n2_('Location'), 0, array(
            'isMultiple' => true
        ));
        new RseventsproTags($source, 'sourcetags', n2_('Tag'), 0, array(
            'isMultiple' => true
        ));


        $limit = $filterGroup->createRow('limit-row');
        new Filter($limit, 'started', n2_('Started'), 0);
        new Filter($limit, 'ended', n2_('Ended'), -1);
        new Filter($limit, 'featured', n2_('Featured'), 0);
        new Filter($limit, 'allday', n2_('All day event'), 0);
        new Filter($limit, 'recurring', n2_('Recurring events'), 0);
        new MenuItems($limit, 'itemid', n2_('Menu item (item ID)'), 0);


        $date = $filterGroup->createRow('date-row');
        new Text($date, 'rseventsprodate', n2_('Date format'), 'm-d-Y');
        new Text($date, 'rseventsprotime', n2_('Time format'), 'G:i');
        new Textarea($date, 'rseventstranslatedate', n2_('Translate date and time'), 'January->January||February->February||March->March', array(
            'width'  => 300,
            'height' => 100
        ));
        new Text($date, 'rseventsoffset', n2_('Date variable offset'), '', array(
            'tipLabel'       => n2_('Date variable offset'),
            'tipDescription' => n2_('Timezone offset in hours. For example: +2 or -7. If you leave it empty, Joomla\'s System -> Global Configuration -> Server -> Server Time Zone setting will be used.'),
            'tipLink'        => 'https://smartslider.helpscoutdocs.com/article/1920-joomla-rsevents-pro-generator'
        ));

        new Text($date, 'rseventsfilteroffset', n2_('Date filter offset'), '', array(
            'tipLabel'       => n2_('Date filter offset'),
            'tipDescription' => n2_('Timezone offset in hours. For example: +2 or -7. If you leave it empty, Joomla\'s System -> Global Configuration -> Server -> Server Time Zone setting will be used.'),
            'tipLink'        => 'https://smartslider.helpscoutdocs.com/article/1920-joomla-rsevents-pro-generator'
        ));

        $orderGroup = new ContainerTable($container, 'order-group', n2_('Order'));
        $order      = $orderGroup->createRow('order-row');
        new GeneratorOrder($order, 'rseventsproorder', 'start|*|desc', array(
            'options' => array(
                ''        => n2_('None'),
                'start'   => n2_('Start date'),
                'end'     => n2_('End date'),
                'created' => n2_('Creation date'),
                'name'    => n2_('Title'),
                'hits'    => n2_('Hits'),
                'id'      => 'ID'
            )
        ));
    }

    private function translate($from, $translate) {
        if (!empty($translate) && !empty($from)) {
            foreach ($translate as $key => $value) {
                $from = str_replace($key, $value, $from);
            }
        }

        return $from;
    }

    private function formatDate($datetime, $format = 'Y-m-d', $strtotime = true) {
        if ($datetime != '0000-00-00 00:00:00') {
            if ($strtotime) {
                $datetime = strtotime($datetime);
            }

            return date($format, $datetime);
        } else {
            return '';
        }
    }

    protected function _getData($count, $startIndex) {
        require_once(JPATH_SITE . '/components/com_rseventspro/helpers/rseventspro.php');
        require_once(JPATH_SITE . '/components/com_rseventspro/helpers/route.php');

        $categories = array_map('intval', explode('||', $this->data->get('sourcecategories', '')));
        $groups     = array_map('intval', explode('||', $this->data->get('sourcegroups', '')));
        $tags       = array_map('intval', explode('||', $this->data->get('sourcetags', '')));
        $locations  = array_map('intval', explode('||', $this->data->get('sourcelocations', '')));

        $where = array('re.published <> 0');

        if (!in_array('0', $categories)) {
            $where[] = "re.id IN (SELECT ide FROM #__rseventspro_taxonomy WHERE id IN (" . implode(', ', $categories) . ") AND type = 'category')";
        }

        if (!in_array('0', $groups)) {
            $where[] = "re.id IN (SELECT ide FROM #__rseventspro_taxonomy WHERE id IN (" . implode(', ', $groups) . ") AND type = 'groups')";
        }

        if (!in_array('0', $tags)) {
            $where[] = "re.id IN (SELECT ide FROM #__rseventspro_taxonomy WHERE id IN (" . implode(', ', $tags) . ") AND type = 'tag')";
        }

        if (!in_array('0', $locations)) {
            $where[] = "re.location IN (" . implode(', ', $locations) . ")";
        }
        if (method_exists('rseventsproHelper', 'showdate')) {
            $today = rseventsproHelper::showdate("now", 'Y-m-d H:i:s');
        } else {
            $today = date('Y-m-d H:i:s', time());
        }

        $config   = Factory::getConfig();
        $timezone = new DateTimeZone($config->get('offset'));
        $offset   = $timezone->getOffset(new DateTime);

        if ($this->data->get('rseventsfilteroffset', '') !== '') {
            $offset = intval($this->data->get('rseventsfilteroffset', 0)) * 3600;
        }

        switch ($this->data->get('started', '0')) {
            case 1:
                $where[] = "DATE_ADD(re.start, INTERVAL " . $offset . " SECOND) < '" . $today . "'";
                break;
            case -1:
                $where[] = "DATE_ADD(re.start, INTERVAL " . $offset . " SECOND) >= '" . $today . "'";
                break;
        }

        switch ($this->data->get('ended', '-1')) {
            case 1:
                $where[] = "((DATE_ADD(re.end, INTERVAL " . $offset . " SECOND) < '" . $today . "' AND re.allday = 0) OR (DATE_ADD(re.start , INTERVAL " . $offset . " SECOND)< '" . $today . "' AND re.allday = 1))";
                break;
            case -1:
                $where[] = "((DATE_ADD(re.end, INTERVAL " . $offset . " SECOND) >= '" . $today . "' AND re.allday = 0) OR (DATE_ADD(re.start, INTERVAL " . $offset . " SECOND) >= '" . $today . "' AND re.allday = 1))";
                break;
        }

        switch ($this->data->get('allday', '0')) {
            case 1:
                $where[] = "re.allday = 1";
                break;
            case -1:
                $where[] = "re.allday = 0";
                break;
        }

        switch ($this->data->get('recurring', '0')) {
            case 1:
                $where[] = "re.recurring = 1";
                break;
            case -1:
                $where[] = "re.recurring = 0";
                break;
        }

        switch ($this->data->get('featured', '0')) {
            case 1:
                $where[] = "re.featured = 1";
                break;
            case -1:
                $where[] = "re.featured = 0";
                break;
        }

        $query = 'SELECT
        re.start, re.end, re.id, re.name, re.description, re.created, re.URL, re.email, re.phone, re.metaname, re.metakeywords, re.metadescription, re.hits, re.icon, 
        rl.name as loc_name, rl.url as loc_url, rl.address, rl.description AS loc_description, rl.coordinates
        FROM #__rseventspro_events AS re
        LEFT JOIN #__rseventspro_locations AS rl ON re.location = rl.id
        WHERE ' . implode(' AND ', $where) . ' ';

        $order = Common::parse($this->data->get('rseventsproorder', 'start|*|desc'));
        if ($order[0]) {
            $query .= 'ORDER BY re.' . $order[0] . ' ' . $order[1] . ' ';
        }

        $query .= 'LIMIT ' . $startIndex . ', ' . $count;

        $result = Database::queryAll($query);

        $data       = array();
        $dateFormat = $this->data->get('rseventsprodate', 'm-d-Y');
        $timeFormat = $this->data->get('rseventsprotime', 'G:i');

        $translateDate  = $this->data->get('rseventstranslatedate', '');
        $translateValue = explode('||', $translateDate);
        $translate      = array();
        if ($translateDate != 'January->January||February->February||March->March' && !empty($translateValue)) {
            foreach ($translateValue as $tv) {
                $translateArray = explode('->', $tv);
                if (!empty($translateArray) && count($translateArray) == 2) {
                    $translate[$translateArray[0]] = $translateArray[1];
                }
            }
        }

        if ($this->data->get('rseventsoffset', '') !== '') {
            $offset = intval($this->data->get('rseventsoffset', 0)) * 3600;
        }

        $itemID = $this->data->get('itemid', '0');

        $config = rseventsproHelper::getConfig();
        foreach ($result as $res) {
            $r = array(
                'title'       => $res['name'],
                'description' => $res['description']
            );

            if (isset($res['icon'])) {
                $res['icon'] = 'components/com_rseventspro/assets/images/events/' . $res['icon'];
            } else {
                $res['icon'] = '';
            }

            $r['image'] = $r['thumbnail'] = ImageFallback::fallback(array(
                @$res['icon']
            ), array(
                @$res['description']
            ));

            $r['icon_small_width'] = rseventsproHelper::thumb($res['id'], $config->icon_small_width);
            $r['icon_big_width']   = rseventsproHelper::thumb($res['id'], $config->icon_big_width);

            if (isset($r['icon_small_width'])) {
                $r['thumbnail'] = $r['icon_small_width'];
            } else {
                $r['thumbnail'] = $r['image'];
            }

            if ($res['start'] != '0000-00-00 00:00:00') {
                $res['start'] = $this->formatDate(strtotime($res['start']) + $offset, 'Y-m-d H:i:s', false);
            }
            if ($res['end'] != '0000-00-00 00:00:00') {
                $res['end'] = $this->formatDate(strtotime($res['end']) + $offset, 'Y-m-d H:i:s', false);
            }
            $res['created'] = $this->formatDate(strtotime($res['created']) + $offset, 'Y-m-d H:i:s', false);
            if (method_exists('rseventsproHelper', 'showdate')) {
                $r += array(
                    'start_date' => $this->translate(rseventsproHelper::showdate($res['start'], $dateFormat), $translate),
                    'start_time' => $this->translate(rseventsproHelper::showdate($res['start'], $timeFormat), $translate),
                    'end_date'   => $this->translate(rseventsproHelper::showdate($res['end'], $dateFormat), $translate),
                    'end_time'   => $this->translate(rseventsproHelper::showdate($res['end'], $timeFormat), $translate)
                );
            } else {
                $r += array(
                    'start_date' => $this->translate($this->formatDate($res['start'], $dateFormat), $translate),
                    'start_time' => $this->translate($this->formatDate($res['start'], $timeFormat), $translate),
                    'end_date'   => $this->translate($this->formatDate($res['end'], $dateFormat), $translate),
                    'end_time'   => $this->translate($this->formatDate($res['end'], $timeFormat), $translate)
                );
            }

            if (empty($itemID)) {
                $itemID = rseventsproHelper::itemid($res['id']);
            }

            $r += array(
                'url'                  => rseventsproHelper::route('index.php?option=com_rseventspro&layout=show&id=' . rseventsproHelper::sef($res['id'], $res['name']), true, $itemID),
                'created'              => $res['created'],
                'website'              => $res['URL'],
                'email'                => $res['email'],
                'phone'                => $res['phone'],
                'metaname'             => $res['metaname'],
                'metakeywords'         => $res['metakeywords'],
                'metadescription'      => $res['metadescription'],
                'hits'                 => $res['hits'],
                'id'                   => $res['id'],
                'location_name'        => $res['loc_name'],
                'location_url'         => $res['loc_url'],
                'location_address'     => $res['address'],
                'location_description' => $res['loc_description']
            );

            $coordinates = explode(',', $res['coordinates']);
            if (count($coordinates) == 2) {
                $r += array(
                    'location_coordinates_lat'  => $coordinates[0],
                    'location_coordinates_long' => $coordinates[1]
                );
            }

            $data[] = $r;
        }

        return $data;
    }
}