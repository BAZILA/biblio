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

use \Joomla\CMS\MVC\Controller\BaseController;

/**
 * YendifVideoShare master display controller.
 *
 * @since  2.0.0
 */
class DisplayController extends BaseController {

	/**
	 * The default view.
	 *
	 * @var    string
	 * @since  2.0.0
	 */
	protected $default_view = 'videos';

	/**
	 * Method to display a view.
	 *
	 * @param   boolean  $cachable   If true, the view output will be cached
	 * @param   array    $urlparams  An array of safe URL parameters and their variable types, for valid values see {@link InputFilter::clean()}.
	 *
	 * @return  BaseController|boolean  This object to support chaining.
	 *
	 * @since   2.0.0
	 */
	public function display( $cachable = false, $urlparams = array() ) {
		return parent::display();
	}
	
}
