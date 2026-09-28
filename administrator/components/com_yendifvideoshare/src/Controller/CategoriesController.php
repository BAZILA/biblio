<?php
/**
 * @version    2.1.1
 * @package    Com_YendifVideoShare
 * @author     PluginsWare Interactive Pvt. Ltd <admin@yendifplayer.com>
 * @copyright  Copyright (c) 2012 - 2025 PluginsWare Interactive Pvt. Ltd. All Rights Reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace PluginsWare\Component\YendifVideoShare\Administrator\Controller;

\defined( '_JEXEC' ) or die;

use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Controller\AdminController;
use Joomla\Utilities\ArrayHelper;

/**
 * Class CategoriesController.
 *
 * @since  2.0.0
 */
class CategoriesController extends AdminController {

	/**
	 * Proxy for getModel.
	 *
	 * @param   string  $name    Optional. Model name
	 * @param   string  $prefix  Optional. Class prefix
	 * @param   array   $config  Optional. Configuration array for model
	 *
	 * @return  object	The Model
	 *
	 * @since   2.0.0
	 */
	public function getModel( $name = 'Category', $prefix = 'Administrator', $config = array() ) {
		return parent::getModel( $name, $prefix, array( 'ignore_request' => true ) );
	}	

	/**
	 * Method to save the submitted ordering values for records via AJAX.
	 *
	 * @return  void
	 *
	 * @since   2.0.0
	 * @throws  Exception
	 */
	public function saveOrderAjax()	{
		// Get the input
		$input = Factory::getApplication()->input;
		$pks   = $input->post->get( 'cid', array(), 'array' );
		$order = $input->post->get( 'order', array(), 'array' );

		// Sanitize the input
		ArrayHelper::toInteger( $pks );
		ArrayHelper::toInteger( $order );

		// Get the model
		$model = $this->getModel();

		// Save the ordering
		$return = $model->saveorder( $pks, $order );

		if ( $return ) {
			echo '1';
		}

		// Close the application
		Factory::getApplication()->close();
	}
	
}
