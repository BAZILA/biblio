<?php

namespace Nextend\SmartSlider3Pro\Generator\Joomla\Hikashop\Elements;

use Nextend\Framework\Database\Database;
use Nextend\Framework\Form\Element\Select;


class HikashopWarehouses extends Select {

    public function __construct($insertAt, $name = '', $label = '', $default = '', $parameters = array()) {
        parent::__construct($insertAt, $name, $label, $default, $parameters);

        $query = "SELECT warehouse_name, warehouse_id FROM #__hikashop_warehouse WHERE warehouse_published = 1";

        $warehouses = Database::queryAll($query, false, "object");

        $this->options['0'] = n2_('All');

        if (count($warehouses)) {
            foreach ($warehouses as $warehouse) {
                $this->options[$warehouse->warehouse_id] = $warehouse->warehouse_name;
            }
        }
    }

}
