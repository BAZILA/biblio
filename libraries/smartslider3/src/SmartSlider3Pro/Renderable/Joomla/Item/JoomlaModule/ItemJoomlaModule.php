<?php


namespace Nextend\SmartSlider3Pro\Renderable\Joomla\Item\JoomlaModule;


use Nextend\Framework\Form\Element\Message\Warning;
use Nextend\Framework\Form\Element\Select;
use Nextend\Framework\Form\Element\Text;
use Nextend\Framework\Form\Fieldset;
use Nextend\SmartSlider3\Renderable\Item\AbstractItem;

class ItemJoomlaModule extends AbstractItem {

    protected $ordering = 101;

    protected function isBuiltIn() {
        return true;
    }

    public function getType() {
        return 'joomlamodule';
    }

    public function getTitle() {
        return n2_('Joomla module');
    }

    public function getIcon() {
        return 'ssi_32 ssi_32--joomla';
    }

    public function getGroup() {
        return n2_x('Advanced', 'Layer group');
    }

    public function createFrontend($id, $itemData, $layer) {
        return new ItemJoomlaModuleFrontend($this, $id, $itemData, $layer);
    }

    public function getValues() {
        return parent::getValues() + array(
                'positiontype'  => 'loadposition',
                'positionvalue' => ''
            );
    }


    public function renderFields($container) {
        $settings = new Fieldset\LayerWindow\FieldsetLayerWindow($container, 'item-joomlamodule', n2_('General'));

        new Warning($settings, '', sprintf(n2_('Please note, that %1$swe do not support%2$s the Joomla module layer!%3$sThe loaded module often needs code customizations what you have to do yourself, so we only suggest using this layer if you are a developer!'), '<b>', '</b>', '<br>'));

        new Select($settings, 'positiontype', n2_('Type'), 'loadposition', array(
            'options' => array(
                'loadposition' => 'Loadposition - Content plugin',
                'loadmoduleid' => 'Loadmoduleid - Content plugin',
                'module'       => 'Module - Modules Anywhere',
                'modulepos'    => 'Modulepos - Modules Anywhere'
            )
        ));

        new Text($settings, 'positionvalue', n2_('Value'), '', array(
            'style'          => 'width:302px;',
            'tipLabel'       => n2_('Position name or module ID'),
            'tipDescription' => n2_('The position name of your module (for Loadposition and Modulepos) or the module\'s ID (Module).'),
            'tipLink'        => 'https://smartslider.helpscoutdocs.com/article/1853-joomla-module-layer'
        ));
    }
}