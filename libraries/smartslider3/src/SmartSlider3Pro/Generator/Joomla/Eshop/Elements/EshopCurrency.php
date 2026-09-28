<?php

namespace Nextend\SmartSlider3Pro\Generator\Joomla\Eshop\Elements;

use Nextend\Framework\Database\Database;
use Nextend\Framework\Form\Element\Select;


class EshopCurrency extends Select {

    public function __construct($insertAt, $name = '', $label = '', $default = '', $parameters = array()) {
        parent::__construct($insertAt, $name, $label, $default, $parameters);

        $query = 'SELECT currency_code
                  FROM #__eshop_currencies
                  ORDER BY id';

        $codes = Database::queryAll($query, false, "object");

        $this->options[0] = n2_('Default');
        if (count($codes)) {
            foreach ($codes as $code) {
                $this->options[$code->currency_code] = $code->currency_code;
            }
        }
    }

}
