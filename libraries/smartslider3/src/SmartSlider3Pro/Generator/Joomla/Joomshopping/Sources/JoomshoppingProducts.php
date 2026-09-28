<?php

namespace Nextend\SmartSlider3Pro\Generator\Joomla\Joomshopping\Sources;

use Joomla\CMS\Factory;
use JSFactory;
use JTable;
use Nextend\Framework\Database\Database;
use Nextend\Framework\Form\Container\ContainerTable;
use Nextend\Framework\Form\Element\MixedField\GeneratorOrder;
use Nextend\Framework\Form\Element\OnOff;
use Nextend\Framework\Form\Element\Select\Filter;
use Nextend\Framework\Form\Element\Text;
use Nextend\Framework\Form\Joomla\Element\Select\MenuItems;
use Nextend\Framework\Notification\Notification;
use Nextend\Framework\Parser\Common;
use Nextend\Framework\ResourceTranslator\ResourceTranslator;
use Nextend\SmartSlider3\Generator\AbstractGenerator;
use Nextend\SmartSlider3\Platform\Joomla\ImageFallback;
use Nextend\SmartSlider3Pro\Generator\Joomla\Joomshopping\Elements\JoomshoppingCategories;
use Nextend\SmartSlider3Pro\Generator\Joomla\Joomshopping\Elements\JoomshoppingLabels;
use Nextend\SmartSlider3Pro\Generator\Joomla\Joomshopping\Elements\JoomshoppingManufacturers;


class JoomshoppingProducts extends AbstractGenerator {

    protected $layout = 'product';

    public function getDescription() {
        return sprintf(n2_('Creates slides from %1$s content.'), 'JoomShopping');
    }

    public function renderFields($container) {
        parent::renderFields($container);

        $filterGroup = new ContainerTable($container, 'filter', n2_('Filter'));

        $source = $filterGroup->createRow('source-row');
        new JoomshoppingCategories($source, 'sourcecategories', n2_('Category'), 0, array(
            'isMultiple' => true
        ));
        new JoomshoppingManufacturers($source, 'sourcemanufacturers', n2_('Manufacturer'), 0, array(
            'isMultiple' => true
        ));


        $limit = $filterGroup->createRow('limit-row');
        new Filter($limit, 'sourceinstock', n2_('In stock'), 0);
        new JoomshoppingLabels($limit, 'sourcelabel', n2_('Label'), -1);

        new MenuItems($limit, 'itemid', n2_('Menu item (item ID)'), 0);
        new Text($limit, 'language', n2_('Language'), '', array(
            'tipLabel'       => n2_('Language'),
            'tipDescription' => 'en-GB,de-DE,hu-HU,...',
            'tipLink'        => 'https://smartslider.helpscoutdocs.com/article/1882-joomla-joomshopping-generator#language',
        ));

        new OnOff($limit, 'allimage', n2_('Ask down all product images'), 0);

        $orderGroup = new ContainerTable($container, 'order-group', n2_('Order'));
        $order      = $orderGroup->createRow('order-row');
        new GeneratorOrder($order, 'productsorder', 'pr.product_date_added|*|desc', array(
            'options' => array(
                ''                        => n2_('None'),
                'pr.name'                 => n2_('Product name'),
                'category_name'           => n2_('Category'),
                'pr_cat.product_ordering' => n2_('Ordering'),
                'pr.hits'                 => n2_('Hits'),
                'pr.product_date_added'   => n2_('Creation time'),
                'pr.date_modify'          => n2_('Modification time')
            )
        ));
    }

