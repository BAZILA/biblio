<?php
/**
 * @version    2.1.1
 * @package    Com_YendifVideoShare
 * @author     PluginsWare Interactive Pvt. Ltd <admin@yendifplayer.com>
 * @copyright  Copyright (c) 2012 - 2025 PluginsWare Interactive Pvt. Ltd. All Rights Reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace PluginsWare\Component\YendifVideoShare\Site\Controller;

\defined( '_JEXEC' ) or die;

use \Joomla\CMS\Factory;
use \Joomla\CMS\Language\Text;
use \Joomla\CMS\MVC\Controller\BaseController;
use \Joomla\CMS\MVC\Factory\MVCFactoryInterface;

/**
 * AdController class.
 *
 * @since  2.0.0
 */
class AdController extends BaseController {
	
    public function impression() {
		$app = Factory::getApplication();
		$db = Factory::getDbo();

		$query = 'UPDATE #__yendifvideoshare_adverts SET impressions=impressions+1 WHERE id=' . $app->input->getInt( 'id' );
    	$db->setQuery( $query );
		$db->execute();
    }

    public function click() {
		$app = Factory::getApplication();	
		$db = Factory::getDbo();	
			
		$query = 'UPDATE #__yendifvideoshare_adverts SET clicks=clicks+1 WHERE id=' . $app->input->getInt( 'id' );
    	$db->setQuery( $query );
		$db->execute();		
	}
	
}
