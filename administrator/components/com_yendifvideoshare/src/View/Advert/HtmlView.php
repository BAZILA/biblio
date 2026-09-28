<?php
/**
 * @version    2.1.1
 * @package    Com_YendifVideoShare
 * @author     PluginsWare Interactive Pvt. Ltd <admin@yendifplayer.com>
 * @copyright  Copyright (c) 2012 - 2025 PluginsWare Interactive Pvt. Ltd. All Rights Reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace PluginsWare\Component\YendifVideoShare\Administrator\View\Advert;

// No direct access
defined( '_JEXEC' ) or die;

use \Joomla\CMS\Factory;
use \Joomla\CMS\Language\Text;
use \Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use \Joomla\CMS\Toolbar\ToolbarHelper;
use \PluginsWare\Component\YendifVideoShare\Site\Helper\YendifVideoShareHelper;

/**
 * View class for a single advert.
 *
 * @since  2.0.0
 */
class HtmlView extends BaseHtmlView {

	protected $state;

	protected $item;

	protected $form;

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
		$title = $isNew ? Text::sprintf( 'COM_YENDIFVIDEOSHARE_ADVERT_ADD_TITLE', $componentName ) : Text::sprintf( 'COM_YENDIFVIDEOSHARE_ADVERT_EDIT_TITLE', $componentName );
		ToolbarHelper::title( $title, 'jcc fa-bullhorn' );

		// If not checked out, can save the item
		if ( ! $checkedOut && ( $canDo->{'core.edit'} || ( $canDo->{'core.create'} ) ) ) {
			ToolbarHelper::apply( 'advert.apply', 'JTOOLBAR_APPLY' );
			ToolbarHelper::save( 'advert.save', 'JTOOLBAR_SAVE' );
		}

		if ( ! $checkedOut && ( $canDo->{'core.create'} ) ) {
			ToolbarHelper::custom( 'advert.save2new', 'save-new.png', 'save-new_f2.png', 'JTOOLBAR_SAVE_AND_NEW', false );
		}

		// If an existing item, can save to a copy
		if ( ! $isNew && $canDo->{'core.create'} )	{
			ToolbarHelper::custom( 'advert.save2copy', 'save-copy.png', 'save-copy_f2.png', 'JTOOLBAR_SAVE_AS_COPY', false );
		}

		if ( empty( $this->item->id ) )	{
			ToolbarHelper::cancel( 'advert.cancel', 'JTOOLBAR_CANCEL' );
		} else {
			ToolbarHelper::cancel( 'advert.cancel', 'JTOOLBAR_CLOSE' );
		}
	}
	
}
