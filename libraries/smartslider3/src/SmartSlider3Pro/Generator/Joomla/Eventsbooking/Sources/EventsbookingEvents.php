<?php

namespace Nextend\SmartSlider3Pro\Generator\Joomla\Eventsbooking\Sources;

use EventbookingHelper;
use EventbookingHelperRoute;
use Joomla\CMS\Router\Route;
use Nextend\Framework\Database\Database;
use Nextend\Framework\Form\Container\ContainerTable;
use Nextend\Framework\Form\Element\MixedField\GeneratorOrder;
use Nextend\Framework\Form\Element\Select;
use Nextend\Framework\Form\Element\Select\Filter;
use Nextend\Framework\Form\Element\Text;
use Nextend\Framework\Form\Joomla\Element\Select\MenuItems;
use Nextend\Framework\Parser\Common;
use Nextend\SmartSlider3\Generator\AbstractGenerator;
use Nextend\SmartSlider3\Platform\Joomla\ImageFallback;
use Nextend\SmartSlider3Pro\Generator\Joomla\Eventsbooking\Elements\EventsbookingCategories;
use Nextend\SmartSlider3Pro\Generator\Joomla\Eventsbooking\Elements\EventsbookingLocations;

require_once(JPATH_SITE . '/components/com_eventbooking/helper/helper.php');
require_once(JPATH_SITE . '/components/com_eventbooking/helper/route.php');

class EventsbookingEvents extends AbstractGenerator {

    protected $layout = 'event';

    public function getDescription() {
        return sprintf(n2_('Creates slides from %1$s content.'), 'Event Booking');
    }

    private function formatDate($datetime, $dateOrTime, $format) {
        if ($dateOrTime == 1 || $datetime != '0000-00-00 00:00:00') {
            return date($format, strtotime($datetime));
        } else {
            return '';
        }
    }

    public function renderFields($container) {
        parent::renderFields($container);

        $filterGroup = new ContainerTable($container, 'filter', n2_('Filter'));

        $source = $filterGroup->createRow('source-row');
        new EventsbookingCategories($source, 'sourcecategories', n2_('Categories'), 0, array(
            'isMultiple' => true
        ));
        new EventsbookingLocations($source, 'sourcelocations', n2_('Locations'), 0, array(
            'isMultiple' => true
        ));

        $limit = $filterGroup->createRow('limit-row');
        new Filter($limit, 'started', n2_('Started'), 0);
        new Filter($limit, 'ended', n2_('Ended'), -1);
        new Filter($limit, 'published', n2_('Published'), 1);
        new Filter($limit, 'featured', n2_('Featured'), 0);
        new Select($limit, 'recurring', n2_('Recurring'), '0', array(
            'options' => array(
                '0' => n2_('All'),
                '1' => n2_('All, but from recurring ones only parent events'),
                '2' => n2_('Only recurring events'),
                '3' => n2_('Only recurring event parents'),
                '4' => n2_('Only not recurring events')
            )
        ));


        $variables = $filterGroup->createRow('variable');
        new Text($variables, 'dateformat', n2_('Date format'), 'm-d-Y');
        new Text($variables, 'timeformat', n2_('Time format'), 'G:i');
        new MenuItems($variables, 'itemid', n2_('Menu item (item ID)'), 0);

        $orderGroup = new ContainerTable($container, 'order-group', n2_('Order'));
        $order      = $orderGroup->createRow('order-row');
        new GeneratorOrder($order, 'eventsbookingorder', 'event_date|*|asc', array(
            'options' => array(
                ''                           => n2_('None'),
                'event_date'                 => n2_('Start date'),
                'event_end_date'             => n2_('End date'),
                'id'                         => n2_('ID'),
                'title'                      => n2_('Title'),
                'individual_price'           => n2_('Price'),
                'discount'                   => n2_('Discount'),
                'registration_start_date'    => n2_('Registration start date'),
                'cut_off_date'               => n2_('Cut off date'),
                'cancel_before_date'         => n2_('Cancel before date'),
                'publish_up'                 => n2_('Publish up date'),
                'publish_down'               => n2_('Publish down date'),
                'early_bird_discount_date'   => n2_('Early bird discount date'),
                'early_bird_discount_amount' => n2_('Early bird discount amount'),
                'late_fee_date'              => n2_('Late fee date'),
                'recurring_end_date'         => n2_('Recurring end date'),
                'max_end_date'               => n2_('Max end date')
            )
        ));
    }

