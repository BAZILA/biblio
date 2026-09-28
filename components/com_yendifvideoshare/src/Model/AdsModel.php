<?php
/**
 * @version    2.1.1
 * @package    Com_YendifVideoShare
 * @author     PluginsWare Interactive Pvt. Ltd <admin@yendifplayer.com>
 * @copyright  Copyright (c) 2012 - 2025 PluginsWare Interactive Pvt. Ltd. All Rights Reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace PluginsWare\Component\YendifVideoShare\Site\Model;

// No direct access
\defined( '_JEXEC' ) or die;

use \Joomla\CMS\Factory;
use \Joomla\CMS\Language\Text;
use \Joomla\CMS\MVC\Model\BaseDatabaseModel;
use \Joomla\CMS\Uri\Uri;
use \Joomla\Database\ParameterType;
use \Joomla\Utilities\ArrayHelper;

/**
 * Class AdsModel.
 *
 * @since  2.0.0
 */
class AdsModel extends BaseDatabaseModel {

    public function getVideo() {	
        $app = Factory::getApplication();
        $db  = Factory::getDbo();        

        $item = new \stdClass();

        $id = $app->input->getInt( 'id' );

        if ( $id > 0 ) {
            $query = 'SELECT * FROM #__yendifvideoshare_videos WHERE id=' . $id;
            $db->setQuery( $query );
            $item = $db->loadObject();
        }
    
        return $item;   
    }

    public function getPrerollId() {	
        $db = Factory::getDbo();
				 
        $query = 'SELECT id FROM #__yendifvideoshare_adverts WHERE state=1 AND (type=' . $db->Quote( 'preroll' ) . ' OR type=' . $db->Quote( 'both' ) . ') ORDER BY RAND() LIMIT 1';
        $db->setQuery( $query );
        $id = $db->loadResult();
		
		return $id;  
    }

    public function getPostrollId() {	
        $db = Factory::getDbo();
				 
        $query = 'SELECT id FROM #__yendifvideoshare_adverts WHERE state=1 AND (type=' . $db->Quote( 'postroll' ) . ' OR type=' . $db->Quote( 'both' ) . ') ORDER BY RAND() LIMIT 1';
        $db->setQuery( $query );
        $id = $db->loadResult();
		
		return $id;  
    }

    public function getAd() {	
        $app = Factory::getApplication();
        $db = Factory::getDbo();

        $query = 'SELECT * FROM #__yendifvideoshare_adverts WHERE state=1 AND id=' . $app->input->getInt( 'id' );
        $db->setQuery( $query );
        $item = $db->loadObject();

        if ( ! empty( $item ) && ( strpos( $item->mp4, 'media/com_yendifvideoshare' ) !== false || strpos( $item->mp4, 'media/yendifvideoshare' ) !== false ) ) {
            $parts = explode( 'media/', $item->mp4 );
            $item->mp4 = URI::root() . 'media/' . $parts[1];
        }
    
        return $item;   
    }

}
