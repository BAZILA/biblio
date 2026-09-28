<?php

namespace Nextend\SmartSlider3Pro\Generator\Joomla\Joomshopping;

use Nextend\Framework\Filesystem\Filesystem;
use Nextend\SmartSlider3\Generator\AbstractGeneratorGroup;
use Nextend\SmartSlider3\Generator\GeneratorFactory;
use Nextend\SmartSlider3Pro\Generator\Joomla\Joomshopping\Sources\JoomshoppingProducts;
use Nextend\SmartSlider3\Platform\Joomla\JoomlaShim;

class GeneratorGroupJoomshopping extends AbstractGeneratorGroup {

    protected $name = 'joomshopping';

    protected $url = 'https://extensions.joomla.org/extension/joomshopping/';

    public function getLabel() {
        return 'JoomShopping';
    }

    public function getDescription() {
        return sprintf(n2_('Creates slides from %1$s content.'), 'JoomShopping');
    }

    public function isInstalled() {
        return Filesystem::existsFile(JPATH_ADMINISTRATOR . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_jshopping' . DIRECTORY_SEPARATOR . 'jshopping.php');
    }

    protected function loadSources() {
        new JoomshoppingProducts($this, 'products', n2_('Products'));
    }

    public function isDeprecated() {
        return JoomlaShim::$isJoomla4;
    }
}

GeneratorFactory::addGenerator(new GeneratorGroupJoomshopping);