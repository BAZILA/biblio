<?php
/**
 * @Copyright ((c) plasma-web.ru
 * @license    GPLv2 or later
 */

namespace QuickForm;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\LanguageHelper;
use Joomla\CMS\HTML\HTMLHelper;

\defined ('_JEXEC') or die;

\defined ('QF3_PLUGIN_DIR') or define ('QF3_PLUGIN_DIR', dirname (__DIR__, 3) . '/components/com_qf3/');
\defined ('QF3_ADMIN_DIR') or define ('QF3_ADMIN_DIR', __DIR__ . '/');

$xml = simplexml_load_file (QF3_ADMIN_DIR . 'qf3.xml');
\defined ('QF3_VERSION') or define ('QF3_VERSION', (string) $xml->version);
Factory::getLanguage ()->load ('com_qf3', QF3_ADMIN_DIR);

require_once(QF3_PLUGIN_DIR . 'classes.php');

class qf_admin {
  protected $closelink;

  public function __construct () {
    require_once(QF3_ADMIN_DIR . 'src/controller.php');
    new controller ();
  }

  public function get ($name, $obj, $def = '') {
    if (is_array ($obj))
      return isset($obj[$name]) ? $obj[$name] : $def; // php < 7.2
    return isset($obj->$name) ? $obj->$name : $def;
  }

  public function addScript ($type, $file) {
    $tp = $type == 'css' ? 'stylesheet' : 'script';
    HTMLHelper::_ ($tp, 'administrator/components/com_qf3/assets/' . $file, array('version' => QF3_VERSION));
  }

  public function getLanguages () {
    $new[''] = 'Language';
    $languages = LanguageHelper::getLanguages ();
    foreach ($languages as $language) {
      $lang = str_replace ('-', '_', $language->lang_code);
      $new[$lang] = $language->title_native;
    }
    return $new;
  }

}

new qf_admin ();
