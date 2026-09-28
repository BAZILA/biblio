<?php

namespace Nextend\SmartSlider3Pro\Generator\Joomla\Virtuemart\Elements;

use Nextend\Framework\Form\Element\Select;
use vmLanguage;


class VirtuemartLanguages extends Select {

    public function __construct($insertAt, $name = '', $label = '', $default = '', $parameters = array()) {
        parent::__construct($insertAt, $name, $label, $default, $parameters);

        $this->options['0'] = n2_('default');

        if (vmLanguage::$langCount) {
            foreach (vmLanguage::$langs as $lang) {
                $lang = strtolower(str_replace('-', '_', $lang));

                $this->options[$lang] = $lang;
            }
        }

    }

}
