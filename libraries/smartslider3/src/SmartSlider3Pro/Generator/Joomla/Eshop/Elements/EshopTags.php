<?php

namespace Nextend\SmartSlider3Pro\Generator\Joomla\Eshop\Elements;

use Nextend\Framework\Database\Database;
use Nextend\Framework\Form\Element\Select;


class EshopTags extends Select {

    public function __construct($insertAt, $name = '', $label = '', $default = '', $parameters = array()) {
        parent::__construct($insertAt, $name, $label, $default, $parameters);

        $query = 'SELECT tag_name, id
                  FROM #__eshop_tags
                  ORDER BY id';

        $tags = Database::queryAll($query, false, "object");

        $this->options[0] = n2_('All');

        if (count($tags)) {
            foreach ($tags as $tag) {
                $this->options[$tag->id] = $tag->tag_name;
            }
        }
    }

}
