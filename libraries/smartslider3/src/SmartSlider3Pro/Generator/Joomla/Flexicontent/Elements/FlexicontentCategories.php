<?php

namespace Nextend\SmartSlider3Pro\Generator\Joomla\Flexicontent\Elements;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Nextend\Framework\Form\Element\Select;


class FlexicontentCategories extends Select {

    public function __construct($insertAt, $name = '', $label = '', $default = '', $parameters = array()) {
        parent::__construct($insertAt, $name, $label, $default, $parameters);

        $db = Factory::getDBO();

        $query = 'SELECT
                    m.id, 
                    m.title AS name, 
                    m.title, 
                    m.parent_id AS parent, 
                    m.parent_id
                FROM #__categories m
                WHERE m.published = 1 AND (m.extension = "com_content" OR m.extension = "system")
                ORDER BY m.lft';


        $db->setQuery($query);
        $menuItems = $db->loadObjectList();
        $children  = array();
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
            array_shift($options);
            foreach ($options as $option) {
                $this->options[$option->id] = $option->treename;
            }
        }
    }

}
