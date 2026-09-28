<?php

namespace Nextend\SmartSlider3Pro\Generator\Joomla\Mijoshop\Elements;

use Joomla\CMS\HTML\HTMLHelper;
use MijoShop;
use Nextend\Framework\Database\Database;
use Nextend\Framework\Form\Element\Select;


class MijoshopCategories extends Select {

    public function __construct($insertAt, $name = '', $label = '', $default = '', $parameters = array()) {
        parent::__construct($insertAt, $name, $label, $default, $parameters);

        $lang       = '';
        $config     = MijoShop::get('opencart')
                              ->get('config');
        $languageId = $config->get('config_language_id');
        if (is_object($config) && $languageId) {
            $lang = ' AND cd.language_id = ' . $languageId;
        }

        $query = 'SELECT 
                    m.category_id AS id, 
                    cd.name AS name, 
                    cd.name AS title, 
                    m.parent_id AS parent, 
                    m.parent_id as parent_id
                FROM #__mijoshop_category m
                LEFT JOIN #__mijoshop_category_description AS cd ON cd.category_id = m.category_id
                WHERE m.status = 1 ' . $lang . '
                ORDER BY m.sort_order';

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

        $this->options['0'] = n2_('All');

        if (count($options)) {
            foreach ($options as $option) {
                $this->options[$option->id] = $option->treename;
            }
        }

    }

}
