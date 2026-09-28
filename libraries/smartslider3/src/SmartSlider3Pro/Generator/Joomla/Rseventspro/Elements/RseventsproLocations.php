<?php

namespace Nextend\SmartSlider3Pro\Generator\Joomla\Rseventspro\Elements;

use Nextend\Framework\Database\Database;
use Nextend\Framework\Form\Element\Select;


class RseventsproLocations extends Select {

    public function __construct($insertAt, $name = '', $label = '', $default = '', $parameters = array()) {
        parent::__construct($insertAt, $name, $label, $default, $parameters);

        $query     = "SELECT id, name FROM #__rseventspro_locations WHERE published = 1";
        $locations = Database::queryAll($query, false, "object");

        $this->options['0'] = n2_('All');

        if (count($locations)) {
            foreach ($locations as $location) {
                $this->options[$location->id] = $location->name;
            }
        }
    }

}
