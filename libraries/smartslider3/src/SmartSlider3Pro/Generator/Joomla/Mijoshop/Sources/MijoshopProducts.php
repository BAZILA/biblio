<?php

namespace Nextend\SmartSlider3Pro\Generator\Joomla\Mijoshop\Sources;

use Joomla\CMS\Factory;
use MijoShop;
use Nextend\Framework\Database\Database;
use Nextend\Framework\Filesystem\Filesystem;
use Nextend\Framework\Form\Container\ContainerTable;
use Nextend\Framework\Form\Element\MixedField\GeneratorOrder;
use Nextend\Framework\Form\Element\Select\Filter;
use Nextend\Framework\Parser\Common;
use Nextend\Framework\ResourceTranslator\ResourceTranslator;
use Nextend\SmartSlider3\Generator\AbstractGenerator;
use Nextend\SmartSlider3\Platform\Joomla\ImageFallback;
use Nextend\SmartSlider3Pro\Generator\Joomla\Mijoshop\Elements\MijoshopCategories;
use Nextend\SmartSlider3Pro\Generator\Joomla\Mijoshop\Elements\MijoshopLanguages;
use Nextend\SmartSlider3Pro\Generator\Joomla\Mijoshop\Elements\MijoshopManufacturers;

class MijoshopProducts extends AbstractGenerator {

    protected $layout = 'product';

    public function getDescription() {
        return sprintf(n2_('Creates slides from %1$s content.'), 'MijoShop');
    }

    public function renderFields($container) {
        parent::renderFields($container);

        $filterGroup = new ContainerTable($container, 'filter', n2_('Filter'));

        $source = $filterGroup->createRow('source-row');
        new MijoshopCategories($source, 'mijoshopsourcecategories', n2_('Category'), 0, array(
            'isMultiple' => true
        ));
        new MijoshopManufacturers($source, 'mijoshopsourcemanufacturers', n2_('Manufacturer'), 0, array(
            'isMultiple' => true
        ));


        $limit = $filterGroup->createRow('limit-row');
        new Filter($limit, 'mijoshopsourcespecial', 'Special', 0);
        new Filter($limit, 'mijoshopsourceinstock', n2_('In stock'), 0);
        new MijoshopLanguages($limit, 'mijoshopsourcelanguage', n2_('Language'), '');

        $orderGroup = new ContainerTable($container, 'order-group', n2_('Order'));
        $order      = $orderGroup->createRow('order-row');
        new GeneratorOrder($order, 'mijoshoporder', 'p.date_added|*|desc', array(
            'options' => array(
                ''                => n2_('None'),
                'pc.name'         => n2_('Product name'),
                'p.sort_order'    => n2_('Ordering'),
                'p.viewed'        => n2_('Viewed'),
                'p.price'         => n2_('Price'),
                'p.date_added'    => n2_('Creation time'),
                'p.date_modified' => n2_('Modification time')
            )
        ));
    }

