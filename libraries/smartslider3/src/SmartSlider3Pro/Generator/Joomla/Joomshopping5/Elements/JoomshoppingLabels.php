<?php

namespace Nextend\SmartSlider3Pro\Generator\Joomla\Joomshopping5\Elements;

use Joomla\CMS\Factory;
use Joomla\Component\Jshopping\Site\Lib\JSFactory;
use Nextend\Framework\Form\Element\Select;


class JoomshoppingLabels extends Select {

    public function __construct($insertAt, $name = '', $label = '', $default = '', $parameters = array()) {
        parent::__construct($insertAt, $name, $label, $default, $parameters);

        $db = Factory::getDBO();

        $lang = JSFactory::getLang();

        $query = "SELECT id, `" . $lang->get('name') . "` AS name
              FROM #__jshopping_product_labels
              ORDER BY name";

        $db->setQuery($query);
        $labels = $db->loadObjectList();

        $this->options['-1'] = n2_('All');
        $this->options['0']  = n2_('None');

        if (count($labels)) {
            foreach ($labels as $option) {
                $this->options[$option->id] = $option->name;
            }
        }
    }

}
