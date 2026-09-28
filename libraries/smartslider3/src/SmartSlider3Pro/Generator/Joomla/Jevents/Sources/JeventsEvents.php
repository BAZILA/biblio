<?php

namespace Nextend\SmartSlider3Pro\Generator\Joomla\Jevents\Sources;

use DateTime;
use DateTimeZone;
use Joomla\CMS\Factory;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\Registry\Registry;
use Joomla\CMS\Uri\Uri;
use Nextend\Framework\Database\Database;
use Nextend\Framework\Filesystem\Filesystem;
use Nextend\Framework\Form\Container\ContainerTable;
use Nextend\Framework\Form\Element\MixedField\GeneratorOrder;
use Nextend\Framework\Form\Element\OnOff;
use Nextend\Framework\Form\Element\Select;
use Nextend\Framework\Form\Element\Select\Filter;
use Nextend\Framework\Form\Element\Text;
use Nextend\Framework\Form\Joomla\Element\Select\MenuItems;
use Nextend\Framework\Parser\Common;
use Nextend\SmartSlider3\Generator\AbstractGenerator;
use Nextend\SmartSlider3\Platform\Joomla\ImageFallback;
use Nextend\SmartSlider3Pro\Generator\Joomla\Jevents\Elements\JeventsCalendars;
use Nextend\SmartSlider3Pro\Generator\Joomla\Jevents\Elements\JeventsCategories;
use Nextend\SmartSlider3Pro\Generator\Joomla\Jevents\GeneratorGroupJevents;

class JeventsEvents extends AbstractGenerator {

    protected $layout = 'event';

    public function getDescription() {
        return sprintf(n2_('Creates slides from %1$s.'), n2_('One time events'));
    }

    public function renderFields($container) {
        parent::renderFields($container);

        $filterGroup = new ContainerTable($container, 'filter', n2_('Filter'));

        $source = $filterGroup->createRow('source-row');
        new JeventsCategories($source, 'sourcecategories', n2_('Category'), 0, array(
            'isMultiple' => true
        ));
        new JeventsCalendars($source, 'sourcecalendars', 'Calendar', 0, array(
            'isMultiple' => true
        ));

        $limit = $filterGroup->createRow('limit-row');
        new Filter($limit, 'started', n2_('Started'), 0);
        new Filter($limit, 'ended', n2_('Ended'), -1);
        new Filter($limit, 'noendtime', 'Specified end time', 0);

        new Text($limit, 'location', n2_('Location'), '*');
        new Text($limit, 'dateformat', n2_('Date format'), 'm-d-Y');
        new Text($limit, 'timeformat', n2_('Time format'), 'G:i');
        new Text($limit, 'datelanguage', n2_('Date language'), '');

        new Text($limit, 'variableoffset', n2_('Date variable offset'), '', array(
            'tipLabel'       => n2_('Date variable offset'),
            'tipDescription' => n2_('Timezone offset in hours. For example: +2 or -7. If you leave it empty, Joomla\'s System -> Global Configuration -> Server -> Server Time Zone setting will be used.')
        ));

        new Text($limit, 'filteroffset', n2_('Date filter offset'), '', array(
            'tipLabel'       => n2_('Date filter offset'),
            'tipDescription' => n2_('Timezone offset in hours. For example: +2 or -7. If you leave it empty, Joomla\'s System -> Global Configuration -> Server -> Server Time Zone setting will be used.')
        ));


        new MenuItems($limit, 'itemid', n2_('Menu item (item ID)'), 0);
        new Select($limit, 'eventstate', n2_('Status'), 1, array(
            'options' => array(
                ''   => n2_('All'),
                '1'  => n2_('Published'),
                '0'  => n2_('Unpublished'),
                '-1' => n2_('Trashed')
            )
        ));

        $standardImages = $filterGroup->createRow('images-row');
        new OnOff($standardImages, 'multiimages', 'JEvents Standard Image and File Uploads plugin', 0);

        $orderGroup = new ContainerTable($container, 'order-group', n2_('Order'));
        $order      = $orderGroup->createRow('order-row');
        new GeneratorOrder($order, 'jeventsorder', 'a.dtstart|*|desc', array(
            'options' => array(
                ''           => n2_('None'),
                'a.dtstart'  => n2_('Start date'),
                'a.dtend'    => n2_('End date'),
                'b.created'  => n2_('Creation time'),
                'a.modified' => n2_('Modification time'),
                'a.summary'  => n2_('Title'),
                'a.hits'     => n2_('Hits'),
                'b.ev_id'    => 'ID'
            )
        ));
    }

