<?php

namespace Nextend\SmartSlider3Pro\Generator\Joomla\Jevents;

use Nextend\Framework\Filesystem\Filesystem;
use Nextend\SmartSlider3\Generator\AbstractGeneratorGroup;
use Nextend\SmartSlider3\Generator\GeneratorFactory;
use Nextend\SmartSlider3Pro\Generator\Joomla\Jevents\Sources\JeventsEvents;
use Nextend\SmartSlider3Pro\Generator\Joomla\Jevents\Sources\JeventsRepeatingevents;

class GeneratorGroupJevents extends AbstractGeneratorGroup {

    protected $name = 'jevents';

    protected $url = 'https://extensions.joomla.org/extension/jevents/';

    public function getLabel() {
        return 'JEvents';
    }

    public function getDescription() {
        return sprintf(n2_('Creates slides from %1$s content.'), 'JEvents');
    }

    public function isInstalled() {
        return Filesystem::existsFolder(JPATH_ADMINISTRATOR . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_jevents');
    }

    protected function loadSources() {
        new JeventsEvents($this, 'events', n2_('One time events'));
        new JeventsRepeatingevents($this, 'repeatingevents', n2_('Repeating events'));
    }

    public static function formatDate($datetime, $dateOrTime, $format, $dateLanguage) {
        $checkDateTime = strtotime($datetime);
        if ($dateOrTime == 1 || $checkDateTime != '0000-00-00 00:00:00') {
            if (!empty($dateLanguage)) {
                $locale = setlocale(LC_ALL, 0);
                setlocale(LC_ALL, $dateLanguage);
                $date = strftime($format, $datetime);
                setlocale(LC_ALL, $locale);
            } else {
                $date = date($format, $datetime);
            }

            return $date;
        } else {
            return '0000-00-00';
        }
    }

}

GeneratorFactory::addGenerator(new GeneratorGroupJevents);
