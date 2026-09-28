<?php

namespace Nextend\SmartSlider3Pro\Generator\Joomla\Eshop\Sources;

use EshopHelper;
use EshopRoute;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Multilanguage;
use Joomla\CMS\Router\Route;
use Nextend\Framework\Database\Database;
use Nextend\Framework\Form\Container\ContainerTable;
use Nextend\Framework\Form\Element\MixedField\GeneratorOrder;
use Nextend\Framework\Form\Element\OnOff;
use Nextend\Framework\Form\Element\Select\Filter;
use Nextend\Framework\Parser\Common;
use Nextend\SmartSlider3\Generator\AbstractGenerator;
use Nextend\SmartSlider3\Platform\Joomla\ImageFallback;
use Nextend\SmartSlider3Pro\Generator\Joomla\Eshop\Elements\EshopCategories;
use Nextend\SmartSlider3Pro\Generator\Joomla\Eshop\Elements\EshopCategoryLanguage;
use Nextend\SmartSlider3Pro\Generator\Joomla\Eshop\Elements\EshopCurrency;
use Nextend\SmartSlider3Pro\Generator\Joomla\Eshop\Elements\EshopManufacturerLanguage;
use Nextend\SmartSlider3Pro\Generator\Joomla\Eshop\Elements\EshopManufacturers;
use Nextend\SmartSlider3Pro\Generator\Joomla\Eshop\Elements\EshopProductLanguage;
use Nextend\SmartSlider3Pro\Generator\Joomla\Eshop\Elements\EshopTags;

class EshopProducts extends AbstractGenerator {

    protected $layout = 'product';

    var $leftSymbol;
    var $rightSymbol;
    var $decimalPlace;
    var $currentTime;
    var $exchangeValue;
    var $decimalPoint;
    var $thousandsSeparator;
    var $categoryTree;

    public function getDescription() {
        return sprintf(n2_('Creates slides from %1$s content.'), 'EShop');
    }

    public function renderFields($container) {
        parent::renderFields($container);

        $filterGroup = new ContainerTable($container, 'filter', n2_('Filter'));

        $source = $filterGroup->createRow('source-row');
        new EshopCategories($source, 'eshopsourcecategories', n2_('Categories'), 0, array(
            'isMultiple' => true
        ));
        new EshopManufacturers($source, 'eshopsourcemanufacturers', n2_('Manufacturer'), 0, array(
            'isMultiple' => true
        ));
        new EshopTags($source, 'eshopsourcetags', n2_('Tags'), 0, array(
            'isMultiple' => true
        ));

        $limit = $filterGroup->createRow('limit-row');
        new Filter($limit, 'eshopsourcefeatured', n2_('Featured'), 0);
        new Filter($limit, 'eshopsourcediscount', n2_('On discount'), 0);
        new Filter($limit, 'eshopsourceinstock', n2_('In stock'), 0);
        new OnOff($limit, 'eshopsourcesubcategory', n2_('Include subcategories'), 0);


        $language = $filterGroup->createRow('language');
        new EshopCurrency($language, 'eshopsourcecurrencycode', n2_('Currency'), 0);
        new EshopProductLanguage($language, 'eshopsourceproductlanguage', n2_('Product language'), 0);
        new EshopCategoryLanguage($language, 'eshopsourcecategorylanguage', n2_('Category language'), 0);
        new EshopManufacturerLanguage($language, 'eshopsourcemanufacturerlanguage', n2_('Manufacturer language'), 0);

        $orderGroup = new ContainerTable($container, 'order-group', n2_('Order'));
        $order      = $orderGroup->createRow('order-row');
        new GeneratorOrder($order, 'eshoporder', 'p.created_date|*|desc', array(
            'options' => array(
                ''                => n2_('None'),
                'p.product_price' => n2_('Price'),
                'pd.product_name' => n2_('Product name'),
                'p.ordering'      => n2_('Ordering'),
                'p.hits'          => n2_('Hits'),
                'p.created_date'  => n2_('Creation time'),
                'p.modified_date' => n2_('Modification time'),
                'p.id'            => n2_('Product ID')
            )
        ));
    }

    protected function resetState() {
        $this->leftSymbol         = '';
        $this->rightSymbol        = '';
        $this->decimalPlace       = '';
        $this->currentTime        = '';
        $this->exchangeValue      = '';
        $this->decimalPoint       = '';
        $this->thousandsSeparator = '';
        $this->categoryTree       = array();
    }

