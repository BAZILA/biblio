<?php

namespace Nextend\SmartSlider3Pro\Generator\Joomla\Phocagallery;

use Nextend\Framework\Filesystem\Filesystem;
use Nextend\SmartSlider3\Generator\AbstractGeneratorGroup;
use Nextend\SmartSlider3\Generator\GeneratorFactory;
use Nextend\SmartSlider3Pro\Generator\Joomla\Phocagallery\Sources\PhocagalleryImages;

class GeneratorGroupPhocagallery extends AbstractGeneratorGroup {

    protected $name = 'phocagallery';

    protected $url = 'https://extensions.joomla.org/extension/phoca-gallery/';

    public function getLabel() {
        return 'Phoca Gallery';
    }

    public function getDescription() {
        return sprintf(n2_('Creates slides from %1$s content.'), 'Phoca Gallery');
    }

    public function isInstalled() {
        return Filesystem::existsFolder(JPATH_ADMINISTRATOR . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_phocagallery');
    }

    protected function loadSources() {
        new PhocagalleryImages($this, 'images', n2_('Images'));
    }


}

GeneratorFactory::addGenerator(new GeneratorGroupPhocagallery);
