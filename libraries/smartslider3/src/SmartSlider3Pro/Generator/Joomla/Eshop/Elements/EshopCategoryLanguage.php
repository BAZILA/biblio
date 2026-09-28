<?php

namespace Nextend\SmartSlider3Pro\Generator\Joomla\Eshop\Elements;

use Nextend\Framework\Database\Database;
use Nextend\Framework\Form\Element\Select;


class EshopCategoryLanguage extends Select {

    public function __construct($insertAt, $name = '', $label = '', $default = '', $parameters = array()) {
        parent::__construct($insertAt, $name, $label, $default, $parameters);

        $query = 'SELECT language
                  FROM #__eshop_categorydetails
                  GROUP BY language';

        $languages = Database::queryAll($query, false, "object");

        $this->options[0] = n2_('Default');

        if (count($languages)) {
            foreach ($languages as $language) {
                $this->options[$language->language] = $language->language;
            }
        }
    }

}
