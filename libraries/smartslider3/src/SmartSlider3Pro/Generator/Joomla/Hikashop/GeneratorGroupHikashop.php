<?php

namespace Nextend\SmartSlider3Pro\Generator\Joomla\Hikashop;

use Nextend\Framework\Filesystem\Filesystem;
use Nextend\SmartSlider3\Generator\AbstractGeneratorGroup;
use Nextend\SmartSlider3\Generator\GeneratorFactory;
use Nextend\SmartSlider3Pro\Generator\Joomla\Hikashop\Sources\HikashopProducts;
use Nextend\SmartSlider3Pro\Generator\Joomla\Hikashop\Sources\HikashopProductsbyid;

class GeneratorGroupHikashop extends AbstractGeneratorGroup {

    protected $name = 'hikashop';

    protected $url = 'https://extensions.joomla.org/extension/hikashop/';

    public function getLabel() {
        return 'HikaShop';
    }

    public function getDescription() {
        return sprintf(n2_('Creates slides from %1$s content.'), 'HikaShop');
    }

    public function isInstalled() {
        return Filesystem::existsFile(JPATH_ADMINISTRATOR . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_hikashop' . DIRECTORY_SEPARATOR . 'hikashop.php');
    }

    protected function loadSources() {
        new HikashopProducts($this, 'products', n2_('Products'));
        new HikashopProductsbyid($this, 'productsbyid', n2_('Products') . ' - IDs');
    }


}

GeneratorFactory::addGenerator(new GeneratorGroupHikashop);
