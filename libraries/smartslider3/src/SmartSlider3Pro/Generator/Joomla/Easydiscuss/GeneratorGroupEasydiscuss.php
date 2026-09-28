<?php

namespace Nextend\SmartSlider3Pro\Generator\Joomla\Easydiscuss;

use Nextend\Framework\Filesystem\Filesystem;
use Nextend\SmartSlider3\Generator\AbstractGeneratorGroup;
use Nextend\SmartSlider3\Generator\GeneratorFactory;
use Nextend\SmartSlider3Pro\Generator\Joomla\Easydiscuss\Sources\EasydiscussDiscussions;

class GeneratorGroupEasydiscuss extends AbstractGeneratorGroup {

    protected $name = 'easydiscuss';

    protected $url = 'https://extensions.joomla.org/extensions/extension/communication/question-a-answers/easydiscuss/';

    public function getLabel() {
        return 'EasyDiscuss';
    }

    public function getDescription() {
        return sprintf(n2_('Creates slides from %1$s content.'), 'EasyDiscuss');
    }

    public function isInstalled() {
        return Filesystem::existsFolder(JPATH_ADMINISTRATOR . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_easydiscuss');
    }

    protected function loadSources() {
        new EasydiscussDiscussions($this, 'discussions', 'Discussions');
    }


}

GeneratorFactory::addGenerator(new GeneratorGroupEasydiscuss);