    protected function _getData($count, $startIndex) {

        $categories = array_map('intval', explode('||', $this->data->get('sourcecategories', '')));
        $calendars  = array_map('intval', explode('||', $this->data->get('sourcecalendars', '')));

        $dateFormat = $this->data->get('dateformat', 'Y-m-d');
        if (empty($dateFormat)) {
            $dateFormat = 'Y-m-d';
        }

        $timeFormat = $this->data->get('timeformat', 'H:i:s');
        if (empty($timeFormat)) {
            $timeFormat = 'H:i:s';
        }

        $dateLanguage = $this->data->get('datelanguage', '');

        $itemId = $this->data->get('itemid', '0');

        $innerWhere = array();
        if (!in_array('0', $categories)) {
            $innerWhere[] = ' catid IN(' . implode(', ', $categories) . ')';
        }
        if (!in_array('0', $calendars)) {
            $innerWhere[] = ' icsid IN(' . implode(', ', $calendars) . ')';
        }

        if (!empty($innerWhere)) {
            $innerWhereStrAll = 'WHERE';
            $innerWhereStrAll .= implode(' AND ', $innerWhere);
        } else {
            $innerWhereStrAll = '';
        }

        $where = array(
            'a.evdet_id IN (SELECT detail_id FROM #__jevents_vevent ' . $innerWhereStrAll . ')',
            'a.evdet_id NOT IN (SELECT eventdetail_id FROM #__jevents_repetition GROUP BY eventdetail_id HAVING COUNT(eventdetail_id) > 1)'
        );

        if (Filesystem::existsFile(JPATH_SITE . DIRECTORY_SEPARATOR . 'plugins' . DIRECTORY_SEPARATOR . 'jevents' . DIRECTORY_SEPARATOR . 'jevfiles' . DIRECTORY_SEPARATOR . 'jevfiles.php') && $this->data->get('multiimages', 0)) {
            $multi = true;
        } else {
            $multi = false;
        }

        $folder = '';
        if ($multi) {
            $plugin = PluginHelper::getPlugin('jevents', 'jevfiles');
            $params = new Registry($plugin->params);
            $folder .= rtrim(Uri::root(false), '/') . '/' . trim($params->get('image_path', 'images'), '/') . '/' . trim($params->get('folder'), '/');
        }

        $config   = Factory::getConfig();
        $timezone = new DateTimeZone($config->get('offset'));
        $JOffset  = $offset = $timezone->getOffset(new DateTime);

        if ($this->data->get('filteroffset', '') !== '') {
            $offset = intval($this->data->get('filteroffset', 0)) * 3600;
        }

        $today = time() + $offset;

        switch ($this->data->get('started', '0')) {
            case 1:
                $where[] = 'a.dtstart < ' . $today;
                break;
            case -1:
                $where[] = 'a.dtstart >= ' . $today;
                break;
        }

        switch ($this->data->get('ended', '-1')) {
            case 1:
                $where[] = 'a.dtend < ' . $today;
                break;
            case -1:
                $where[] = 'a.dtend >= ' . $today;
                break;
        }

        switch ($this->data->get('noendtime', 0)) {
            case 1:
                $where[] = 'a.noendtime = 0';
                break;
            case -1:
                $where[] = 'a.noendtime = 1';
                break;
        }

        $location = $this->data->get('location', '*');
        if ($location != '*' && !empty($location)) {
            $where[] = "location = '" . $location . "'";
        }

        $state = $this->data->get('eventstate', '1');
        if ($state != "") {
            $where[] = "b.state = '" . $state . "'";
        }

        $order = Common::parse($this->data->get('jeventsorder', 'a.dtstart|*|desc'));
        if ($order[0]) {
            $orderBy = 'ORDER BY ' . $order[0] . ' ' . $order[1] . ' ';
        }

        $query = 'SELECT d.rp_id, b.ev_id, FROM_UNIXTIME(a.dtstart) AS event_start,
                    FROM_UNIXTIME(a.dtend) AS event_end, a.description, a.location, a.summary,
                    a.contact, a.hits, a.extra_info';

        $query .= ' FROM #__jevents_vevdetail AS a LEFT JOIN #__jevents_vevent
                    AS b ON a.evdet_id = b.detail_id ';

        $query .= 'LEFT JOIN #__jevents_repetition AS d ON a.evdet_id = d.eventid ';

        $query .= ' WHERE ' . implode(' AND ', $where) . ' GROUP BY b.ev_id ' . $orderBy . ' LIMIT ' . $startIndex . ', ' . $count;

        $result = Database::queryAll($query);
        $data   = array();

        if ($multi) {
            $query = "SELECT ev_id,";
            for ($i = 1; $i < 30; $i++) {
                $query .= "imagename" . $i . ",";
            }
            $query          .= "imagename30 FROM #__jev_files_combined WHERE ev_id IN (SELECT ev_id FROM #__jevents_vevent " . $innerWhereStrAll . ") AND ev_id NOT IN (SELECT eventid FROM #__jevents_repetition GROUP BY eventid HAVING COUNT(eventid) > 1)";
            $jevfilesresult = Database::queryAll($query);
            foreach ($jevfilesresult as $files) {
                $event_id = $files['ev_id'];
                unset($files['ev_id']);
                foreach ($files as $file) {
                    if (!empty($file)) {
                        $jffile[$event_id][]           = $folder . '/' . $file;
                        $jffileoriginals[$event_id][]  = $folder . '/originals/orig_' . $file;
                        $jffilethumbnails[$event_id][] = $folder . '/thumbnails/thumb_' . $file;
                    }
                }
            }
        }

        if ($this->data->get('variableoffset', '') !== '') {
            $offset = intval($this->data->get('variableoffset', 0)) * 3600;
        } else {
            $offset = $JOffset;
        }

        foreach ($result as $res) {
            $r = array(
                'title'       => $res['summary'],
                'description' => $res['description']
            );

            $image     = '';
            $thumbnail = '';
            if ($multi) {
                $i = 0;
                if (isset($jffile[$res['ev_id']])) {
                    $images = array();
                    foreach ($jffile[$res['ev_id']] as $jff) {
                        $images += array(
                            'image_' . $i       => $jff,
                            'image_orig_' . $i  => $jffileoriginals[$res['ev_id']][$i],
                            'image_thumb_' . $i => $jffilethumbnails[$res['ev_id']][$i]

                        );
                        if (empty($image)) {
                            $image     = $images['image_orig_' . $i];
                            $thumbnail = $images['image_thumb_' . $i];
                        }
                        $i++;
                    }
                }
            }

            $r['image'] = ImageFallback::fallback(array($image), array(
                $res['description']
            ), $folder);

            $r['thumbnail'] = ImageFallback::fallback(array(
                $thumbnail,
                $r['image']
            ), array(), $folder);

            $r += array(
                'url'        => 'index.php?option=com_jevents&task=icalevent.detail&evid=' . $res['ev_id'] . '&Itemid=' . $itemId,
                'start_date' => GeneratorGroupJevents::formatDate(strtotime($res['event_start']) + $offset, 0, $dateFormat, $dateLanguage),
                'start_time' => GeneratorGroupJevents::formatDate(strtotime($res['event_start']) + $offset, 1, $timeFormat, $dateLanguage),
                'end_date'   => GeneratorGroupJevents::formatDate(strtotime($res['event_end']) + $offset, 0, $dateFormat, $dateLanguage),
                'end_time'   => GeneratorGroupJevents::formatDate(strtotime($res['event_end']) + $offset, 1, $timeFormat, $dateLanguage),
                'location'   => $res['location'],
                'contact'    => $res['contact'],
                'hits'       => $res['hits'],
                'extra_info' => $res['extra_info'],
                'ev_id'      => $res['ev_id'],
                'rp_id'      => $res['rp_id']
            );

            if ($multi) {
                $r = array_merge($r, $images);
            }
            $data[] = $r;
        }

        return $data;
    }
}