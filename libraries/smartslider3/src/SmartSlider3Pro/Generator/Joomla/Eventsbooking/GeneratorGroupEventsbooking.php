<?php

namespace Nextend\SmartSlider3Pro\Generator\Joomla\Eventsbooking;

use Nextend\Framework\Filesystem\Filesystem;
use Nextend\SmartSlider3\Generator\AbstractGeneratorGroup;
use Nextend\SmartSlider3\Generator\GeneratorFactory;
use Nextend\SmartSlider3Pro\Generator\Joomla\Eventsbooking\Sources\EventsbookingEvents;

class GeneratorGroupEventsbooking extends AbstractGeneratorGroup {

    protected $name = 'eventsbooking';

    protected $url = 'https://extensions.joomla.org/extension/event-booking/';

    public function getLabel() {
        return 'Event Booking';
    }

    public function getDescription() {
        return sprintf(n2_('Creates slides from %1$s content.'), 'Event Booking');
    }

    public function isInstalled() {
        return Filesystem::existsFile(JPATH_ADMINISTRATOR . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_eventbooking' . DIRECTORY_SEPARATOR . 'eventbooking.php');
    }

    protected function loadSources() {
        new EventsbookingEvents($this, 'events', n2_('Events'));
    }
}

GeneratorFactory::addGenerator(new GeneratorGroupEventsbooking);
