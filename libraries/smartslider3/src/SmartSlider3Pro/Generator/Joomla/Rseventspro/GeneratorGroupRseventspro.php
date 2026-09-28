<?php

namespace Nextend\SmartSlider3Pro\Generator\Joomla\Rseventspro;

use Nextend\Framework\Filesystem\Filesystem;
use Nextend\SmartSlider3\Generator\AbstractGeneratorGroup;
use Nextend\SmartSlider3\Generator\GeneratorFactory;
use Nextend\SmartSlider3Pro\Generator\Joomla\Rseventspro\Sources\RseventsproEvents;

class GeneratorGroupRseventspro extends AbstractGeneratorGroup {

    protected $name = 'rseventspro';

    protected $url = 'https://extensions.joomla.org/extension/rsevents-pro/';

    public function getLabel() {
        return 'RSEvents!Pro';
    }

    public function getDescription() {
        return sprintf(n2_('Creates slides from %1$s content.'), 'RSEvents!Pro');
    }

    public function isInstalled() {
        return Filesystem::existsFile(JPATH_ADMINISTRATOR . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_rseventspro' . DIRECTORY_SEPARATOR . 'rseventspro.php');
    }

    protected function loadSources() {
        new RseventsproEvents($this, 'events', n2_('Events'));
    }


}

GeneratorFactory::addGenerator(new GeneratorGroupRseventspro);
