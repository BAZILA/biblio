<?php

namespace Nextend\SmartSlider3Pro\Generator\Joomla\K2\Elements;

use Nextend\Framework\Database\Database;
use Nextend\Framework\Form\Element\Select;


class K2Tags extends Select {

    public function __construct($insertAt, $name = '', $label = '', $default = '', $parameters = array()) {
        parent::__construct($insertAt, $name, $label, $default, $parameters);

        $query = 'SELECT id, name FROM #__k2_tags WHERE published = 1 ORDER BY id';

        $tags = Database::queryAll($query, false, "object");

        $this->options['0'] = n2_('All');

        if (count($tags)) {
            foreach ($tags as $tag) {
                $this->options[$tag->id] = $tag->name;
            }
        }
    }

}
