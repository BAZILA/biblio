<?php

namespace Nextend\SmartSlider3Pro\Generator\Joomla\Eshop;

use Nextend\Framework\Filesystem\Filesystem;
use Nextend\SmartSlider3\Generator\AbstractGeneratorGroup;
use Nextend\SmartSlider3\Generator\GeneratorFactory;
use Nextend\SmartSlider3Pro\Generator\Joomla\Eshop\Sources\EshopProducts;

class GeneratorGroupEshop extends AbstractGeneratorGroup {

    protected $name = 'eshop';

    protected $url = 'https://extensions.joomla.org/extension/e-commerce/shopping-cart/eshop/';

    public function getLabel() {
        return 'EShop';
    }

    public function getDescription() {
        return sprintf(n2_('Creates slides from %1$s content.'), 'EShop');
    }

    public function isInstalled() {
        return Filesystem::existsFile(JPATH_ADMINISTRATOR . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_eshop' . DIRECTORY_SEPARATOR . 'eshop.php');
    }

    protected function loadSources() {
        new EshopProducts($this, 'products', n2_('Products'));
    }


}

GeneratorFactory::addGenerator(new GeneratorGroupEshop);
