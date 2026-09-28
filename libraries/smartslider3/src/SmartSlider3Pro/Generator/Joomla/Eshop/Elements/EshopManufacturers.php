<?php

namespace Nextend\SmartSlider3Pro\Generator\Joomla\Eshop\Elements;

use Nextend\Framework\Database\Database;
use Nextend\Framework\Form\Element\Select;


class EshopManufacturers extends Select {

    public function __construct($insertAt, $name = '', $label = '', $default = '', $parameters = array()) {
        parent::__construct($insertAt, $name, $label, $default, $parameters);

        $query = 'SELECT manufacturer_name, manufacturer_id
                  FROM #__eshop_manufacturerdetails
                  ORDER BY manufacturer_id';

        $manufacturers = Database::queryAll($query, false, "object");

        $this->options[0] = n2_('All');

        if (count($manufacturers)) {
            foreach ($manufacturers as $manufacturer) {
                $this->options[$manufacturer->manufacturer_id] = $manufacturer->manufacturer_name;
            }
        }
    }

}
