<?php

namespace Nextend\SmartSlider3Pro\Generator\Joomla\Joomshopping5;

use Nextend\Framework\Filesystem\Filesystem;
use Nextend\SmartSlider3\Generator\AbstractGeneratorGroup;
use Nextend\SmartSlider3\Generator\GeneratorFactory;
use Nextend\SmartSlider3Pro\Generator\Joomla\Joomshopping5\Sources\JoomshoppingProducts;
use Nextend\SmartSlider3\Platform\Joomla\JoomlaShim;

class GeneratorGroupJoomshopping5 extends AbstractGeneratorGroup {

    protected $name = 'joomshopping5';

    protected $url = 'https://extensions.joomla.org/extension/joomshopping/';

    public function getLabel() {
        return 'JoomShopping';
    }

    public function getDescription() {
        return sprintf(n2_('Creates slides from %1$s content.'), 'JoomShopping');
    }

    public function isInstalled() {
        return Filesystem::existsFile(JPATH_ADMINISTRATOR . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_jshopping' . DIRECTORY_SEPARATOR . 'jshopping.xml');
    }

    protected function loadSources() {
        if (JoomlaShim::$isJoomla4) {
            require_once(JPATH_SITE . "/components/com_jshopping/classmap.php");
            require_once(JPATH_SITE . "/components/com_jshopping/bootstrap.php");
            require_once(JPATH_SITE . "/components/com_jshopping/Lib/JSFactory.php");

            new JoomshoppingProducts($this, 'products', n2_('Products'));
        }
    }

    public function isDeprecated() {
        return !JoomlaShim::$isJoomla4;
    }
}

GeneratorFactory::addGenerator(new GeneratorGroupJoomshopping5);