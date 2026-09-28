<?php

namespace Nextend\SmartSlider3Pro\Generator\Joomla\Hikashop\Elements;

use Nextend\Framework\Database\Database;
use Nextend\Framework\Form\Element\Select;


class HikashopTags extends Select {

    public function __construct($insertAt, $name = '', $label = '', $default = '', $parameters = array()) {
        parent::__construct($insertAt, $name, $label, $default, $parameters);

        $query = "SELECT title, id FROM #__tags WHERE published = 1 AND parent_id <> 0";

        $tags = Database::queryAll($query, false, "object");

        $this->options['0'] = n2_('All');

        if (count($tags)) {
            foreach ($tags as $tag) {
                $this->options[$tag->id] = $tag->title;
            }
        }
    }

}
