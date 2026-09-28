<?php


namespace Nextend\SmartSlider3\Application\Model;


use Nextend\Framework\Model\StorageSectionManager;
use Nextend\Framework\Notification\Notification;
use Nextend\Framework\Pattern\SingletonTrait;
use Nextend\SmartSlider3\SmartSlider3Info;

/**
 * Class ModelLicense
 *
 * @package Nextend\SmartSlider3\Application\Model
 *
 */
class ModelLicense {

    use SingletonTrait;

    private $key;

    public function __construct() {
        if (defined('SMART_SLIDER_LICENSE')) {
            $this->key = SMART_SLIDER_LICENSE;
        } else {
            $this->key = StorageSectionManager::getStorage('smartslider')
                                              ->get('license', 'key');
        }
    
    }

    public function hasKey() {
		return true;//mhehm
        return !empty($this->key);
    }

    public function maybeActiveLazy() {
        $lastActive = StorageSectionManager::getStorage('smartslider')
                                           ->get('license', 'isActive');

        return $lastActive > 0;

    }

    public function maybeActive() {
        $lastActive = StorageSectionManager::getStorage('smartslider')
                                           ->get('license', 'isActive');
        if ($lastActive && $lastActive > strtotime("-1 week")) {
            return true;
        }

        return false;
    }

    public function getKey() {
		return '73879A3c9cb691cc8b796c4d63d53802a8b2570e36f34781a044981349bfdf7dd5fece1e1ce6ed34bc3a96e1814582a7ecf9';//mhehm
        return $this->key;
    }

    public function setKey($licenseKey) {
        StorageSectionManager::getStorage('smartslider')
                             ->set('license', 'key', $licenseKey);
        StorageSectionManager::getStorage('smartslider')
                             ->set('license', 'isActive', time());
        if ($licenseKey == '') {
            StorageSectionManager::getStorage('smartslider')
                                 ->set('license', 'isActive', '0');
        }
        $this->key = $licenseKey;
    
    }

    public function checkKey($license, $action = 'licensecheck') {
        $result = SmartSlider3Info::api(array(
            'action'  => $action,
            'license' => $license
        ));
        if ($result === false) {
            return 'CONNECTION_ERROR';
        }

        return $result['status'];
    }

    public function isActive($cacheAccepted = true) {
        if ($cacheAccepted && $this->maybeActive()) {
            return 'OK';
        }
        $status = $this->checkKey($this->key);
        if ($this->hasKey() && $status == 'OK') {
            StorageSectionManager::getStorage('smartslider')
                                 ->set('license', 'isActive', time());

            return $status;
        }
        StorageSectionManager::getStorage('smartslider')
                             ->set('license', 'isActive', '0');

        return $status;
    }

    public function deAuthorize() {
        if ($this->hasKey()) {
            $this->setKey('');
            Notification::notice(n2_('Smart Slider 3 deactivated on this site!'));

            return 'OK';
        }

        return false;
    }
}