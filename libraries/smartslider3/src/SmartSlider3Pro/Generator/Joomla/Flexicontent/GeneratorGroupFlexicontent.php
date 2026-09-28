<?php

namespace Nextend\SmartSlider3Pro\Generator\Joomla\Flexicontent;

use Nextend\Framework\Filesystem\Filesystem;
use Nextend\SmartSlider3\Generator\AbstractGeneratorGroup;
use Nextend\SmartSlider3\Generator\GeneratorFactory;
use Nextend\SmartSlider3Pro\Generator\Joomla\Flexicontent\Sources\FlexicontentItems;

class GeneratorGroupFlexicontent extends AbstractGeneratorGroup {

    protected $name = 'flexicontent';

    protected $url = 'https://extensions.joomla.org/extension/flexicontent/';

    public function getLabel() {
        return 'FLEXIcontent';
    }

    public function getDescription() {
        return sprintf(n2_('Creates slides from %1$s content.'), 'FLEXIcontent');
    }

    public function isInstalled() {
        return Filesystem::existsFolder(JPATH_ADMINISTRATOR . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_flexicontent');
    }

    protected function loadSources() {
        new FlexicontentItems($this, 'items', 'Items');
    }


}

GeneratorFactory::addGenerator(new GeneratorGroupFlexicontent);

