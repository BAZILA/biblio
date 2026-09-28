<?php

namespace Nextend\SmartSlider3Pro\Generator\Joomla\Joomshopping5\Elements;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Nextend\Framework\Form\Element\Select;
use Joomla\Component\Jshopping\Site\Lib\JSFactory;


class JoomshoppingCategories extends Select {

    public function __construct($insertAt, $name = '', $label = '', $default = '', $parameters = array()) {
        parent::__construct($insertAt, $name, $label, $default, $parameters);

        $db = Factory::getDBO();

        $lang = JSFactory::getLang();

        $query = "SELECT m.category_id AS id, `" . $lang->get('name') . "` AS title, `" . $lang->get('name') . "` AS name, m.category_parent_id AS parent_id, m.category_parent_id as parent
              FROM #__jshopping_categories AS m
              LEFT JOIN #__jshopping_products_to_categories AS f
              ON m.category_id = f.category_id
              WHERE m.category_publish = 1
              ORDER BY ordering";

        $db->setQuery($query);
        $menuItems = $db->loadObjectList();

        $children = array();
        if ($menuItems) {
            foreach ($menuItems as $v) {
                $pt   = $v->parent_id;
                $list = isset($children[$pt]) ? $children[$pt] : array();
                array_push($list, $v);
                $children[$pt] = $list;
            }
        }
        jimport('joomla.html.html.menu');
        $options            = HTMLHelper::_('menu.treerecurse', 0, '', array(), $children, 9999, 0, 0);
        $this->options['0'] = n2_('All');

        if (count($options)) {
            foreach ($options as $option) {
                $this->options[$option->id] = $option->treename;
            }
        }
    }

}
