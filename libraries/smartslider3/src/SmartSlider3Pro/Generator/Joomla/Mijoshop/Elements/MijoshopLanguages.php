<?php

namespace Nextend\SmartSlider3Pro\Generator\Joomla\Mijoshop\Elements;

use Nextend\Framework\Database\Database;
use Nextend\Framework\Form\Element\Select;


class MijoshopLanguages extends Select {

    public function __construct($insertAt, $name = '', $label = '', $default = '', $parameters = array()) {
        parent::__construct($insertAt, $name, $label, $default, $parameters);

        $query = 'SELECT lang_id, title
                FROM #__languages
                WHERE published = 1';

        $languages = Database::queryAll($query, false, "object");

        $this->options['0'] = 'Auto';

        if (count($languages)) {
            foreach ($languages as $language) {
                $this->options[$language->lang_id] = $language->title;
            }
        }
    }

}