    function setCurrencyDetails($left, $right, $dec, $point, $thou, $now, $exchange) {
        $this->leftSymbol         = $left;
        $this->rightSymbol        = $right;
        $this->decimalPlace       = $dec;
        $this->decimalPoint       = $point;
        $this->thousandsSeparator = $thou;
        $this->currentTime        = $now;
        $this->exchangeValue      = $exchange;
    }

    function decimals($var) {
        if (!empty($this->decimalPlace)) {
            return number_format($var, $this->decimalPlace, $this->decimalPoint, $this->thousandsSeparator);
        } else {
            return round($var);
        }
    }

    function createPrice($product_price, $discount_price = null, $discount_date_start = null, $discount_date_end = null, $symbol = true) {
        if ($symbol) {
            $price = $this->leftSymbol;
        } else {
            $price = '';
        }
        if (!empty($discount_price)) {
            if (($discount_date_start == '0000-00-00 00:00:00' || $discount_date_start <= $this->currentTime) && ($discount_date_end == '0000-00-00 00:00:00' || $discount_date_end > $this->currentTime)) {
                $product_price = $discount_price;
            }
        }
        $product_price = $this->exchangeValue * $product_price;
        $price         .= $this->decimals($product_price);
        $price         .= $this->rightSymbol;

        return $price;
    }

    function buildCategoryTree($categoryID) {
        $categories = EshopHelper::getCategories($categoryID);
        if (!empty($categories)) {
            foreach ($categories as $cat) {
                $this->categoryTree[] = $cat->id;
                $this->buildCategoryTree($cat->id);
            }
        }
    }

