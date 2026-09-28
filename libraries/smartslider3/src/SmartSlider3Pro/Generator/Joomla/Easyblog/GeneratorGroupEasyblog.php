<?php

namespace Nextend\SmartSlider3Pro\Generator\Joomla\Easyblog;

use Nextend\Framework\Filesystem\Filesystem;
use Nextend\SmartSlider3\Generator\AbstractGeneratorGroup;
use Nextend\SmartSlider3\Generator\GeneratorFactory;
use Nextend\SmartSlider3Pro\Generator\Joomla\Easyblog\Sources\EasyblogPosts;

class GeneratorGroupEasyblog extends AbstractGeneratorGroup {

    protected $name = 'easyblog';

    protected $url = 'https://extensions.joomla.org/extension/easyblog/';

    public function getLabel() {
        return 'EasyBlog';
    }

    public function getDescription() {
        return sprintf(n2_('Creates slides from %1$s content.'), 'EasyBlog');
    }

    public function isInstalled() {
        return Filesystem::existsFolder(JPATH_ADMINISTRATOR . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_easyblog');
    }

    protected function loadSources() {
        new EasyblogPosts($this, 'posts', 'Posts');
    }


}

GeneratorFactory::addGenerator(new GeneratorGroupEasyblog);

