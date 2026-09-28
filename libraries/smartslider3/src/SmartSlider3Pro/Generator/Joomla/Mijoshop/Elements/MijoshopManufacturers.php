<?php

namespace Nextend\SmartSlider3Pro\Generator\Joomla\Mijoshop\Elements;

use Nextend\Framework\Database\Database;
use Nextend\Framework\Form\Element\Select;


class MijoshopManufacturers extends Select {

    public function __construct($insertAt, $name = '', $label = '', $default = '', $parameters = array()) {
        parent::__construct($insertAt, $name, $label, $default, $parameters);

        $query = 'SELECT manufacturer_id AS id, name FROM #__mijoshop_manufacturer ORDER BY sort_order, id';

        $manufacturers = Database::queryAll($query, false, "object");

        $this->options['0'] = n2_('All');

        if (count($manufacturers)) {
            foreach ($manufacturers as $manufacturer) {
                $this->options[$manufacturer->id] = $manufacturer->name;
            }
        }
    }

}
