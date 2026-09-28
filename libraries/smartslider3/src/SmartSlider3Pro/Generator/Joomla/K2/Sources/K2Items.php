<?php

namespace Nextend\SmartSlider3Pro\Generator\Joomla\K2\Sources;

use DateTime;
use DateTimeZone;
use Joomla\CMS\Factory;
use K2ModelItem;
use Nextend\Framework\Database\Database;
use Nextend\Framework\Filesystem\Filesystem;
use Nextend\Framework\Form\Container\ContainerTable;
use Nextend\Framework\Form\Element\MixedField\GeneratorOrder;
use Nextend\Framework\Form\Element\Select\Filter;
use Nextend\Framework\Form\Element\Text;
use Nextend\Framework\Form\Joomla\Element\Select\MenuItems;
use Nextend\Framework\Parser\Common;
use Nextend\Framework\ResourceTranslator\ResourceTranslator;
use Nextend\Framework\Url\Url;
use Nextend\SmartSlider3\Generator\AbstractGenerator;
use Nextend\SmartSlider3\Platform\Joomla\ImageFallback;
use Nextend\SmartSlider3Pro\Generator\Joomla\K2\Elements\K2Categories;
use Nextend\SmartSlider3Pro\Generator\Joomla\K2\Elements\K2Tags;

class K2Items extends AbstractGenerator {

    private $extraFields, $offset;

    protected $layout = 'article';

    public function getDescription() {
        return sprintf(n2_('Creates slides from %1$s content.'), n2_('Items'));
    }

    public function renderFields($container) {
        parent::renderFields($container);

        $filterGroup = new ContainerTable($container, 'filter', n2_('Filter'));

        $source = $filterGroup->createRow('source-row');
        new K2Categories($source, 'k2itemssourcecategories', n2_('Category'), 0, array(
            'isMultiple' => true
        ));
        new K2Tags($source, 'k2itemssourcetags', n2_('Tag'), 0, array(
            'isMultiple' => true
        ));


        $limit = $filterGroup->createRow('limit-row');
        new Filter($limit, 'k2itemssourcefeatured', n2_('Featured'), 0);
        new Text($limit, 'k2itemssourceuserid', n2_('User ID'), '');
        new Text($limit, 'k2itemssourcelanguage', n2_('Language'), '');
        new MenuItems($limit, 'k2itemsitemid', n2_('Menu item (item ID)'), 0);


        $date = $filterGroup->createRow('date-row');
        new Text($date, 'sourcedateformat', n2_('Date format'), 'm-d-Y');
        new Text($date, 'sourcetimeformat', n2_('Time format'), 'G:i');

        $orderGroup = new ContainerTable($container, 'order-group', n2_('Order'));
        $order      = $orderGroup->createRow('order-row');
        new GeneratorOrder($order, 'k2itemsorder', 'con.created|*|desc', array(
            'options' => array(
                ''                 => n2_('None'),
                'con.title'        => n2_('Title'),
                'cat_title'        => n2_('Category'),
                'created_by_alias' => n2_('User name'),
                'con.featured'     => n2_('Featured'),
                'con.ordering'     => n2_('Ordering'),
                'con.hits'         => n2_('Hits'),
                'con.created'      => n2_('Creation time'),
                'con.modified'     => n2_('Modification time')
            )
        ));
    }

    protected function resetState() {
        $this->extraFields = null;
    }

    function loadExtraFields() {
        static $extraFields = null;
        if ($extraFields === null) {

            $query = 'SELECT ';
            $query .= 'fgroups.name AS group_name, ';
            $query .= 'field.name AS name, ';
            $query .= 'field.id ';

            $query .= 'FROM #__k2_extra_fields_groups AS fgroups ';

            $query .= 'LEFT JOIN #__k2_extra_fields AS field ON field.group = fgroups.id ';

            $query .= 'WHERE field.published = 1 ';

            $this->extraFields = Database::queryAll($query, false, "assoc", "id");
        }
    }

