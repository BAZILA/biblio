<?php

namespace Nextend\SmartSlider3Pro\Generator\Joomla\Jevents\Elements;

use Joomla\CMS\HTML\HTMLHelper;
use Nextend\Framework\Database\Database;
use Nextend\Framework\Form\Element\Select;


class JeventsCategories extends Select {

    public function __construct($insertAt, $name = '', $label = '', $default = '', $parameters = array()) {
        parent::__construct($insertAt, $name, $label, $default, $parameters);

        $query     = "SELECT id, parent_id, title, name FROM #__assets WHERE name LIKE '%com_jevents.category%' ORDER BY parent_id";
        $menuItems = Database::queryAll($query, false, "object");

        $query      = "SELECT id FROM #__assets WHERE name = 'com_jevents' LIMIT 1";
        $mainParent = Database::queryAll($query, false, "object");

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
        $options = HTMLHelper::_('menu.treerecurse', $mainParent[0]->id, '', array(), $children, 9999, 0, 0);

        $this->options['0'] = n2_('All');

        if (count($options)) {
            foreach ($options as $option) {
                $id                    = explode('.', $option->name);
                $this->options[$id[2]] = $option->treename;
            }
        }

    }
}
