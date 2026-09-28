<?php
/**
 * @Copyright ((c) plasma-web.ru
 * @license    GPLv2 or later
 */

namespace QuickForm;

\defined ('QF3_VERSION') or die;

class qf_config {
  public function getItems ($file) {
    if (file_exists ($file)) {
      $data = json_decode (include $file);
      foreach ($data as $k => &$v) {
        $v = htmlspecialchars_decode ($v, ENT_QUOTES);
      }
      return $data;
    }
  }

  protected function config ($xml, $file) {
    $xml = simplexml_load_file ($xml);
    $arr = $this->XMLToArray ($xml);

    $config = (array) $this->getItems ($file);
    if (! $config) {
      return $arr;
    }

    foreach ($arr as $key => $def) {
      if (! isset($config[$key])) {
        $config[$key] = $def;
      }
    }
    qf::gl ('key', qf::get ('cod', $config));

    return $config;
  }

  protected function XMLToArray ($xml) {
    $arr = array();
    foreach ($xml as $k => $v) {
      if ((is_object ($v) || is_array ($v)) && ($k != 'field')) {
        $new = $this->XMLToArray ($v);
        $arr = array_merge ($arr, $new);
      }
      elseif ($k == 'field') {
        $name = isset($v['name']) ? (string) $v['name'] : '';
        $def = isset($v['default']) ? (string) $v['default'] : '';
        if ($name) {
          $arr[$name] = $def;
        }
      }
    }
    return $arr;
  }

  public function getpath ($fname = 'settings') {
    return QF3_ADMIN_DIR . 'helpers/' . $fname . '.php';
  }

  public function get ($name = '', $fname = 'settings', $def = '') {
    if (! $cfg = qf::gl ($fname)) {
      $xml = QF3_ADMIN_DIR . 'forms/' . $fname . '.xml';
      $cfg = $this->config ($xml, $this->getpath ($fname));
      qf::gl ($fname, $cfg);
    }
    return $name ? qf::get ($name, $cfg, $def) : $cfg;
  }
}