    public function datify($date, $format) {
        $timestamp = strtotime($date) + $this->offset;

        return date($format, $timestamp);
    }

    public function removeSpecChar($str) {
        return iconv('UTF-8', 'ISO-8859-1//TRANSLIT//IGNORE', $str);
    }

    protected function _getData($count, $startIndex) {

        $categories = array_map('intval', explode('||', $this->data->get('k2itemssourcecategories', '0')));
        $tags       = array_map('intval', explode('||', $this->data->get('k2itemssourcetags', '0')));

        $query = 'SELECT ';
        $query .= 'con.id, ';
        $query .= 'con.title, ';
        $query .= 'con.alias, ';
        $query .= 'con.introtext, ';
        $query .= 'con.fulltext, ';
        $query .= 'con.catid, ';
        $query .= 'con.created, ';
        $query .= 'con.modified, ';
        $query .= 'cat.name AS cat_title, ';
        $query .= 'cat.alias AS cat_alias, ';
        $query .= 'con.created_by, ';
        $query .= 'usr.name AS created_by_alias, ';
        $query .= 'con.hits, ';
        $query .= 'con.image_caption, ';
        $query .= 'con.image_credits, ';
        $query .= 'con.video, ';
        $query .= 'con.extra_fields ';

        $query .= 'FROM #__k2_items AS con ';

        $query .= 'LEFT JOIN #__users AS usr ON usr.id = con.created_by ';

        $query .= 'LEFT JOIN #__k2_categories AS cat ON cat.id = con.catid ';

        $jNow  = Factory::getDate();
        $now   = $jNow->toSql();
        $where = array(
            "con.published = 1 AND (con.publish_up = '0000-00-00 00:00:00' OR con.publish_up IS NULL OR con.publish_up < '" . $now . "') AND (con.publish_down = '0000-00-00 00:00:00' OR con.publish_down IS NULL OR con.publish_down > '" . $now . "') ",
            'con.trash = 0 '
        );
        if (!in_array('0', $categories)) {
            $where[] = 'con.catid IN (' . implode(',', $categories) . ') ';
        }

        if (!in_array('0', $tags)) {
            $where[] = 'con.id IN ( SELECT itemID FROM #__k2_tags_xref WHERE tagID IN (' . implode(",", $tags) . ')) ';
        }

        $sourceUserId = intval($this->data->get('k2itemssourceuserid', ''));
        if ($sourceUserId) {
            $where[] = 'con.created_by = ' . $sourceUserId . ' ';
        }

        switch ($this->data->get('k2itemssourcefeatured', 0)) {
            case 1:
                $where[] = 'con.featured = 1 ';
                break;
            case -1:
                $where[] = 'con.featured = 0 ';
                break;
        }

        $language = $this->data->get('k2itemssourcelanguage', '*');
        if ($language) {
            $where[] = 'con.language = ' . Database::quote($language) . ' ';
        }

        if (count($where) > 0) {
            $query .= 'WHERE ' . implode(' AND ', $where) . ' ';
        }

        $order = Common::parse($this->data->get('k2itemsorder', 'con.created|*|desc'));
        if ($order[0]) {
            $query .= 'ORDER BY ' . $order[0] . ' ' . $order[1] . ' ';
        }

        $query  .= 'LIMIT ' . $startIndex . ', ' . $count . ' ';
        $result = Database::queryAll($query);
        $this->loadExtraFields();

        require_once(JPATH_SITE . '/components/com_k2/helpers/utilities.php');
        if (!class_exists('K2ModelItem')) {
            require_once(JPATH_ADMINISTRATOR . '/components/com_k2/models/model.php');
            require_once(JPATH_SITE . '/components/com_k2/models/item.php');
        }
        $k2item = new K2ModelItem();

        $config       = Factory::getConfig();
        $timezone     = new DateTimeZone($config->get('offset'));
        $this->offset = $timezone->getOffset(new DateTime);

        $data = array();
        for ($i = 0; $i < count($result); $i++) {

            $modified = '?t=' . strftime("%Y%m%d_%H%M%S", strtotime($result[$i]['modified']));

            $r = array(
                'title'       => $result[$i]['title'],
                'description' => $result[$i]['introtext'],
            );

            $thumbnail = JPATH_SITE . "/media/k2/items/cache/" . md5("Image" . $result[$i]['id']) . "_S.jpg";
            if (Filesystem::fileexists($thumbnail)) {
                $r['thumbnail'] = ResourceTranslator::urlToResource(Url::pathToUri($thumbnail)) . $modified;
            }

            $image = JPATH_SITE . "/media/k2/items/cache/" . md5("Image" . $result[$i]['id']) . "_XL.jpg";
            if (Filesystem::fileexists($image)) {
                $r['image'] = ResourceTranslator::urlToResource(Url::pathToUri($image)) . $modified;
            } else {
                $r['image'] = ImageFallback::fallback(array(), array($r['description']));
            }
            if (!isset($r['thumbnail'])) {
                $r['thumbnail'] = $r['image'];
            }

            $image = JPATH_SITE . "/media/k2/items/src/" . md5("Image" . $result[$i]['id']) . ".jpg";
            if (Filesystem::fileexists($image)) {
                $r['src_image'] = ResourceTranslator::urlToResource(Url::pathToUri($image)) . $modified;
            }

            if (!empty($result[$i]['video'])) {
                $r['video'] = $result[$i]['video'];
                preg_match_all('/(<source.*?src=[\'"](.*?)[\'"][^>]+>)/i', $result[$i]['video'], $video);
                $r['video_src'] = $video[2][0];
                preg_match_all('/(<source.*?src=[\'"](.*mp4)[\'"][^>]+>)/i', $result[$i]['video'], $mp4);
                if (isset($mp4[2][0])) {
                    $r['video_src_mp4'] = $mp4[2][0];
                }
            }

            $itemID = $this->data->get('k2itemsitemid', '0');
            $url    = 'index.php?option=com_k2&view=item&id=' . $result[$i]['id'] . ':' . $result[$i]['alias'];
            if (!empty($itemID) && $itemID != 0) {
                $url .= '&Itemid=' . $itemID;
            }

            $r += array(
                'url'              => $url,
                'url_label'        => n2_('View item'),
                'category_title'   => $result[$i]['cat_title'],
                'category_url'     => 'index.php?option=com_k2&view=itemlist&task=category&id=' . $result[$i]['catid'] . ':' . $result[$i]['cat_alias'],
                'alias'            => $result[$i]['alias'],
                'id'               => $result[$i]['id'],
                'category_id'      => $result[$i]['catid'],
                'created_by_alias' => $result[$i]['created_by_alias'],
                'hits'             => $result[$i]['hits'],
                'image_caption'    => $result[$i]['image_caption'],
                'image_credits'    => $result[$i]['image_credits'],
                'created_date'     => $this->datify($result[$i]['created'], $this->data->get('sourcedateformat', 'm-d-Y')),
                'created_time'     => $this->datify($result[$i]['created'], $this->data->get('sourcetimeformat', 'G:i'))
            );

            $item   = (object)$result[$i];
            $extras = $k2item->getItemExtraFields($result[$i]['extra_fields'], $item);

            $count = 0;
            if (is_array($extras) && count($extras) > 0) {
                foreach ($extras as $field) {
                    $count++;
                    $r['extra' . $count] = $r['extra' . $this->removeSpecChar($field->id)] = $r['extra' . $this->removeSpecChar($field->id . '_' . preg_replace("/\W|_/", "", $this->extraFields[$field->id]['group_name'] . '_' . $this->extraFields[$field->id]['name']))] = $field->value;
                }
            }
            $data[] = $r;
        }

        return $data;
    }

}