    protected function _getData($count, $startIndex) {

        require_once(JPATH_SITE . "/components/com_jshopping/lib/factory.php");

        $jShopConfig = JSFactory::getConfig();
        $langObject  = JSFactory::getLang();
        $language    = $this->data->get('language', '');
        $customLang  = !empty($language);
        if ($customLang) {
            $checkLanguage = Database::queryRow("SELECT * FROM #__jshopping_languages WHERE language = '" . $language . "'");
            if (empty($checkLanguage)) {
                Notification::error('Wrong language code is used in the generator settings!');

                return null;
            }
        }
        $session = Factory::getSession();

        $where = array(' pr.product_publish = 1 ');

        $category = array_map('intval', explode('||', $this->data->get('sourcecategories', '')));
        if (!in_array(0, $category) && count($category) > 0) {
            $where[] = 'pr_cat.category_id IN (' . implode(',', $category) . ') ';
        }

        $manufacturers = array_map('intval', explode('||', $this->data->get('sourcemanufacturers', '')));
        if (!in_array(0, $manufacturers) && count($manufacturers) > 0) {
            $where[] = 'pr.product_manufacturer_id IN (' . implode(',', $manufacturers) . ') ';
        }

        switch ($this->data->get('sourceinstock', 0)) {
            case 1:
                $where[] = ' (pr.product_quantity > 0 OR pr.unlimited = 1) ';
                break;
            case -1:
                $where[] = ' (pr.product_quantity = 0 AND pr.unlimited = 0) ';
                break;
        }

        $label_id = intval($this->data->get('sourcelabel', -1));

        if ($label_id != -1) {
            $where[] = ' pr.label_id = "' . $label_id . '" ';
        }

        $o     = '';
        $order = Common::parse($this->data->get('productsorder', 'pr.product_date_added|*|desc'));
        if ($order[0]) {
            if ($order[0] == 'pr.name') $order[0] = 'pr.`' . $langObject->get('name') . '`';
            $o .= 'ORDER BY ' . $order[0] . ' ' . $order[1] . ' ';
        }

        $query = "SELECT 
                        pr.product_id, 
                        pr.product_publish, 
                        pr_cat.product_ordering, ";

        if ($customLang) {
            $query .= " pr.`name_" . $language . "` as name,
                        pr.`short_description_" . $language . "` as short_description,
                        pr.`description_" . $language . "` as description,
                        man.`name_" . $language . "` as man_name,";
        } else {
            $query .= " pr.`" . $langObject->get('name') . "` as name,
                        pr.`" . $langObject->get('short_description') . "` as short_description,
                        pr.`" . $langObject->get('description') . "` as description,
                        man.`" . $langObject->get('name') . "` as man_name,";
        }

        $query .= "     pr.product_ean as ean,
                        pr.product_quantity as qty,
                        pr.image as image,
                        pr.product_price,
                        pr.currency_id,
                        pr.hits,
                        pr.unlimited,
                        pr.product_date_added,
                        pr.label_id,
                        pr.vendor_id,
                        V.f_name as v_f_name,
                        V.l_name as v_l_name,
                        cat.category_image,
                        cat.category_id,";

        if ($customLang) {
            $query .= " cat.`name_" . $language . "` as category_name,
                        cat.`alias_" . $language . "` as category_alias,
                        cat.`short_description_" . $language . "` as category_short_description,
                        cat.`description_" . $language . "` as category_description";
        } else {
            $query .= " cat.`" . $langObject->get('name') . "` as category_name,
                        cat.`" . $langObject->get('alias') . "` as category_alias,
                        cat.`" . $langObject->get('short_description') . "` as category_short_description,
                        cat.`" . $langObject->get('description') . "` as category_description";
        }

        $query .= " FROM `#__jshopping_products` AS pr
                    LEFT JOIN `#__jshopping_products_to_categories` AS pr_cat USING (product_id)
                    LEFT JOIN `#__jshopping_categories` AS cat USING (category_id)
                    LEFT JOIN `#__jshopping_manufacturers` AS man ON pr.product_manufacturer_id=man.manufacturer_id
                    LEFT JOIN `#__jshopping_vendors` as V on pr.vendor_id=V.id
                    WHERE pr.parent_id=0 " . (count($where) ? ' AND ' . implode(' AND ', $where) : '') . " GROUP BY pr.product_id " . $o . " LIMIT " . $startIndex . ", " . $count;

        $result = Database::queryAll($query);

        $data = array();

        $itemID = $this->data->get('itemid', '0');

        $allImage = $this->data->get('allimage', 0);

        for ($i = 0; $i < count($result); $i++) {
            $product = JTable::getInstance('product', 'jshop');
            $product->load($result[$i]['product_id']);

            $jinput = Factory::getApplication()->input;
            $attr   = $jinput->get('attr', null, null);

            $back_value = $session->get('product_back_value');
            if (!isset($back_value['pid'])) $back_value = array(
                'pid'  => null,
                'attr' => null,
                'qty'  => null
            );
            if ($back_value['pid'] != $result[$i]['product_id']) $back_value = array(
                'pid'  => null,
                'attr' => null,
                'qty'  => null
            );
            if (!is_array($back_value['attr'])) $back_value['attr'] = array();
            if (count($back_value['attr']) == 0 && is_array($attr)) $back_value['attr'] = $attr;
            $attributesDatas = $product->getAttributesDatas($back_value['attr']);
            $product->setAttributeActive($attributesDatas['attributeActive']);

            getDisplayPriceForProduct($product->product_price);
            $product->getExtendsData();

            $r = array(
                'title'             => $result[$i]['name'],
                'url'               => SEFLink('index.php?option=com_jshopping&controller=product&task=view&product_id=' . $result[$i]['product_id'] . '&category_id=' . $result[$i]['category_id']),
                'joomla_url'        => 'index.php?option=com_jshopping&controller=product&task=view&product_id=' . $result[$i]['product_id'] . '&category_id=' . $result[$i]['category_id'] . '&Itemid=' . $itemID,
                'description'       => $result[$i]['description'],
                'short_description' => $result[$i]['short_description']
            );

            $op = $product->getOldPrice();

            if ($result[$i]['image'] != null) {
                $r += array(
                    'image'      => ResourceTranslator::urlToResource($jShopConfig->image_product_live_path . '/' . $result[$i]['image']),
                    'thumbnail'  => ResourceTranslator::urlToResource($jShopConfig->image_product_live_path . '/thumb_' . $result[$i]['image']),
                    'image_full' => ResourceTranslator::urlToResource($jShopConfig->image_product_live_path . '/full_' . $result[$i]['image'])
                );
            } else {
                $image      = ImageFallback::findImage($r['description']);
                $r['image'] = $r['thumbnail'] = ImageFallback::fallback(array($image));
            }

            $r += array(
                'price'                      => formatprice($product->getPriceCalculate()),
                'product_old_price'          => $op > 0 ? formatprice($op) : '',
                'category_name'              => $result[$i]['category_name'],
                'category_short_description' => $result[$i]['category_short_description'],
                'category_description'       => $result[$i]['category_description'],
                'category_url'               => SEFLink('index.php?option=com_jshopping&controller=category&task=view&category_id=' . $result[$i]['category_id']),
                'add_to_cart_url'            => SEFLink('index.php?option=com_jshopping&controller=cart&task=add&quantity=1&to=cart&product_id=' . $result[$i]['product_id'] . '&category_id=' . $result[$i]['category_id']),
                'manufacturer_name'          => $result[$i]['man_name'],
                'product_id'                 => $result[$i]['product_id']
            );

            if ($allImage) {
                $imageQuery = 'SELECT image_name FROM #__jshopping_products_images WHERE product_id = ' . $result[$i]['product_id'] . ' ORDER BY ordering asc';
                $images     = Database::queryAll($imageQuery);
                for ($j = 0; $j < count($images); $j++) {
                    $r += array(
                        'image' . ($j + 1)      => ImageFallback::fallback(array($images[$j]['image_name']), array(), $jShopConfig->image_product_live_path),
                        'thumbnail' . ($j + 1)  => ImageFallback::fallback(array('thumb_' . $images[$j]['image_name']), array(), $jShopConfig->image_product_live_path),
                        'image_full' . ($j + 1) => ImageFallback::fallback(array('full_' . $images[$j]['image_name']), array(), $jShopConfig->image_product_live_path)
                    );
                }
            }

            $data[] = $r;
        }

        return $data;
    }
}