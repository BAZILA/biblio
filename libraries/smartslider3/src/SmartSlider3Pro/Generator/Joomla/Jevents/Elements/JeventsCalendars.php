<?php

namespace Nextend\SmartSlider3Pro\Generator\Joomla\Jevents\Elements;

use Nextend\Framework\Database\Database;
use Nextend\Framework\Form\Element\Select;


class JeventsCalendars extends Select {

    public function __construct($insertAt, $name = '', $label = '', $default = '', $parameters = array()) {
        parent::__construct($insertAt, $name, $label, $default, $parameters);

        $query     = "SELECT ics_id, label FROM #__jevents_icsfile WHERE state = '1'";
        $calendars = Database::queryAll($query, false, "object");

        $this->options['0'] = n2_('All');

        if (count($calendars)) {
            foreach ($calendars as $calendar) {
                $this->options[$calendar->ics_id] = $calendar->label;
            }
        }

    }

}
