<?php
/**
 * @Copyright ((c) plasma-web.ru
 * @license    GPLv2 or later
 */

namespace QuickForm;

use Joomla\CMS\Factory;
use Joomla\CMS\Version;
use Joomla\CMS\Language\Text as JText;
use Joomla\CMS\HTML\HTMLHelper;

\defined ('QF3_VERSION') or die;

class Text {
  public static function _ ($text) {
    return JText::_ ($text);
  }

  public static function translate ($text) {
    return preg_replace_callback ('/\b(QF_\w+)\b/', function ($m) {
      return self::_ ($m[1]);
    }, $text);
  }
}


class qf {
  public static function form ($id) {
    require_once(QF3_PLUGIN_DIR . 'classes/buildform.php');
    return (new QuickForm ())->getQuickForm ($id);
  }

  public static function cart ($headonly) {
    require_once(QF3_PLUGIN_DIR . 'classes/buildform.php');
    return (new QuickForm ())->getShopModule ($headonly);
  }

  public static function conf () {
    require_once QF3_ADMIN_DIR . 'helpers/config.php';
    return new qf_config ();
  }

  public static function ses () {
    return Factory::getSession ();
  }

  public static function user () {
    return Factory::getUser ();
  }

	public static function db () {
		return Factory::getDbo ();
	}

  public static function cmsVersion () {
    $version = new Version ();
    return 'j' . $version->getShortVersion ()[0];
  }

  public static function addScript ($type, $file) {
    $tp = $type == 'css' ? 'stylesheet' : 'script';
    HTMLHelper::_ ($tp, 'components/com_qf3/assets/' . $file, array('version' => QF3_VERSION));
  }

  public static function getlang () {
    $lang = Factory::getLanguage ()->getTag ();
    return str_replace ('-', '_', $lang);
  }

  public static function getacs () {
    $acs[''] = 'QF_ACCESS';
    $groups = HTMLHelper::_ ('access.assetgroups');
    foreach ($groups as $group) {
      $acs[$group->value] = $group->text;
    }
    return $acs;
  }

  public static function gettask ($var = 'task', $def = '') {
    if (isset($_REQUEST[$var])) {
      return preg_replace ('/[^a-z.\d\-_]/i', '', $_REQUEST[$var]);
    }
    return $def;
  }

  public static function gl ($k, $v = false) {
    if ($v !== false) {
      $GLOBALS['qf3'][$k] = $v;
    }
    else {
      return isset($GLOBALS['qf3'][$k]) ? $GLOBALS['qf3'][$k] : '';
    }
  }

  public static function errors ($v = false) {
    static $errors = [];
    if ($v === false) {
      return $errors;
    }
    elseif ($v) {
      $errors[] = htmlspecialchars( $v);
    }
  }

  public static function messages ( $v = false ) {
		static $messages = [];
		if ( $v === false ) {
			return $messages;
		}
    elseif ( $v ) {
			$messages[] = htmlspecialchars( $v);
		}
	}


  public static function getUrl () {
    return ((! empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
  }

  public static function formatPrice ($field, $math, $label = '') {
    $arr = [0 => [',', ' '], 1 => ['.', ','], 2 => ['.', '']];
    $ar = $arr[(int) self::get ('format', $field, 0)];
    $math = number_format ($math, (int) self::get ('fixed', $field, 0), $ar[0], $ar[1]);

    $label = $label ? '<span>' . $label . '</span>' : '';
    $math = '<span class="adderprice">' . $math . '</span>';
    $unit = '<span class="adderunit">' . self::get ('unit', $field) . '</span>';

    return $label . (self::get ('pos', $field) ? $math . ' ' . $unit : $unit . ' ' . $math);
  }

  public static function get ($v, $obj, $def = '') {
    $obj = (object) $obj;
    if (! isset($obj->$v)) {
      if (isset($obj->custom) && strpos ($obj->custom, $v) !== false) {
        $pattern = "/" . $v . "\s*=\s*[\"]([^\"]*)[\"]\s?/i";
        preg_match ($pattern, $obj->custom, $m);
        if (isset($m[1])) {
          return $m[1];
        }
        else {
          $subject = preg_replace ("/\s*=\s*[\"]([^\"]*)[\"]\s?/i", '', $obj->custom);
          if (strpos ($subject, $v) !== false) {
            return true;
          }
          else {
            return $def;
          }
        }
      }
      return $def;
    }
    return $obj->$v;
  }


}