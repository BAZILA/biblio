<?php
/**
* @Copyright ((c) plasma-web.ru
        * @license    GPLv2 or later
        */

defined('_JEXEC') or die;
use Joomla\CMS\Factory;

header ("Content-type: text/html; charset=utf-8");

defined('QF3_PLUGIN_URL') or define('QF3_PLUGIN_URL', \Joomla\CMS\Uri\Uri::root().'components/com_qf3/');
defined('QF3_PLUGIN_DIR') or define('QF3_PLUGIN_DIR', JPATH_ROOT.'/components/com_qf3/');
defined('QF3_ADMIN_DIR') or define('QF3_ADMIN_DIR', JPATH_ROOT.'/administrator/components/com_qf3/');

$xml = simplexml_load_file(QF3_ADMIN_DIR .'qf3.xml');
defined('QF3_VERSION') or define('QF3_VERSION', (string) $xml->version);

Factory::getLanguage()->load('com_qf3', QF3_PLUGIN_DIR);
Factory::getLanguage()->load('custom', QF3_PLUGIN_DIR);

require_once(QF3_PLUGIN_DIR . 'classes.php');

if (isset($_POST['option']) && $_POST['option'] == 'com_qf3') {
	require_once(QF3_PLUGIN_DIR . 'controller.php');
	new QuickForm\controller();
}