<?php


namespace Nextend\SmartSlider3Pro\Renderable\Joomla\Item\JoomlaModule;


use Nextend\SmartSlider3\Renderable\Item\AbstractItemFrontend;

class ItemJoomlaModuleFrontend extends AbstractItemFrontend {

    public function render() {

        return '<div class="n2-ss-item-content n2-ow">{' . $this->data->get('positiontype', '') . ' ' . $this->data->get('positionvalue', '') . '}</div>';
    }

    public function renderAdminTemplate() {

        return $this->render();
    }
}