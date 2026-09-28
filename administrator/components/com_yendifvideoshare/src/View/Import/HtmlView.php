<?php
/**
 * @version    2.1.1
 * @package    Com_YendifVideoShare
 * @author     PluginsWare Interactive Pvt. Ltd <admin@yendifplayer.com>
 * @copyright  Copyright (c) 2012 - 2025 PluginsWare Interactive Pvt. Ltd. All Rights Reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace PluginsWare\Component\YendifVideoShare\Administrator\View\Import;

// No direct access
\defined( '_JEXEC' ) or die;

use \Joomla\CMS\Component\ComponentHelper;
use \Joomla\CMS\Factory;
use \Joomla\CMS\Language\Text;
use \Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use \Joomla\CMS\Toolbar\ToolbarHelper;
use \PluginsWare\Component\YendifVideoShare\Site\Helper\YendifVideoShareHelper;

/**
 * View class for a single Video.
 *
 * @since  2.0.0
 */
class HtmlView extends BaseHtmlView {

	protected $state;

	protected $item;

	protected $form;

	protected $params;

	/**
	 * Display the view
	 *
	 * @param   string  $tpl  Template name
	 *
	 * @return  void
	 *
	 * @since   2.0.0
	 * @throws  Exception
	 */
	public function display( $tpl = null ) {
		$this->state = $this->get( 'State' );
		$this->item  = $this->get( 'Item' );
		$this->form  = $this->get( 'Form' );
		$this->canDo = YendifVideoShareHelper::canDo();
		$this->params = ComponentHelper::getParams( 'com_yendifvideoshare' );	

		// Check for errors
		if ( count( $errors = $this->get( 'Errors' ) ) ) {
			throw new \Exception( implode( "\n", $errors ) );
		}

		$this->addToolbar();
		parent::display( $tpl );
	}

	/**
	 * Add the page title and toolbar.
	 *
	 * @return  void
	 *
	 * @since   2.0.0
	 * @throws  Exception
	 */
	protected function addToolbar()	{
		Factory::getApplication()->input->set( 'hidemainmenu', true );

		$user  = Factory::getUser();
		$isNew = ( $this->item->id == 0 );

		if ( isset( $this->item->checked_out ) ) {
			$checkedOut = ! ( $this->item->checked_out == 0 || $this->item->checked_out == $user->get( 'id' ) );
		} else {
			$checkedOut = false;
		}

		$canDo = YendifVideoShareHelper::getActions();

		$componentName = Text::_( 'COM_YENDIFVIDEOSHARE' );
		$title = $isNew ? Text::sprintf( 'COM_YENDIFVIDEOSHARE_IMPORT_ADD_TITLE', $componentName ) : Text::sprintf( 'COM_YENDIFVIDEOSHARE_IMPORT_EDIT_TITLE', $componentName );
		ToolbarHelper::title( $title , 'jcc fa-download' );

		// If not checked out, can save the item
		if ( ! $checkedOut && ( $canDo->{'core.edit'} || ( $canDo->{'core.create'} ) ) ) {
			if ( $isNew ) {
				ToolbarHelper::apply( 'import.apply', 'COM_YENDIFVIDEOSHARE_IMPORT_TOOLBAR_SAVE_IMPORT' );
			} else {
				if ( empty( $this->item->import_state ) ) {
					ToolbarHelper::apply( 'import.apply', 'COM_YENDIFVIDEOSHARE_IMPORT_TOOLBAR_SAVE_IMPORT' );
				} else {
					ToolbarHelper::apply( 'import.apply', 'COM_YENDIFVIDEOSHARE_IMPORT_TOOLBAR_SAVE_IMPORT_NEXT_BATCH' );
				}				
			}			
			
			ToolbarHelper::save( 'import.save', 'JTOOLBAR_SAVE' );
		}

		if ( empty( $this->item->id ) ) {
			ToolbarHelper::cancel( 'import.cancel', 'JTOOLBAR_CANCEL' );
		} else {
			ToolbarHelper::cancel( 'import.cancel', 'JTOOLBAR_CLOSE' );
		}
	}

}
