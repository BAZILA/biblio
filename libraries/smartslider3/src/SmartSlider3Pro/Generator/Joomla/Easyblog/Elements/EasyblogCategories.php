<?php

namespace Nextend\SmartSlider3Pro\Generator\Joomla\Easyblog\Elements;

use Joomla\CMS\HTML\HTMLHelper;
use Nextend\Framework\Database\Database;
use Nextend\Framework\Form\Element\Select;


class EasyblogCategories extends Select {

    protected $isMultiple = true;
    protected $size = 10;

    public function __construct($insertAt, $name = '', $label = '', $default = '', $parameters = array()) {
        parent::__construct($insertAt, $name, $label, $default, $parameters);

        $menuItems = Database::queryAll('SELECT * FROM #__easyblog_category WHERE published = 1 ORDER BY parent_id, ordering', false, "object");

        $children = array();
        if ($menuItems) {
            foreach ($menuItems as $v) {
                $pt   = $v->parent_id;
                $list = isset($children[$pt]) ? $children[$pt] : array();
                array_push($list, $v);
                $children[$pt] = $list;
            }
        }

        $this->options['0'] = n2_('All');

        jimport('joomla.html.html.menu');
        $options = HTMLHelper::_('menu.treerecurse', 0, '', array(), $children, 9999, 0, 0);
        if (count($options)) {
            foreach ($options as $option) {
                $this->options[$option->id] = $option->treename;
            }
        }
    }
}