<?php
/**
* @Copyright ((c) plasma-web.ru
        * @license    GPLv2 or later
        */

namespace QuickForm;
use Joomla\CMS\Factory;

\defined('QF3_VERSION') or die;

class baseModel extends qf_admin
{
    public $closelink;
    public $savelink;
    public $savecreatelink;

    public function filterdir() {
        $col = qf::gettask('col');
        $ses = qf::ses()->get('quickform', []);
        $filter = $this->get('filterdir', $ses, []);
        $order = $this->get('order', $filter, 'id');

        if($col == $order) {
            $dir = $this->get('dir', $filter) === 'asc' ? 'desc' : 'asc';
            $ses['filterdir']['dir'] = $dir;
            qf::ses()->set('quickform', $ses);
        }
        else {
          $ses['filterdir']['order'] =  $col;
          qf::ses()->set('quickform', $ses);
        }

    }

    public function filterlist($page)
    {
        $col = filter_input(INPUT_POST, 'col', FILTER_SANITIZE_STRING);
        $v = filter_input(INPUT_POST, 'v', FILTER_SANITIZE_FULL_SPECIAL_CHARS);

        if ($col) {
          $ses = qf::ses()->get('quickform', []);
          $ses['filterlist'][$page.'.'.$col] = $v;
          qf::ses()->set('quickform', $ses);
        }
    }

    public function publish($page)
    {
        $db = Factory::getDbo();

        if($page == 'projects') {
            $db->setQuery( 'UPDATE #__qf3_projects SET published = 1 - published WHERE id=' .(int) $_GET['id'] );
            $db->execute();
        }
    }

    public function activate($page)
    {
        $db = Factory::getDbo();

        $new_id = $this->checkcid();
        if($new_id && $page == 'projects') {
            $db->setQuery( 'UPDATE #__qf3_projects SET published = 1 WHERE id IN ('.implode(',', $new_id).')' );
            $db->execute();
            $res = $db->getAffectedRows();
            if($res) qf::messages ($res . ' ' . 'QF_N_ITEMS_PUBLISHED');
            elseif($res === false) qf::errors ('QF_ERR_DATABASE');
        }
    }

    public function deactivate($page)
    {
        $db = Factory::getDbo();

        $new_id = $this->checkcid();
        if($new_id && $page == 'projects') {
            $db->setQuery( 'UPDATE #__qf3_projects SET published = 0 WHERE id IN ('.implode(',', $new_id).')' );
            $db->execute();
            $res = $db->getAffectedRows();
            if($res) qf::messages ($res . ' ' . 'QF_N_ITEMS_UNPUBLISHED');
            elseif($res === false) qf::errors ('QF_ERR_DATABASE');
        }
    }

    public function delete($page)
    {
        $db = Factory::getDbo();

        $new_id = $this->checkcid();
        if($new_id) {
            $db->setQuery( 'DELETE FROM #__qf3_'.$page.' WHERE id IN ('.implode(',', $new_id).')' );
            $db->execute();
            $res = $db->getAffectedRows();
            if($res) qf::messages ($res . ' ' . 'QF_N_ITEMS_DELETED');
            elseif($res === false) qf::errors ('QF_ERR_DATABASE');
        }
    }

    protected function checkcid()
    {
        return array_diff(filter_input(INPUT_POST, 'cid', FILTER_SANITIZE_NUMBER_INT , FILTER_REQUIRE_ARRAY), array(''));
    }

    public function help()
    {
        echo Text::_('QF_HELP');
    }

    protected function record ($page, $inputData) {
      if ($inputData['id']) {
        foreach ($inputData as $key => $value) {
          $updates[] = $key . ' = ' . $value;
        }
        qf::db ()->setQuery ('UPDATE #__qf3_'.$page.' SET ' . implode (', ', $updates) . ' WHERE id=' . (int) $inputData['id']);
        qf::db ()->execute ();
        return $inputData['id'];
      }
      else {
        qf::db ()->setQuery ('INSERT INTO #__qf3_'.$page.' (' . implode (',', array_keys ($inputData)) . ') VALUES (' . implode (',', array_values ($inputData)) . ')');
        qf::db ()->execute ();
        return qf::db ()->insertid ();
      }
    }
  

}
