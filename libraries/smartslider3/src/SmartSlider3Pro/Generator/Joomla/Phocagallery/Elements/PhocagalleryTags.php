<?php

namespace Nextend\SmartSlider3Pro\Generator\Joomla\Phocagallery\Elements;

use Nextend\Framework\Database\Database;
use Nextend\Framework\Form\Element\Select;


class PhocagalleryTags extends Select {

    public function __construct($insertAt, $name = '', $label = '', $default = '', $parameters = array()) {
        parent::__construct($insertAt, $name, $label, $default, $parameters);

        $query = 'SELECT id, title FROM #__phocagallery_tags WHERE published = 1 ORDER BY ordering';

        $tags = Database::queryAll($query, false, "object");

        $this->options['0'] = n2_('All');

        if (count($tags)) {
            foreach ($tags as $tag) {
                $this->options[$tag->id] = $tag->title;
            }
        }
    }

}
