<?php

namespace Nextend\SmartSlider3Pro\Generator\Joomla\Flexicontent\Elements;

use Joomla\CMS\Factory;
use Nextend\Framework\Form\Element\Select;


class FlexicontentTags extends Select {

    public function __construct($insertAt, $name = '', $label = '', $default = '', $parameters = array()) {
        parent::__construct($insertAt, $name, $label, $default, $parameters);

        $db = Factory::getDBO();

        $db->setQuery('SELECT id, name FROM #__flexicontent_tags WHERE published = 1 ORDER BY id');
        $menuItems = $db->loadObjectList();

        $this->options['0'] = n2_('All');

        if (count($menuItems)) {
            foreach ($menuItems as $option) {
                $this->options[$option->id] = $option->name;
            }
        }
    }
}
