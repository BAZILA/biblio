<?php

namespace Nextend\SmartSlider3Pro\Generator\Joomla\Hikashop\Elements;

use Joomla\CMS\HTML\HTMLHelper;
use Nextend\Framework\Database\Database;
use Nextend\Framework\Form\Element\Select;

class HikashopBrands extends Select {

    public function __construct($insertAt, $name = '', $label = '', $default = '', $parameters = array()) {
        parent::__construct($insertAt, $name, $label, $default, $parameters);

        $query = "SELECT category_id AS id, category_name AS title, category_name AS name,
        category_parent_id AS parent_id, category_parent_id AS parent FROM #__hikashop_category WHERE category_published = 1 AND category_type = 'manufacturer'";

        $menuItems = Database::queryAll($query, false, "object");

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
        $options = HTMLHelper::_('menu.treerecurse', 1, '', array(), $children, 9999, 0, 0);

        $this->options['0'] = n2_('All');

        if (count($options)) {
            foreach ($options as $option) {
                $this->options[$option->id] = $option->treename;
            }
        }
    }

}
