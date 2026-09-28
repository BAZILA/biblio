<?php

namespace Nextend\SmartSlider3Pro\Generator\Joomla\Flexicontent\Sources;

use DateTime;
use DateTimeZone;
use FlexicontentHelperRoute;
use Joomla\CMS\Factory;
use Nextend\Framework\Database\Database;
use Nextend\Framework\Form\Container\ContainerTable;
use Nextend\Framework\Form\Element\MixedField\GeneratorOrder;
use Nextend\Framework\Form\Element\Select\Filter;
use Nextend\Framework\Form\Element\Text;
use Nextend\Framework\Form\Element\Textarea;
use Nextend\Framework\Parser\Common;
use Nextend\SmartSlider3\Generator\AbstractGenerator;
use Nextend\SmartSlider3\Platform\Joomla\ImageFallback;
use Nextend\SmartSlider3Pro\Generator\Joomla\Flexicontent\Elements\FlexicontentCategories;
use Nextend\SmartSlider3Pro\Generator\Joomla\Flexicontent\Elements\FlexicontentTags;
use Nextend\SmartSlider3Pro\Generator\Joomla\Flexicontent\Elements\FlexicontentTypes;

class FlexicontentItems extends AbstractGenerator {

    protected $layout = 'article';

    protected $translate = array();

    public function getDescription() {
        return sprintf(n2_('Creates slides from %1$s content.'), 'FLEXIcontent');
    }

    public function renderFields($container) {
        parent::renderFields($container);

        $filterGroup = new ContainerTable($container, 'filter', n2_('Filter'));

        $source = $filterGroup->createRow('source-row');
        new FlexicontentTypes($source, 'sourcetype', n2_('Type'), 0);
        new FlexicontentCategories($source, 'sourcecategory', n2_('Categories'), 0, array(
            'isMultiple' => true
        ));
        new FlexicontentTags($source, 'sourcetag', n2_('Tags'), 0, array(
            'isMultiple' => true
        ));

        $limit = $filterGroup->createRow('limit-row');
        new Filter($limit, 'sourcefeatured', n2_('Featured'), 0);
        new Text($limit, 'sourcelanguage', n2_('Language'), '*');
        new Text($limit, 'sourceids', n2_('Only display items with these IDs'), '');

        $date = $filterGroup->createRow('date-row');
        new Text($date, 'dateformat', n2_('Date format'), 'Y-m-d');
        new Text($date, 'timeformat', n2_('Time format'), 'G:i');
        new Text($date, 'offset', n2_('Offset hours'), '', array(
            'tipLabel'       => n2_('Offset hours'),
            'tipDescription' => n2_('Timezone offset in hours. For example: +2 or -7. If you leave it empty, Joomla\'s System -> Global Configuration -> Server -> Server Time Zone setting will be used.')
        ));
        new Textarea($date, 'translatedate', n2_('Translate date and time'), 'January->January||February->February||March->March', array(
            'width'  => 300,
            'height' => 100
        ));

        $orderGroup = new ContainerTable($container, 'order-group', n2_('Order'));
        $order      = $orderGroup->createRow('order-row');
        new GeneratorOrder($order, 'flexiorder', 'con.created|*|desc', array(
            'options' => array(
                ''                 => n2_('None'),
                'con.title'        => n2_('Title'),
                'cat_title'        => n2_('Category title'),
                'created_by_alias' => n2_('Username'),
                'con.featured'     => n2_('Featured'),
                'con.ordering'     => n2_('Ordering'),
                'con.hits'         => n2_('Hits'),
                'con.created'      => n2_('Creation time'),
                'con.modified'     => n2_('Modification time')
            )
        ));
    }

