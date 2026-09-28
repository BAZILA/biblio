<?php

namespace Nextend\SmartSlider3Pro\Generator\Joomla\Joomshopping5\Elements;

use Joomla\CMS\Factory;
use Joomla\Component\Jshopping\Site\Lib\JSFactory;
use Nextend\Framework\Form\Element\Select;

class JoomshoppingManufacturers extends Select {

    public function __construct($insertAt, $name = '', $label = '', $default = '', $parameters = array()) {
        parent::__construct($insertAt, $name, $label, $default, $parameters);

        $db = Factory::getDBO();

        $lang = JSFactory::getLang();

        $query = "SELECT manufacturer_id AS id, `" . $lang->get('name') . "` AS title
              FROM #__jshopping_manufacturers
              WHERE manufacturer_publish = 1
              ORDER BY ordering";

        $db->setQuery($query);
        $menuItems = $db->loadObjectList();

        $this->options['0'] = n2_('All');

        if (count($menuItems)) {
            foreach ($menuItems as $option) {
                $this->options[$option->id] = $option->title;
            }
        }
    }

}
