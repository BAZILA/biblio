<?php

namespace Nextend\SmartSlider3Pro\Generator\Joomla\Virtuemart\Sources;

use CurrencyDisplay;
use Joomla\CMS\Factory;
use Nextend\Framework\Database\Database;
use Nextend\Framework\Form\Container\ContainerTable;
use Nextend\Framework\Form\Element\MixedField\GeneratorOrder;
use Nextend\Framework\Form\Element\OnOff;
use Nextend\Framework\Form\Element\Select\Filter;
use Nextend\Framework\Form\Element\Select;
use Nextend\Framework\Form\Element\Text;
use Nextend\Framework\Form\Element\Textarea;
use Nextend\Framework\Parser\Common;
use Nextend\SmartSlider3\Generator\AbstractGenerator;
use Nextend\SmartSlider3\Platform\Joomla\ImageFallback;
use Nextend\SmartSlider3Pro\Generator\Joomla\Virtuemart\Elements\VirtuemartCategories;
use Nextend\SmartSlider3Pro\Generator\Joomla\Virtuemart\Elements\VirtuemartManufacturers;
use Nextend\SmartSlider3Pro\Generator\Joomla\Virtuemart\Elements\VirtuemartLanguages;
use VirtueMartModelProduct;
use VmConfig;

class VirtuemartProducts extends AbstractGenerator {

    protected $layout = 'product', $media_product_path, $media_product_path_resized, $resized_extensions = array(), $extensions = array(
        '.jpg',
        '.jpeg',
        '.png',
        '.svg',
        '.gif',
        '.webp',
        '.JPG',
        '.JPEG',
        '.PNG',
        '.SVG',
        '.GIF',
        '.WEBP'
    );

    public function getDescription() {
        return sprintf(n2_('Creates slides from %1$s content.'), 'VirtueMart');
    }

    public function renderFields($container) {
        parent::renderFields($container);

        $filterGroup = new ContainerTable($container, 'filter', n2_('Filter'));

        $source = $filterGroup->createRow('source-row');
        new VirtuemartCategories($source, 'virtuemartcategories', n2_('Category'), 0, array(
            'isMultiple' => true
        ));
        new VirtuemartManufacturers($source, 'virtuemartmanufacturers', n2_('Manufacturer'), 0, array(
            'isMultiple' => true
        ));


        $limit = $filterGroup->createRow('limit-row');
        new Filter($limit, 'virtuemartfeatured', n2_('Featured'), 0);
        new Filter($limit, 'virtuemartinstock', n2_('In stock'), 0);
        new VirtuemartLanguages($limit, 'virtuemartlanguage', n2_('Language'), 0);
        new VirtuemartLanguages($limit, 'fallbacklanguage', n2_('Fallback language'), '');
        new Select($limit, 'noimage', n2_('Products without images'), '0', array(
            'options' => array(
                '0' => 'Exclude them',
                '1' => 'Include them'
            )
        ));
        new OnOff($limit, 'virtuemartparentonly', n2_('Show parent products only'), 0);

        $date = $filterGroup->createRow('date-row');
        new Text($date, 'dateformat', n2_('Date format'), 'Y-m-d');
        new Textarea($date, 'translatedate', n2_('Translate date'), 'January->January||February->February||March->March', array(
            'width'  => 300,
            'height' => 100
        ));

        $orderGroup = new ContainerTable($container, 'order-group', n2_('Order'));
        $order      = $orderGroup->createRow('order-row');
        new GeneratorOrder($order, 'virtuemartproductsorder', 'prod.created_on|*|desc', array(
            'options' => array(
                ''                      => n2_('None'),
                'prod_ext.product_name' => n2_('Product name'),
                'cat.category_name'     => n2_('Category'),
                'prod.product_special'  => n2_('Special'),
                'cat_x.ordering'        => n2_('Ordering'),
                'prod.hits'             => n2_('Hits'),
                'prod.created_on'       => n2_('Creation time'),
                'prod.modified_on'      => n2_('Modification time'),
                'rand()'                => n2_('Random')
            )
        ));
    }

