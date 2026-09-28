<?php
/**
 * @Copyright ((c) plasma-web.ru
 * @license    GNU/GPL
 */

defined ('_JEXEC') or die ();

use Joomla\CMS\Factory;
use Joomla\Database\DatabaseInterface;
use Joomla\CMS\Installer\Installer;

class com_qf3InstallerScript {
  public function install ($parent) {
    return $this->qfinstall ($parent);
  }

  public function update ($parent) {
    return $this->qfinstall ($parent);
  }

  public function qfinstall ($parent) {
    $db = $this->getDbo();
    $manifest = $parent->getParent ()->manifest;
    $status = [];
    $src = $parent->getParent ()->getPath ('source');

    $plugins = $manifest->xpath ('plugins/plugin');
    $modules = $manifest->xpath ('modules/module');

    foreach ($plugins as $plugin) {
      $installer = $this->getInstaller ($db);
      $result = $installer->install ("$src/plugins/" . (string) $plugin->attributes ()->plugin);
      if ((string) $plugin->attributes ()->activate == 'true') {
        $db->setQuery ("UPDATE `#__extensions` SET `enabled` = 1 WHERE `type` = 'plugin' AND `element` = " . $db->Quote ((string) $plugin->attributes ()->plugin) . " AND `folder` = " . $db->Quote ((string) $plugin->attributes ()->group));
        $db->execute ();
      }
      $status[] = ['name' => 'plugin ' . (string) $plugin->attributes ()->name, 'result' => $result];
    }

    foreach ($modules as $module) {
      $installer = $this->getInstaller ($db);
      $result = $installer->install ("$src/modules/" . (string) $module->attributes ()->module);
      $status[] = ['name' => 'module ' . (string) $module->attributes ()->name, 'result' => $result];
    }

    return $status;
  }

  public function uninstall ($parent) {
    $db = $this->getDbo();
    $status = [];
    $manifest = $parent->getParent ()->manifest;

    $modules = $manifest->xpath ('modules/module');
    $plugins = $manifest->xpath ('plugins/plugin');

    foreach ($plugins as $plugin) {
      $query = "SELECT `extension_id` FROM `#__extensions` WHERE `type`='plugin' AND element = " . $db->Quote ((string) $plugin->attributes ()->plugin) . " AND folder = " . $db->Quote ((string) $plugin->attributes ()->group);
      $db->setQuery ($query);
      $extensions = $db->loadColumn ();
      if (count ($extensions)) {
        foreach ($extensions as $id) {
          $installer = $this->getInstaller ($db);
          $result = $installer->uninstall ('plugin', $id);
        }
        $status[] = ['name' => 'plugin ' . (string) $plugin->attributes ()->name, 'result' => $result];
      }
    }

    foreach ($modules as $module) {
      $query = "SELECT `extension_id` FROM `#__extensions` WHERE `type`='module' AND element = " . $db->Quote ((string) $module->attributes ()->module);
      $db->setQuery ($query);
      $extensions = $db->loadColumn ();
      if (count ($extensions)) {
        foreach ($extensions as $id) {
          $installer = $this->getInstaller ($db);
          $result = $installer->uninstall ('module', $id);
        }
        $status[] = ['name' => 'module ' . (string) $module->attributes ()->name, 'result' => $result];
      }
    }

    return $status;
  }

  public function getDbo () {
    if (class_exists ('Joomla\CMS\Factory') && method_exists ('Joomla\CMS\Factory', 'getContainer')) {
      return Factory::getContainer ()->get (DatabaseInterface::class);
    }
    else {
      return \JFactory::getDbo ();
    }
  }

  public function getInstaller ($db) {
    $installer = new Installer;
    if (method_exists ('Joomla\CMS\Installer\Installer', 'setDatabase')) {
      $installer->setDatabase($db);
    }
    else {
      $installer = new JInstaller;
    }
    return $installer;
  }

}
