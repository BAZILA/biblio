<?php

namespace Nextend\SmartSlider3Pro\Generator\Joomla\Eventsbooking\Elements;

use Joomla\CMS\HTML\HTMLHelper;
use Nextend\Framework\Database\Database;
use Nextend\Framework\Form\Element\Select;

class EventsbookingCategories extends Select {

    public function __construct($insertAt, $name = '', $label = '', $default = '', $parameters = array()) {
        parent::__construct($insertAt, $name, $label, $default, $parameters);

        $query     = "SELECT id, parent AS parent_id, name AS title FROM #__eb_categories";
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
        $options = HTMLHelper::_('menu.treerecurse', 0, '', array(), $children, 9999, 0, 0);

        $this->options[0] = n2_('All');

        if (count($options)) {
            foreach ($options as $option) {
                $this->options[$option->id] = $option->treename;
            }
        }
    }

}