    protected function _getData($count, $startIndex) {

        $query = 'SELECT ';
        $query .= 'con.id, con.title, con.images, con.introtext, con.fulltext, con.hits, con.created, con.modified, cat.id AS category_id, cat.title AS category_title, users.name AS created_by, users.username AS created_by_username ';

        $query .= 'FROM #__content AS con ';

        $query .= 'LEFT JOIN #__flexicontent_cats_item_relations AS fcat ON fcat.itemid = con.id ';
        $query .= 'LEFT JOIN #__categories AS cat ON fcat.catid = cat.id ';
        $query .= 'LEFT JOIN #__users AS users ON con.created_by = users.id ';

        $jNow  = Factory::getDate();
        $now   = $jNow->toSql();
        $where = array(
            "(con.publish_up = '0000-00-00 00:00:00' OR con.publish_up IS NULL OR con.publish_up < '" . $now . "') AND (con.publish_down = '0000-00-00 00:00:00' OR con.publish_down IS NULL OR con.publish_down > '" . $now . "') ",
            'con.state = 1 '
        );

        $category = array_map('intval', explode('||', $this->data->get('sourcecategory', '')));
        if (!in_array('0', $category)) {
            $where[] = 'fcat.catid IN (' . implode(',', $category) . ') ';
        }

        $tag = array_map('intval', explode('||', $this->data->get('sourcetag', '0')));
        if (!in_array('0', $tag)) {
            $where[] = ' con.id IN (SELECT itemid FROM #__flexicontent_tags_item_relations WHERE tid IN(' . implode(',', $tag) . '))';
        }

        $type = array_map('intval', explode('||', $this->data->get('sourcetype', '0')));
        if (!in_array('0', $type)) {
            $where[] = ' con.id IN (SELECT item_id FROM #__flexicontent_items_ext WHERE type_id IN(' . implode(',', $type) . '))';
        }

        $ids = $this->data->get('sourceids', '');
        if (!empty($ids)) {
            $where[] = ' con.id IN (' . $ids . ')';
        }

        switch ($this->data->get('sourcefeatured', 0)) {
            case 1:
                $where[] = 'con.featured = 1 ';
                break;
            case -1:
                $where[] = 'con.featured = 0 ';
                break;
        }

        $language = $this->data->get('sourcelanguage', '*');
        if ($language) {
            $db      = Factory::getDbo();
            $where[] = 'con.language = ' . $db->quote($language) . ' ';
        }

        if (count($where)) {
            $query .= ' WHERE ' . implode(' AND ', $where);
        }

        $query .= 'GROUP BY con.id ';

        $order = Common::parse($this->data->get('flexiorder', 'con.created|*|desc'));
        if ($order[0]) {
            $query .= 'ORDER BY ' . $order[0] . ' ' . $order[1] . ' ';
        }

        $query .= 'LIMIT ' . $startIndex . ', ' . $count;

        $result = Database::queryAll($query);

        require_once(JPATH_SITE . DS . 'components' . DS . 'com_flexicontent' . DS . 'helpers' . DS . 'route.php');

        $this->processTranslateField();
        $dateFormat = $this->data->get('dateformat', 'Y-m-d');
        $timeFormat = $this->data->get('timeformat', 'G:i');

        $data = array();
        for ($i = 0; $i < count($result); $i++) {
            $r = array(
                'title'       => $result[$i]['title'],
                'description' => $result[$i]['introtext'],
                'introtext'   => $result[$i]['introtext'],
                'fulltext'    => $result[$i]['fulltext'],
            );

            if (!empty($result[$i]['images'])) {
                $img        = json_decode($result[$i]['images']);
                $r['image'] = $r['thumbnail'] = ImageFallback::fallback(array(
                    @$img->image_intro,
                    @$img->image_fulltext
                ), array(
                    $result[$i]['introtext']
                ));
            }

            $r += array(
                'url'                 => FlexicontentHelperRoute::getItemRoute($result[$i]['id'], $result[$i]['category_id']),
                'creation_date'       => $this->translate($this->datify($result[$i]['created'], $dateFormat)),
                'creation_time'       => $this->translate($this->datify($result[$i]['created'], $timeFormat)),
                'modification_date'   => $this->translate($this->datify($result[$i]['modified'], $dateFormat)),
                'modification_time'   => $this->translate($this->datify($result[$i]['modified'], $timeFormat)),
                'created_by'          => $result[$i]['created_by'],
                'created_by_username' => $result[$i]['created_by_username'],
                'hits'                => $result[$i]['hits'],
                'category_title'      => $result[$i]['category_title'],
                'id'                  => $result[$i]['id'],
                'category_id'         => $result[$i]['category_id'],
                'category_url'        => FlexicontentHelperRoute::getCategoryRoute($result[$i]['category_id'])
            );

            $r += $this->getFields($result[$i]['id']);

            $data[] = $r;
        }

        return $data;
    }

    private function getFields($id) {
        $query  = "SELECT item.value, fields.id, fields.name FROM #__flexicontent_fields_item_relations AS item LEFT JOIN #__flexicontent_fields AS fields ON item.field_id = fields.id WHERE item_id = " . $id;
        $fields = Database::queryAll($query);

        $data = array();
        foreach ($fields as $field) {
            $values = @unserialize($field['value']);
            if ($values === false) {
                $data += array($field['name'] . $field['id'] => $field['value']);
            } else {
                foreach ($values as $name => $value) {
                    $data += array($field['name'] . $field['id'] . '_' . $name => $value);
                }
            }
        }

        return $data;
    }

    private function datify($date, $format) {
        if ($date != "0000-00-00 00:00:00") {
            $config   = Factory::getConfig();
            $timezone = new DateTimeZone($config->get('offset'));

            $offset = $this->data->get('offset', '');
            if ($offset !== '') {
                $offset = intval($offset) * 3600;
            } else {
                $offset = $timezone->getOffset(new DateTime);
            }

            $result = date($format, strtotime($date) + $offset);

            return $result;
        } else {
            return '';
        }
    }

    private function processTranslateField() {
        $translateField  = $this->data->get('translatedate', '');
        $translateValues = explode('||', $translateField);
        if ($translateField != 'January->January||February->February||March->March' && !empty($translateValues)) {
            foreach ($translateValues as $translateValue) {
                $translateFromTo = explode('->', $translateValue);
                if (!empty($translateFromTo) && count($translateFromTo) == 2) {
                    $this->translate[$translateFromTo[0]] = $translateFromTo[1];
                }
            }
        }
    }

    private function translate($text) {
        if (!empty($this->translate) && !empty($text)) {
            foreach ($this->translate as $from => $to) {
                $text = str_replace($from, $to, $text);
            }
        }

        return $text;
    }
}