    protected function _getData($count, $startIndex) {
        $dateFormat = $this->data->get('dateformat', 'Y-m-d');
        if (empty($dateFormat)) {
            $dateFormat = 'Y-m-d';
        }

        $timeFormat = $this->data->get('timeformat', 'H:i:s');
        if (empty($timeFormat)) {
            $timeFormat = 'H:i:s';
        }

        $itemId = $this->data->get('itemid', '0');

        $where = array();

        $categories = array_map('intval', explode('||', $this->data->get('sourcecategories', '')));
        if (!in_array('0', $categories)) {
            $where[] = ' c.id IN (' . implode(', ', $categories) . ')';
        }

        $locations = array_map('intval', explode('||', $this->data->get('sourcelocations', '')));
        if (!in_array('0', $locations)) {
            $where[] = ' e.location_id IN(' . implode(', ', $locations) . ')';
        }

        $today = date('Y-m-d h:i:s', time());

        switch ($this->data->get('started', '0')) {
            case 1:
                $where[] = " e.event_date < '" . $today . "'";
                break;
            case -1:
                $where[] = " e.event_date >= '" . $today . "'";
                break;
        }

        switch ($this->data->get('ended', '-1')) {
            case 1:
                $where[] = " (e.event_end_date < '" . $today . "' AND e.event_end_date <> '0000-00-00 00:00:00')";
                break;
            case -1:
                $where[] = " (e.event_end_date >= '" . $today . "' OR e.event_end_date = '0000-00-00 00:00:00')";
                break;
        }

        switch ($this->data->get('recurring', '0')) {
            case 0:
                break;
            case 1:
                $where[] = " e.parent_id = 0";
                break;
            case 2:
                $where[] = " (e.recurring_type > 0 OR e.parent_id > 0)";
                break;
            case 3:
                $where[] = " e.recurring_type > 0";
                break;
            case 4:
                $where[] = " e.recurring_frequency is NULL";
                break;
        }

        switch ($this->data->get('published', '1')) {
            case 0:
                break;
            case 1:
                $where[] = " e.published = 1";
                break;
            case -1:
                $where[] = " e.published = 0";
                break;
        }

        switch ($this->data->get('featured', '1')) {
            case 0:
                break;
            case 1:
                $where[] = " e.featured = 1";
                break;
            case -1:
                $where[] = " e.featured = 0";
                break;
        }

        $query = 'SELECT e.*, c.name AS category_name, c.id AS category_id, c.image AS category_image, el.name AS location_name FROM #__eb_events AS e LEFT JOIN #__eb_event_categories AS ec ON e.id = ec.event_id LEFT JOIN #__eb_categories AS c ON ec.category_id = c.id LEFT JOIN #__eb_locations AS el ON el.id = e.location_id';
        if (!empty($where)) {
            $query .= ' WHERE' . implode(' AND ', $where);
        }

        $order = Common::parse($this->data->get('eventsbookingorder', 'event_date|*|asc'));
        if ($order[0]) {
            $query .= ' GROUP BY e.id ORDER BY e.' . $order[0] . ' ' . $order[1] . ' ';
        }

        $query .= ' LIMIT ' . $startIndex . ', ' . $count;

        $result = Database::queryAll($query);
        $data   = array();
        $config = EventbookingHelper::getConfig();
        foreach ($result as $res) {
            $r = array(
                'title'             => $res['title'],
                'description'       => $res['description'],
                'short_description' => $res['short_description']
            );

            $r['image'] = ImageFallback::fallback(array(
                !empty($res['image']) ? $res['image'] : '',
                !empty($res['thumb']) ? 'images/com_eventbooking/' . $res['thumb'] : '',
                !empty($res['thumb']) ? 'media/com_eventbooking/images/' . $res['thumb'] : ''
            ), array(
                $res['description'],
                $res['short_description']
            ));

            $r['thumbnail'] = ImageFallback::fallback(array(
                !empty($res['thumb']) ? 'images/com_eventbooking/thumb/' . $res['thumb'] : '',
                !empty($res['thumb']) ? 'media/com_eventbooking/images/thumb/' . $res['thumb'] : '',
                $r['image']
            ));

            $r      += array(
                'url'                                    => Route::_(EventbookingHelperRoute::getEventRoute($res['id'], 0, $itemId), false),
                'category_name'                          => $res['category_name'],
                'category_url'                           => Route::_(EventbookingHelperRoute::getCategoryRoute($res['category_id'], $itemId), false),
                'category_image'                         => ImageFallback::fallback(array($res['category_image'])),
                'start_date'                             => $this->formatDate($res['event_date'], 0, $dateFormat),
                'start_time'                             => $this->formatDate($res['event_date'], 1, $timeFormat),
                'end_date'                               => $this->formatDate($res['event_end_date'], 0, $dateFormat),
                'end_time'                               => $this->formatDate($res['event_end_date'], 1, $timeFormat),
                'location_name'                          => $res['location_name'],
                'price'                                  => EventbookingHelper::formatCurrency($res['individual_price'], $config, $res['currency_symbol']),
                'discount'                               => EventbookingHelper::formatCurrency($res['discount'], $config, $res['currency_symbol']),
                'unformatted_price'                      => $res['individual_price'],
                'unformatted_discount'                   => $res['discount'],
                'tax_rate'                               => $res['tax_rate'],
                'price_with_tax'                         => EventbookingHelper::formatCurrency(round($res['individual_price'] * (1 + $res['tax_rate'] / 100), 2), $config, $res['currency_symbol']),
                'unformatted_price_with_tax'             => round($res['individual_price'] * (1 + $res['tax_rate'] / 100), 2),
                'early_bird_discount_date'               => $this->formatDate($res['early_bird_discount_date'], 0, $dateFormat),
                'early_bird_discount_amount'             => EventbookingHelper::formatCurrency($res['early_bird_discount_amount'], $config, $res['currency_symbol']),
                'unformatted_early_bird_discount_amount' => $res['early_bird_discount_amount'],
                'cut_off_date'                           => $this->formatDate($res['cut_off_date'], 0, $dateFormat),
                'cancel_before_date'                     => $this->formatDate($res['cancel_before_date'], 0, $dateFormat),
                'recurring_end_date'                     => $this->formatDate($res['recurring_end_date'], 0, $dateFormat),
                'registration_start_date'                => $this->formatDate($res['registration_start_date'], 0, $dateFormat)
            );
            $data[] = $r;
        }

        return $data;
    }

}