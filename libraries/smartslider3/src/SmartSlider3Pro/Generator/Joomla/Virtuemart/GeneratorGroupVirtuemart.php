<?php

namespace Nextend\SmartSlider3Pro\Generator\Joomla\Virtuemart;

use Nextend\Framework\Filesystem\Filesystem;
use Nextend\SmartSlider3\Generator\AbstractGeneratorGroup;
use Nextend\SmartSlider3\Generator\GeneratorFactory;
use Nextend\SmartSlider3Pro\Generator\Joomla\Virtuemart\Sources\VirtuemartProducts;

class GeneratorGroupVirtuemart extends AbstractGeneratorGroup {

    protected $name = 'virtuemart';

    protected $url = 'https://extensions.joomla.org/extension/virtuemart/';

    public function getLabel() {
        return 'VirtueMart';
    }

    public function getDescription() {
        return sprintf(n2_('Creates slides from %1$s content.'), 'VirtueMart');
    }

    public function isInstalled() {
        return Filesystem::existsFile(JPATH_ADMINISTRATOR . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_virtuemart' . DIRECTORY_SEPARATOR . 'helpers' . DIRECTORY_SEPARATOR . 'config.php');
    }

    protected function loadSources() {
        new VirtuemartProducts($this, 'products', n2_('Products'));
    }


}

GeneratorFactory::addGenerator(new GeneratorGroupVirtuemart);
