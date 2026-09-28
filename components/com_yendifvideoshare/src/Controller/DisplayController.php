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
use \Joomla\CMS\Router\Route;
use \Joomla\CMS\Uri\Uri;

/**
 * Class DisplayController.
 *
 * @since  2.0.0
 */
class DisplayController extends BaseController {

	/**
	 * Constructor.
	 *
	 * @param  array                $config   An optional associative array of configuration settings.
	 * Recognized key values include 'name', 'default_task', 'model_path', and
	 * 'view_path' (this list is not meant to be comprehensive).
	 * @param  MVCFactoryInterface  $factory  The factory.
	 * @param  CMSApplication       $app      The JApplication for the dispatcher
	 * @param  Input                $input    Input
	 *
	 * @since  2.0.0
	 */
	public function __construct( $config = array(), MVCFactoryInterface $factory = null, $app = null, $input = null ) {
		parent::__construct( $config, $factory, $app, $input );
	}

	/**
	 * Method to display a view.
	 *
	 * @param   boolean  $cachable   If true, the view output will be cached.
	 * @param   boolean  $urlparams  An array of safe URL parameters and their variable types, for valid values see {@link InputFilter::clean()}.
	 *
	 * @return  \Joomla\CMS\MVC\Controller\BaseController  This object to support chaining.
	 *
	 * @since   2.0.0
	 */
	public function display( $cachable = false, $urlparams = false ) {
		$view = $this->input->getCmd( 'view', 'videos' );					
		
		switch ( $view ) {
			case 'category':
				$id = $this->input->getInt( 'id', 0 );
				if ( empty( $id ) ) {
					$view = 'categories';
				}
				break;
			case 'video':
				$id = $this->input->getInt( 'id', 0 );
				if ( empty( $id ) ) {
					$view = 'videos';
				}

				// Redirect our old embed URL to the latest
				$tmpl = $this->input->getCmd( 'tmpl', '' );
				if ( $tmpl == 'component' && $id > 0 ) {
					$this->setRedirect( Uri::root() . 'index.php?option=com_yendifvideoshare&view=player&id=' . $id . '&format=raw' );
					$this->redirect();	
				}
				break;
			case 'user':
			case 'videoform':			
				$user = Factory::getUser();

				if ( $user->guest ) {
					$this->setMessage( Text::_( 'COM_YENDIFVIDEOSHARE_USER_LOGIN_REQUIRED' ), 'info' );
					$this->setRedirect( Route::_( 'index.php?option=com_users&view=login&return=' . base64_encode( Uri::getInstance()->toString() ), false ) );

					$this->redirect();
				}

				$layout = $this->input->getCmd( 'layout', '' );
				if ( $layout == 'add' ) {
					$view = 'videoform';
				}
				break;
		}
		
		$this->input->set( 'view', $view );		

		parent::display( $cachable, $urlparams );
		return $this;
	}
	
}
