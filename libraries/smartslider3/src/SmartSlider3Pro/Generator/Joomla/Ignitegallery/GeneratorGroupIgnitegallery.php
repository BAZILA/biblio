<?php

namespace Nextend\SmartSlider3Pro\Generator\Joomla\Ignitegallery;

use Nextend\Framework\Filesystem\Filesystem;
use Nextend\SmartSlider3\Generator\AbstractGeneratorGroup;
use Nextend\SmartSlider3\Generator\GeneratorFactory;
use Nextend\SmartSlider3Pro\Generator\Joomla\Ignitegallery\Sources\IgnitegalleryImages;

class GeneratorGroupIgnitegallery extends AbstractGeneratorGroup {

    protected $name = 'ignitegallery';

    protected $url = 'https://extensions.joomla.org/profile/extension/photos-a-images/galleries/ignite-gallery/';

    public function getLabel() {
        return 'Ignite Gallery';
    }

    public function getDescription() {
        return sprintf(n2_('Creates slides from %1$s content.'), 'Ignite Gallery');
    }

    public function isInstalled() {
        return Filesystem::existsFolder(JPATH_ADMINISTRATOR . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_igallery');
    }

    protected function loadSources() {
        new IgnitegalleryImages($this, 'images', n2_('Images'));
    }


}

GeneratorFactory::addGenerator(new GeneratorGroupIgnitegallery);