    protected function _getData($count, $startIndex) {
        if (!class_exists('EShopHelper')) {
            require_once(JPATH_SITE . '/components/com_eshop/helpers/helper.php');
        }

        if (!class_exists('EShopCustomer')) {
            require_once(JPATH_SITE . '/components/com_eshop/helpers/customer.php');
        }

        if (!class_exists('EshopRoute')) {
            require_once JPATH_ROOT . '/components/com_eshop/helpers/' . ((version_compare(JVERSION, '3.0', 'ge') && Multilanguage::isEnabled() && count(EShopHelper::getLanguages()) > 1) ? 'routev3.php' : 'route.php');
        }

        $categories    = array_map('intval', explode('||', $this->data->get('eshopsourcecategories', '0')));
        $manufacturers = array_map('intval', explode('||', $this->data->get('eshopsourcemanufacturers', '0')));
        $tags          = array_map('intval', explode('||', $this->data->get('eshopsourcetags', '0')));

        if ($this->data->get('eshopsourcesubcategory', '0') == 1) {
            foreach ($categories as $cat) {
                $this->buildCategoryTree($cat);
            }
            $categories = $this->categoryTree;
        }

        $where = array('p.published = 1');
        if (!in_array(0, $categories) && count($categories) > 0) {
            $where[] = 'pc.category_id IN(' . implode(', ', $categories) . ') ';
        }
        if (!in_array(0, $manufacturers) && count($manufacturers) > 0) {
            $where[] = 'p.manufacturer_id IN(' . implode(', ', $manufacturers) . ') ';
        }
        if (!in_array(0, $tags) && count($tags) > 0) {
            $where[] = 'pt.tag_id IN(' . implode(', ', $tags) . ') ';
        }

        switch ($this->data->get('eshopsourcefeatured', 0)) {
            case 1:
                $where[] = 'p.product_featured = 1 ';
                break;
            case -1:
                $where[] = 'p.product_featured = 0 ';
                break;
        }

        $jNow = Factory::getDate();
        $now  = $jNow->toSql();
        switch ($this->data->get('eshopsourcediscount', 0)) {
            case 1:
                $where[] = "p.id IN (SELECT product_id FROM #__eshop_productdiscounts WHERE
        date_start = '0000-00-00 00:00:00' OR date_start IS NULL OR date_start <= '" . $now . "' AND date_end = '0000-00-00 00:00:00' OR date_end IS NULL OR date_end > '" . $now . "') ";
                break;
            case -1:
                $where[] = "p.id NOT IN (SELECT product_id FROM #__eshop_productdiscounts WHERE
        date_start = '0000-00-00 00:00:00' OR date_start IS NULL OR date_start <= '" . $now . "' AND date_end = '0000-00-00 00:00:00' OR date_end IS NULL OR date_end > '" . $now . "') ";
                break;
        }

        switch ($this->data->get('eshopsourceinstock', 0)) {
            case 1:
                $where[] = "p.product_quantity > 0";
                break;
            case -1:
                $where[] = "product_quantity = 0";
                break;
        }

        $prodLang = $this->data->get('eshopsourceproductlanguage', '');
        if (!empty($prodLang)) {
            $where[] = "pd.language = '" . $prodLang . "'";
        }

        $catLang = $this->data->get('eshopsourcecategorylanguage', '');
        if (!empty($catLang)) {
            $where[] = "cd.language = '" . $catLang . "'";
        }

        $manLang = $this->data->get('eshopsourcemanufacturerlanguage', '');
        if (!empty($manLang)) {
            $where[] = "md.language = '" . $manLang . "'";
        }

        $currencyCode = $this->data->get('eshopsourcecurrencycode', '');
        if (!empty($currencyCode)) {
            $where[] = "cu.currency_code = '" . $currencyCode . "'";
        } else {
            $where[] = "cu.currency_code = (SELECT config_value FROM #__eshop_configs WHERE config_key = 'default_currency_code' LIMIT 1)";
        }

        $query = "SELECT *, cow.config_value AS image_thumb_width, coh.config_value AS image_thumb_height, p.id AS id
                  FROM #__eshop_products AS p
                  LEFT JOIN #__eshop_productcategories AS pc ON p.id = pc.product_id
                  LEFT JOIN #__eshop_productdetails AS pd ON p.id = pd.product_id
                  LEFT JOIN #__eshop_productimages AS pi ON p.id = pi.product_id
                  LEFT JOIN #__eshop_productdiscounts AS pdi ON p.id = pdi.product_id
                  LEFT JOIN #__eshop_producttags as pt ON p.id = pt.product_id
                  LEFT JOIN #__eshop_categories as c ON c.id = pc.category_id
                  LEFT JOIN #__eshop_categorydetails as cd ON cd.category_id = pc.category_id
                  LEFT JOIN #__eshop_manufacturers as m ON p.manufacturer_id = m.id
                  LEFT JOIN #__eshop_manufacturerdetails AS md ON p.manufacturer_id = md.manufacturer_id
                  CROSS JOIN #__eshop_currencies AS cu
                  CROSS JOIN #__eshop_configs AS cow
                  CROSS JOIN #__eshop_configs AS coh
                  WHERE cow.config_key = 'image_thumb_width' AND coh.config_key = 'image_thumb_height' AND " . implode(' AND ', $where) . " GROUP BY p.id ";

        $order = Common::parse($this->data->get('eshoporder', 'p.created_date|*|desc'));
        if ($order[0]) {
            $query .= 'ORDER BY ' . $order[0] . ' ' . $order[1] . ' ';
        }

        $query .= 'LIMIT ' . $startIndex . ', ' . $count . ' ';

        $result = Database::queryAll($query);

        $query = 'SELECT tax_rate FROM #__eshop_taxes';
        $taxes = Database::queryAll($query);

        $data = array();
        foreach ($result as $res) {
            $this->setCurrencyDetails($res['left_symbol'], $res['right_symbol'], $res['decimal_place'], $res['decimal_symbol'], $res['thousands_separator'], $now, $res['exchanged_value']);
            $r = array(
                'title'             => $res['product_name'],
                'url'               => Route::_(EshopRoute::getProductRoute($res['id'], $res['category_id'])),
                'description'       => $res['product_desc'],
                'short_description' => $res['product_short_desc']
            );

            $r['image'] = ImageFallback::fallback(array(
                !empty($res['product_image']) ? $res['product_image'] : '',
                !empty($res['product_image']) ? 'media/com_eshop/products/' . $res['product_image'] : ''
            ), array($res['product_desc']));

            if (!empty($res['product_image'])) {
                $reSized = explode('.', $res['product_image']);
                if (count($reSized) == 2) {
                    $oldEshopImageUrl = '/media/com_eshop/products/resized/' . $reSized[0] . '-' . $res['image_thumb_width'] . 'x' . $res['image_thumb_height'] . '.' . $reSized[1];

                    $reSizedParts = explode('/', $reSized[0]);
                    if (count($reSizedParts) > 1) {
                        $imageName = end($reSizedParts);
                        array_pop($reSizedParts);
                        $reSizedParts     = implode('/', $reSizedParts);
                        $newEshopImageUrl = $reSizedParts . '/resized/' . $imageName . '-' . $res['image_thumb_width'] . 'x' . $res['image_thumb_height'] . '.' . $reSized[1];
                    }

                    $r['thumbnail'] = ImageFallback::fallback(array(
                        $newEshopImageUrl,
                        $oldEshopImageUrl
                    ));
                }
            }

            if (empty($r['thumbnail'])) {
                $r['thumbnail'] = $r['image'];
            }

            if (!empty($res['category_image'])) {
                $categoryImage = ImageFallback::fallback(array(
                    $res['category_image'],
                    'media/com_eshop/categories/' . $res['category_image']
                ));
            } else {
                $categoryImage = '';
            }

            if (!empty($res['manufacturer_image'])) {
                $manufacturerImage = ImageFallback::fallback(array(
                    $res['manufacturer_image'],
                    'media/com_eshop/manufacturers/' . $res['manufacturer_image']
                ));
            } else {
                $manufacturerImage = '';
            }

            $r += array(
                'price'                                  => $this->createPrice($res['product_price']),
                'price_without_currency_symbol'          => $this->createPrice($res['product_price'], null, null, null, false),
                'discount_price'                         => $this->createPrice($res['price']),
                'discount_price_without_currency_symbol' => $this->createPrice($res['price'], null, null, null, false),
                'id'                                     => $res['id'],
                'product_sku'                            => $res['product_sku'],
                'product_weight'                         => $this->decimals($res['product_weight']),
                'product_length'                         => $this->decimals($res['product_length']),
                'product_width'                          => $this->decimals($res['product_width']),
                'product_height'                         => $this->decimals($res['product_height']),
                'product_shipping_cost'                  => $this->createPrice($res['product_shipping_cost']),
                'hits'                                   => $res['hits'],
                'product_page_title'                     => $res['product_page_title'],
                'product_page_heading'                   => $res['product_page_heading'],
                'tab1_title'                             => $res['tab1_title'],
                'tab1_content'                           => $res['tab1_content'],
                'tab2_title'                             => $res['tab2_title'],
                'tab2_content'                           => $res['tab2_content'],
                'tab3_title'                             => $res['tab3_title'],
                'tab3_content'                           => $res['tab3_content'],
                'tab4_title'                             => $res['tab4_title'],
                'tab4_content'                           => $res['tab4_content'],
                'tab5_title'                             => $res['tab5_title'],
                'tab5_content'                           => $res['tab5_content'],
                'category_name'                          => $res['category_name'],
                'category_desc'                          => $res['category_desc'],
                'category_image'                         => $categoryImage,
                'category_url'                           => Route::_(EshopRoute::getCategoryRoute($res['category_id'])),
                'manufacturer_email'                     => $res['manufacturer_email'],
                'manufacturer_url'                       => $res['manufacturer_url'],
                'manufacturer_site_url'                  => Route::_(EshopRoute::getManufacturerRoute($res['manufacturer_id'])),
                'manufacturer_image'                     => $manufacturerImage,
                'manufacturer_name'                      => $res['manufacturer_name'],
                'manufacturer_desc'                      => $res['manufacturer_desc'],
                'manufacturer_page_title'                => $res['manufacturer_page_title'],
                'manufacturer_page_heading'              => $res['manufacturer_page_heading']
            );

            $r['full_price'] = $r['price'];

            $j = 1;
            foreach ($taxes as $tax) {
                $r['price_with_tax' . $j] = $this->createPrice($res['product_price'] + $res['product_price'] * $tax['tax_rate'] / 100);
                $j++;
            }

            $k     = 1;
            $query = 'SELECT tag_name FROM #__eshop_producttags as pt LEFT JOIN #__eshop_tags AS t ON pt.tag_id = t.id WHERE product_id = ' . $res['id'] . ';';
            $tags  = Database::queryAll($query);
            foreach ($tags as $tag) {
                $r['tag_name' . $k] = $tag['tag_name'];
                $k++;
            }

            $data[] = $r;
        }

        return $data;
    }

}