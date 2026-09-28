<?php

namespace Nextend\SmartSlider3Pro\Generator\Joomla\Easysocial;

use Nextend\Framework\Filesystem\Filesystem;
use Nextend\SmartSlider3\Generator\AbstractGeneratorGroup;
use Nextend\SmartSlider3\Generator\GeneratorFactory;
use Nextend\SmartSlider3Pro\Generator\Joomla\Easysocial\Sources\EasysocialAlbums;
use Nextend\SmartSlider3Pro\Generator\Joomla\Easysocial\Sources\EasysocialEvents;
use Nextend\SmartSlider3Pro\Generator\Joomla\Easysocial\Sources\EasysocialGroups;
use Nextend\SmartSlider3Pro\Generator\Joomla\Easysocial\Sources\EasysocialPages;
use Nextend\SmartSlider3Pro\Generator\Joomla\Easysocial\Sources\EasysocialUsers;
use Nextend\SmartSlider3Pro\Generator\Joomla\Easysocial\Sources\EasysocialVideos;

class GeneratorGroupEasysocial extends AbstractGeneratorGroup {

    protected $name = 'easysocial';

    protected $url = 'https://extensions.joomla.org/extension/easysocial/';

    public function getLabel() {
        return 'EasySocial';
    }

    public function getDescription() {
        return sprintf(n2_('Creates slides from %1$s content.'), 'EasySocial');
    }

    public function isInstalled() {
        return Filesystem::existsFolder(JPATH_ADMINISTRATOR . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_easysocial');
    }

    protected function loadSources() {
        new EasysocialEvents($this, 'events', n2_('Events'));
        new EasysocialGroups($this, 'groups', n2_('Groups'));
        new EasysocialAlbums($this, 'albums', n2_('Albums'));
        new EasysocialVideos($this, 'videos', n2_('Videos'));
        new EasysocialPages($this, 'pages', n2_('Pages'));
        new EasysocialUsers($this, 'users', n2_('Users'));
    }


}

GeneratorFactory::addGenerator(new GeneratorGroupEasysocial);