    protected function _getData($count, $startIndex) {

        require_once(JPATH_ADMINISTRATOR . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_virtuemart' . DIRECTORY_SEPARATOR . 'helpers' . DIRECTORY_SEPARATOR . 'config.php');
        VmConfig::loadConfig();

        $language = $this->data->get('virtuemartlanguage', 0);
        if (!$language) $language = VMLANG;

        $fallbackLanguage = $this->data->get('fallbacklanguage', '');

        $categories    = array_map('intval', explode('||', $this->data->get('virtuemartcategories', '')));
        $manufacturers = array_map('intval', explode('||', $this->data->get('virtuemartmanufacturers', '')));

        $query = 'SELECT ';
        $query .= 'prod.virtuemart_product_id AS id, ';
        $query .= 'prod.product_sku AS sku, ';
        $query .= 'prod_ext.product_name AS name, ';
        $query .= 'prod_ext.product_s_desc AS short_description, ';
        $query .= 'prod_ext.product_desc AS description, ';
        $query .= 'prod_ext.slug AS slug, ';
        $query .= 'prod.product_available_date AS product_available_date, ';
        $query .= 'prod.product_parent_id AS product_parent_id, ';

        $query .= 'cat.virtuemart_category_id AS category_id, ';
        $query .= 'cat.category_name AS category_name, ';
        $query .= 'cat.category_description AS category_description, ';
        $query .= 'cat.slug AS category_slug, ';

        $query .= 'man.virtuemart_manufacturer_id AS manufacturer_id, ';
        $query .= 'man.mf_name AS manufacturer_name, ';
        $query .= 'man.mf_email AS manufacturer_email, ';
        $query .= 'man.mf_desc AS manufacturer_description, ';
        $query .= 'man.mf_url AS manufacturer_url, ';
        $query .= 'man.slug AS manufacturer_slug, ';

        if (!empty($fallbackLanguage)) {
            $query .= 'prod_ext_fb.product_name AS title_fb, ';
            $query .= 'prod_ext_fb.product_s_desc AS short_description_fb, ';
            $query .= 'prod_ext_fb.product_desc AS description_fb, ';
            $query .= 'prod_ext_fb.slug AS slug_fb, ';

            $query .= 'cat_fb.virtuemart_category_id AS category_id_fb, ';
            $query .= 'cat_fb.category_name AS category_name_fb, ';
            $query .= 'cat_fb.category_description AS category_description_fb, ';
            $query .= 'cat_fb.slug AS category_slug_fb, ';

            $query .= 'man_fb.virtuemart_manufacturer_id AS manufacturer_id_fb, ';
            $query .= 'man_fb.mf_name AS manufacturer_name_fb, ';
            $query .= 'man_fb.mf_email AS manufacturer_email_fb, ';
            $query .= 'man_fb.mf_desc AS manufacturer_description_fb, ';
            $query .= 'man_fb.mf_url AS manufacturer_url_fb, ';
            $query .= 'man_fb.slug AS manufacturer_slug_fb, ';
        }

        $query .= 'med.file_url AS image, ';
        $query .= 'med.file_url_thumb AS thumbnail ';

        $query .= 'FROM #__virtuemart_products AS prod ';

        $query .= 'LEFT JOIN #__virtuemart_products_' . $language . ' AS prod_ext ON prod.virtuemart_product_id = prod_ext.virtuemart_product_id ';

        $query .= 'LEFT JOIN #__virtuemart_product_categories AS cat_x ON cat_x.virtuemart_product_id = prod.virtuemart_product_id ';

        $query .= 'LEFT JOIN #__virtuemart_categories_' . $language . ' AS cat ON cat_x.virtuemart_category_id = cat.virtuemart_category_id ';

        $query .= 'LEFT JOIN #__virtuemart_product_manufacturers AS man_x ON man_x.virtuemart_product_id = prod.virtuemart_product_id ';

        $query .= 'LEFT JOIN #__virtuemart_manufacturers_' . $language . ' AS man ON man_x.virtuemart_manufacturer_id = man.virtuemart_manufacturer_id ';

        $query .= 'LEFT JOIN #__virtuemart_product_medias AS med_x ON med_x.id = (SELECT id FROM #__virtuemart_product_medias AS med_x2 WHERE med_x2.virtuemart_product_id = prod.virtuemart_product_id ORDER BY ordering ASC LIMIT 1) ';

        $query .= 'LEFT JOIN #__virtuemart_medias AS med ON med_x.virtuemart_media_id = med.virtuemart_media_id ';

        if (!empty($fallbackLanguage)) {
            $query .= 'LEFT JOIN #__virtuemart_products_' . $fallbackLanguage . ' AS prod_ext_fb ON prod.virtuemart_product_id = prod_ext_fb.virtuemart_product_id ';

            $query .= 'LEFT JOIN #__virtuemart_categories_' . $fallbackLanguage . ' AS cat_fb ON cat_x.virtuemart_category_id = cat_fb.virtuemart_category_id ';

            $query .= 'LEFT JOIN #__virtuemart_manufacturers_' . $fallbackLanguage . ' AS man_fb ON man_x.virtuemart_manufacturer_id = man_fb.virtuemart_manufacturer_id ';
        }

        $where = array(
            'prod.published = 1',
        );

        if (!$this->data->get('noimage', '0')) {
            $where[] = 'med.file_is_downloadable = 0';
            $where[] = 'med.file_is_forSale = 0';
        } else {
            $where[] = '(med.virtuemart_media_id IS NULL OR (med.file_is_downloadable = 0 AND med.file_is_forSale = 0))';
        }

        if (!in_array(0, $categories) && count($categories) > 0) {
            $where[] = 'cat_x.virtuemart_category_id IN (' . implode(',', $categories) . ')';
        }

        if (!in_array(0, $manufacturers) && count($manufacturers) > 0) {
            $where[] = 'man.virtuemart_manufacturer_id IN (' . implode(',', $manufacturers) . ')';
        }

        switch ($this->data->get('virtuemartfeatured', 0)) {
            case 1:
                $where[] = 'prod.product_special = 1';
                break;
            case -1:
                $where[] = 'prod.product_special = 0';
                break;
        }

        switch ($this->data->get('virtuemartinstock', 0)) {
            case 1:
                $where[] = 'prod.product_in_stock > 0';
                break;
            case -1:
                $where[] = 'prod.product_in_stock = 0';
                break;
        }

        if ($this->data->get('virtuemartparentonly', 0)) {
            $where[] = 'prod.virtuemart_product_id IN (SELECT product_parent_id FROM #__virtuemart_products)';
        }

        $query .= 'WHERE ' . implode(' AND ', $where) . ' GROUP BY prod.virtuemart_product_id ';

        $order = Common::parse($this->data->get('virtuemartproductsorder', 'prod.created_on|*|desc'));
        if ($order[0]) {
            $query .= 'ORDER BY ' . $order[0] . ' ' . $order[1] . ' ';
        }

        $query .= 'LIMIT ' . $startIndex . ', ' . $count;

        $result = Database::queryAll($query);
        require_once(JPATH_ADMINISTRATOR . DS . 'components' . DS . 'com_virtuemart' . DS . 'helpers' . DS . 'currencydisplay.php');
        if (!class_exists('VirtueMartModelProduct')) {
            require_once(JPATH_ADMINISTRATOR . DS . 'components' . DS . 'com_virtuemart' . DS . 'models' . DS . 'product.php');
        }
        $currency = CurrencyDisplay::getInstance();

        $data = array();
        $ids  = array();

        $this->media_product_path         = VmConfig::get('media_product_path');
        $this->media_product_path_resized = $this->media_product_path . 'resized/';

        $thumbnail_width  = str_replace('px', '', VmConfig::get('img_width'));
        $thumbnail_height = str_replace('px', '', VmConfig::get('img_height'));
        foreach ($this->extensions as $extension) {
            $this->resized_extensions[] = '_' . $thumbnail_width . 'x' . $thumbnail_height . $extension;
        }

        $this->processTranslateField();
        $dateFormat = $this->data->get('dateformat', 'Y-m-d');

        if (is_array($result)) {
            for ($i = 0; $i < count($result); $i++) {
                $productModel = new VirtueMartModelProduct();
                $p            = $productModel->getProduct($result[$i]['id'], TRUE, TRUE, TRUE, 1, 0);
                $ids[]        = $result[$i]['id'];
                $ids[]        = $result[$i]['product_parent_id'];

                $url = 'index.php?option=com_virtuemart&view=productdetails&virtuemart_product_id=' . $result[$i]['id'];
                if (!empty($p->categoryItem[0]['virtuemart_category_id']) && $p->categoryItem[0]['virtuemart_category_id'] != 0) {
                    $url .= '&virtuemart_category_id=' . $p->categoryItem[0]['virtuemart_category_id'];
                }

                $r = array(
                    'title'       => $result[$i]['name'],
                    'url'         => $url,
                    'description' => $result[$i]['description']
                );

                $r['image'] = ImageFallback::fallback(array(
                    $result[$i]['image'] == 'images/stories/virtuemart/product/cart_logo.jpg' ? '' : $result[$i]['image']
                ), array(
                    $result[$i]['description'],
                    $result[$i]['short_description']
                ));

                $r['thumbnail'] = ImageFallback::fallback(array(
                    $result[$i]['thumbnail'],
                    $this->thumbnail($result[$i]['image']),
                    $r['image']
                ));

                $r += array(
                    'price'                        => $currency->createPriceDiv('costPrice', '', $p->prices, true),
                    'short_description'            => $result[$i]['short_description'],
                    'category_name'                => $result[$i]['category_name'],
                    'category_description'         => $result[$i]['category_description'],
                    'category_url'                 => !empty($result[$i]['category_id']) ? 'index.php?option=com_virtuemart&view=category&virtuemart_category_id=' . $result[$i]['category_id'] : '',
                    'manufacturer_name'            => $result[$i]['manufacturer_name'],
                    'manufacturer_description'     => $result[$i]['manufacturer_description'],
                    'manufacturer_email'           => $result[$i]['manufacturer_email'],
                    'manufacturer_url'             => $result[$i]['manufacturer_url'],
                    'base_price'                   => $currency->createPriceDiv('basePrice', '', $p->prices, true),
                    'base_price_variant'           => $currency->createPriceDiv('basePriceVariant', '', $p->prices, true),
                    'base_price_with_tax'          => $currency->createPriceDiv('basePriceWithTax', '', $p->prices, true),
                    'discounted_price_without_tax' => $currency->createPriceDiv('discountedPriceWithoutTax', '', $p->prices, true),
                    'price_before_tax'             => $currency->createPriceDiv('priceBeforeTax', '', $p->prices, true),
                    'sales_price'                  => $currency->createPriceDiv('salesPrice', '', $p->prices, true),
                    'tax_amount'                   => $currency->createPriceDiv('taxAmount', '', $p->prices, true),
                    'sales_price_with_discount'    => $currency->createPriceDiv('salesPriceWithDiscount', '', $p->prices, true),
                    'sales_price_temp'             => $currency->createPriceDiv('salesPriceTemp', '', $p->prices, true),
                    'unit_price'                   => $currency->createPriceDiv('unitPrice', '', $p->prices, true),
                    'price_without_tax'            => $currency->createPriceDiv('priceWithoutTax', '', $p->prices, true),
                    'discount_amount'              => $currency->createPriceDiv('discountAmount', '', $p->prices, true),
                    'sku'                          => $result[$i]['sku'],
                    'id'                           => $result[$i]['id'],
                    'category_id'                  => $result[$i]['category_id'],
                    'manufacturer_id'              => $result[$i]['manufacturer_id'],
                    'parent_id'                    => $result[$i]['product_parent_id'],
                    'product_available_date'       => $this->translate($this->datify($result[$i]['product_available_date'], $dateFormat))
                );

                if (!empty($fallbackLanguage)) {
                    foreach ($r as $key => $value) {
                        if ($value === '' || $value === null) {
                            if (!empty($result[$i][$key . '_fb'])) {
                                $r[$key] = $result[$i][$key . '_fb'];
                            }
                        }
                    }
                }

                $data[] = $r;
            }
        }

        if (!empty($ids)) {
            $query = 'SELECT vm.file_url, vm.file_url_thumb, vpm.virtuemart_product_id AS id
                        FROM #__virtuemart_medias AS vm 
                        LEFT JOIN #__virtuemart_product_medias AS vpm  
                        ON vm.virtuemart_media_id = vpm.virtuemart_media_id  
                        WHERE vm.virtuemart_media_id IN 
                            (SELECT virtuemart_media_id FROM #__virtuemart_product_medias WHERE virtuemart_product_id IN (' . implode(',', $ids) . ')) 
							AND vm.file_is_forSale = 0 
							AND vm.file_is_downloadable = 0 
                            ORDER BY vpm.ordering';

            $images = Database::queryAll($query);
            for ($i = 0; $i < count($data); $i++) {
                $k = 1;
                $l = 1;
                for ($j = 0; $j < count($images); $j++) {
                    if ($data[$i]['id'] == $images[$j]['id']) {
                        $data[$i]['image_' . $k]     = ImageFallback::fallback(array($images[$j]['file_url']));
                        $data[$i]['thumbnail_' . $k] = ImageFallback::fallback(array(
                            $images[$j]['file_url_thumb'],
                            $this->thumbnail($images[$j]['file_url']),
                            $images[$j]['file_url']
                        ));
                        $k++;
                    } else if ($data[$i]['parent_id'] == $images[$j]['id']) {
                        $data[$i]['parent_image_' . $l]     = ImageFallback::fallback(array($images[$j]['file_url']));
                        $data[$i]['parent_thumbnail_' . $l] = ImageFallback::fallback(array(
                            $images[$j]['file_url_thumb'],
                            $this->thumbnail($images[$j]['file_url']),
                            $images[$j]['file_url']
                        ));
                        $l++;
                    }
                }
            }
        }

        return $data;
    }

    private function thumbnail($image) {
        return str_replace($this->media_product_path, $this->media_product_path_resized, str_replace($this->extensions, $this->resized_extensions, $image));
    }

    private function datify($date, $format) {
        if (!empty($date) && $date != "0000-00-00 00:00:00") {
            $result = date($format, strtotime($date));

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
