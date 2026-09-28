<?php
/**
* @Copyright ((c) plasma-web.ru
        * @license    GPLv2 or later
        */

namespace QuickForm;
use Joomla\CMS\Factory;

\defined('QF3_VERSION') or die;

class historyModel extends baseModel
{
    public function __construct()
    {
        $this->closelink = 'historys';
        $this->savelink = 'historys&task=history.edit';
    }

    public function getItems()
    {
        $db = Factory::getDbo();
        $db->setQuery("SELECT * FROM #__qf3_ps WHERE id = " .(int) $this->get( 'id', $_GET ));
        return  $db->loadObject();
    }

    public function save()
    {
        $db = Factory::getDbo();

        $data = filter_input(INPUT_POST, 'qffield', FILTER_DEFAULT , FILTER_REQUIRE_ARRAY);
        $id = (int) $this->get('id', $_GET, 0);

        if(! $data['st_title']) {
          qf::errors ('The title is not filled in.');
          return $id;
        }

        $inputData = array(
          'st_title' 	=> '\''.addslashes(strip_tags($data['st_title'])).'\'',
          'id' 	=> (int) $id,
          'st_status' 	=> (int) $this->get( 'st_status', $data ),
          'st_desk'   => '\''.addslashes($this->get( 'st_desk', $data )).'\'',
         );

        if($id) {
            foreach ($inputData as $key => $value) {
                $updates[] = $key .' = '. $value;
            }
            $db->setQuery('UPDATE #__qf3_ps SET '.implode(', ', $updates). ' WHERE id='.(int) $inputData['id']);
            $db->execute();
            return $inputData['id'];
        }
    }

}
