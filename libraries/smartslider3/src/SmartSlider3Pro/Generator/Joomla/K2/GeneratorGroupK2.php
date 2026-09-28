<?php

namespace Nextend\SmartSlider3Pro\Generator\Joomla\K2;

use Nextend\Framework\Filesystem\Filesystem;
use Nextend\SmartSlider3\Generator\AbstractGeneratorGroup;
use Nextend\SmartSlider3\Generator\GeneratorFactory;
use Nextend\SmartSlider3Pro\Generator\Joomla\K2\Sources\K2Items;

class GeneratorGroupK2 extends AbstractGeneratorGroup {

    protected $name = 'k2';

    protected $url = 'https://extensions.joomla.org/extension/authoring-a-content/content-construction/k2/';

    public function getLabel() {
        return 'K2';
    }

    public function getDescription() {
        return sprintf(n2_('Creates slides from %1$s content.'), 'K2 ' . n2_('Items'));
    }

    public function isInstalled() {
        return Filesystem::existsFolder(JPATH_ADMINISTRATOR . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_k2');
    }

    protected function loadSources() {
        new K2Items($this, 'items', n2_('Items'));
    }

    public function isDeprecated() {
        return true;
    }

}

GeneratorFactory::addGenerator(new GeneratorGroupK2);
