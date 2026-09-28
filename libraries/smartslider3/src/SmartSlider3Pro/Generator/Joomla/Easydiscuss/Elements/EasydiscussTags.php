<?php

namespace Nextend\SmartSlider3Pro\Generator\Joomla\Easydiscuss\Elements;

use Nextend\Framework\Database\Database;
use Nextend\Framework\Form\Element\Select;


class EasydiscussTags extends Select {

    protected $isMultiple = true;
    protected $size = 10;

    public function __construct($insertAt, $name = '', $label = '', $default = '', $parameters = array()) {
        parent::__construct($insertAt, $name, $label, $default, $parameters);

        $menuItems = Database::queryAll('SELECT * FROM #__discuss_tags WHERE published = 1 ORDER BY id', false, "object");

        $this->options['0'] = n2_('All');

        if (count($menuItems)) {
            foreach ($menuItems as $option) {
                $this->options[$option->id] = $option->title;
            }
        }
    }
}