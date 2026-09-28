<?php

namespace Nextend\SmartSlider3Pro\Generator\Joomla\Virtuemart\Elements;

use Nextend\Framework\Database\Database;
use Nextend\Framework\Form\Element\Select;
use VmConfig;


class VirtuemartManufacturers extends Select {

    public function __construct($insertAt, $name = '', $label = '', $default = '', $parameters = array()) {
        parent::__construct($insertAt, $name, $label, $default, $parameters);

        require_once(JPATH_ADMINISTRATOR . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_virtuemart' . DIRECTORY_SEPARATOR . 'helpers' . DIRECTORY_SEPARATOR . 'config.php');
        VmConfig::loadConfig();
        $query = 'SELECT virtuemart_manufacturer_id AS id, mf_name AS name FROM #__virtuemart_manufacturers_' . VMLANG . ' ORDER BY id';

        $manufacturers = Database::queryAll($query, false, "object");

        $this->options['0'] = n2_('All');

        if (count($manufacturers)) {
            foreach ($manufacturers as $manufacturer) {
                $this->options[$manufacturer->id] = $manufacturer->name;
            }
        }

    }

}
