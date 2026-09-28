<?php

namespace Nextend\SmartSlider3Pro\Generator\Joomla\Mijoshop;

use Nextend\Framework\Filesystem\Filesystem;
use Nextend\SmartSlider3\Generator\AbstractGeneratorGroup;
use Nextend\SmartSlider3\Generator\GeneratorFactory;
use Nextend\SmartSlider3Pro\Generator\Joomla\Mijoshop\Sources\MijoshopProducts;

class GeneratorGroupMijoshop extends AbstractGeneratorGroup {

    protected $name = 'mijoshop';

    protected $url = 'https://miwisoft.com/joomla-extensions/mijoshop-joomla-shopping-cart';

    public function getLabel() {
        return 'MijoShop';
    }

    public function getDescription() {
        return sprintf(n2_('Creates slides from %1$s content.'), 'MijoShop');
    }

    public function isInstalled() {
        return Filesystem::existsFile(JPATH_ADMINISTRATOR . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_mijoshop' . DIRECTORY_SEPARATOR . 'mijoshop.php');
    }

    protected function loadSources() {
        new MijoshopProducts($this, 'products', n2_('Products'));
    }

    public function isDeprecated() {
        return true;
    }

}

GeneratorFactory::addGenerator(new GeneratorGroupMijoshop);