    protected function _getData($count, $startIndex) {

        //Load Mijoshop config
        MijoShop::get('opencart')
                ->loadControllerFunction('startup/startup/index');
        $config   = MijoShop::get('opencart')
                            ->get('config');
        $currency = MijoShop::get('opencart')
                            ->get('currency');

        $router = MijoShop::get('router');

        $language_id = intval($this->data->get('mijoshopsourcelanguage'));
        if (!$language_id) $language_id = intval($config->get('config_language_id'));

        $tmpLng = $config->get('config_language_id');
        $config->set('config_language_id', $language_id);

        $tax    = MijoShop::get('opencart')
                          ->get('tax');
        $length = MijoShop::get('opencart')
                          ->get('length');
        $weight = MijoShop::get('opencart')
                          ->get('weight');

        $query = 'SELECT ';
        $query .= 'p.product_id ';

        $where = array(' p.status = 1 ');
        switch ($this->data->get('mijoshopsourcespecial', 0)) {
            case 0:
                $query .= ', ps.price AS special_price ';
                break;
            case 1:
                $query .= ', ps.price AS special_price ';

                $where[] = ' ps.price IS NOT NULL';
                $jNow    = Factory::getDate();
                $now     = $jNow->toSql();
                $where[] = ' (ps.date_start = "0000-00-00" OR ps.date_start < \'' . $now . '\')';
                $where[] = ' (ps.date_end = "0000-00-00" OR ps.date_end > \'' . $now . '\')';
                break;
            case -1:
                $jNow    = Factory::getDate();
                $now     = $jNow->toSql();
                $where[] = ' (ps.price IS NULL OR (ps.date_start > \'' . $now . '\' OR ps.date_end < \'' . $now . '\' AND ps.date_end <> "0000-00-00"))';
                break;
        }

        $query .= 'FROM #__mijoshop_product AS p ';

        $query .= 'LEFT JOIN #__mijoshop_product_description AS pc USING(product_id) ';
        $query .= 'LEFT JOIN #__mijoshop_product_to_category AS ptc USING(product_id) ';
        $query .= 'LEFT JOIN #__mijoshop_product_special AS ps USING(product_id) ';

        $categories = array_map('intval', explode('||', $this->data->get('mijoshopsourcecategories', '0')));

        if (!in_array(0, $categories) && count($categories) > 0) {
            $where[] = 'ptc.category_id IN (' . implode(',', $categories) . ') ';
        }

        $manufacturers = array_map('intval', explode('||', $this->data->get('mijoshopmanufacturers', '0')));

        if (!in_array(0, $manufacturers) && count($manufacturers) > 0) {
            $where[] = 'p.manufacturer_id IN (' . implode(',', $manufacturers) . ') ';
        }

        switch ($this->data->get('mijoshopsourceinstock', 0)) {
            case 1:
                $where[] = ' p.quantity > 0 ';
                break;
            case -1:
                $where[] = ' p.quantity = 0 ';
                break;
        }

        $where[] = ' pc.language_id  = ' . $language_id;

        if (count($where) > 0) {
            $query .= 'WHERE ' . implode(' AND ', $where) . ' ';
        }

        $query .= 'GROUP BY p.product_id ';

        $order = Common::parse($this->data->get('mijoshoporder', 'p.date_added|*|desc'));
        if ($order[0]) {
            $query .= 'ORDER BY ' . $order[0] . ' ' . $order[1] . ' ';
        }
        $query .= 'LIMIT ' . $startIndex . ', ' . $count;

        $result = Database::queryAll($query);

        $data = array();
        for ($i = 0; $i < count($result); $i++) {

            $pi = MijoShop::get('opencart')
                          ->loadModelFunction('catalog/product/getProduct', $result[$i]['product_id']);

            $r = array(
                'title'       => $pi['name'],
                'url'         => $router->route('index.php?option=com_mijoshop&route=product/product&product_id=' . $pi['product_id']),
                'description' => html_entity_decode($pi['description'])
            );
            if (!empty($pi['image'])) {
                $r['image'] = ResourceTranslator::urlToResource(Filesystem::pathToAbsoluteURL(DIR_IMAGE) . $pi['image']);
            } else {
                $r['image'] = ImageFallback::fallback(array(), array($r['description']));
            }

            $r += array(
                'thumbnail' => $r['image'],
                'price'     => $currency->format($tax->calculate($pi['price'], $pi['tax_class_id'], $config->get('config_tax')), $config->get('config_currency'))
            );
            if (!empty($result[$i]['special_price'])) {
                $r['special_price'] = $currency->format($tax->calculate($result[$i]['special_price'], $pi['tax_class_id'], $config->get('config_tax')), $config->get('config_currency'));
            }

            if ($config->get('config_tax')) {

                $r['price_without_tax'] = $currency->format(!empty($result[$i]['special_price']) ? $result[$i]['special_price'] : $pi['price'], $config->get('config_currency'));
            }

            $r      += array(
                'model'    => $pi['model'],
                'sku'      => $pi['sku'],
                'upc'      => $pi['upc'],
                'ean'      => $pi['ean'],
                'jan'      => $pi['jan'],
                'isbn'     => $pi['isbn'],
                'mpn'      => $pi['mpn'],
                'location' => $pi['location'],
                'weight'   => $weight->format($pi['weight'], $pi['weight_class_id']),
                'length'   => $length->format($pi['length'], $pi['length_class_id']),
                'width'    => $length->format($pi['width'], $pi['length_class_id']),
                'height'   => $length->format($pi['height'], $pi['length_class_id']),
                'tag'      => $pi['tag']
            );
            $data[] = $r;
        }

        $config->set('config_language_id', $tmpLng);

        return $data;
    }

}
