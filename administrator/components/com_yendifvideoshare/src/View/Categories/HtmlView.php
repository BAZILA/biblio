<?php
/**
 * @version    2.1.1
 * @package    Com_YendifVideoShare
 * @author     PluginsWare Interactive Pvt. Ltd <admin@yendifplayer.com>
 * @copyright  Copyright (c) 2012 - 2025 PluginsWare Interactive Pvt. Ltd. All Rights Reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace PluginsWare\Component\YendifVideoShare\Administrator\View\Categories;

// No direct access
\defined( '_JEXEC' ) or die;

use \Joomla\CMS\Form\Form;
use \Joomla\CMS\HTML\Helpers\Sidebar;
use \Joomla\CMS\Language\Text;
use \Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use \Joomla\CMS\Toolbar\Toolbar;
use \Joomla\CMS\Toolbar\ToolbarHelper;
use \Joomla\Component\Content\Administrator\Extension\ContentComponent;
use \PluginsWare\Component\YendifVideoShare\Site\Helper\YendifVideoShareHelper;

/**
 * View class for a list of categories.
 *
 * @since  2.0.0
 */
class HtmlView extends BaseHtmlView {

	protected $state;

	protected $items;

	protected $pagination;	

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
		$this->items = $this->get( 'Items' );
		$this->pagination = $this->get( 'Pagination' );

		if ( ! count( $this->items ) && $this->get( 'IsEmptyState' ) ) {
			$this->setLayout( 'emptystate' );
		}

		$this->filterForm = $this->get( 'FilterForm' );
		$this->activeFilters = $this->get( 'ActiveFilters' );

		// Check for errors
		if ( count( $errors = $this->get( 'Errors' ) ) ) {
			throw new \Exception( implode( "\n", $errors ) );
		}

		$this->addToolbar();

		$this->sidebar = Sidebar::render();
		parent::display( $tpl );
	}

	/**
	 * Add the page title and toolbar.
	 *
	 * @return  void
	 *
	 * @since   2.0.0
	 */
	protected function addToolbar()	{
		$state = $this->get( 'State' );
		$canDo = YendifVideoShareHelper::getActions();

		ToolbarHelper::title( Text::_( 'COM_YENDIFVIDEOSHARE_TITLE_CATEGORIES' ), 'jcc fa-folder-open' );

		$toolbar = Toolbar::getInstance( 'toolbar' );

		// Check if the form exists before showing the add/edit buttons
		$formPath = JPATH_COMPONENT_ADMINISTRATOR . '/src/View/Category';

		if ( file_exists( $formPath ) )	{
			if ( $canDo->{'core.create'} )	{
				$toolbar->addNew( 'category.add' );
			}
		}

		if ( $canDo->{'core.edit.state'} || count( $this->transitions ) )	{
			$dropdown = $toolbar->dropdownButton( 'status-group' )
				->text( 'JTOOLBAR_CHANGE_STATUS' )
				->toggleSplit( false )
				->icon( 'fas fa-ellipsis-h' )
				->buttonClass( 'btn btn-action' )
				->listCheck( true );

			$childBar = $dropdown->getChildToolbar();

			if ( isset( $this->items[0]->state ) ) {
				$childBar->publish( 'categories.publish' )->listCheck( true );
				$childBar->unpublish( 'categories.unpublish' )->listCheck( true );
				$childBar->archive( 'categories.archive' )->listCheck( true );
			} elseif ( isset( $this->items[0] ) ) {
				// If this component does not use state then show a direct delete button as we can not trash
				$toolbar->delete( 'categories.delete' )
					->text( 'JTOOLBAR_EMPTY_TRASH' )
					->message( 'JGLOBAL_CONFIRM_DELETE' )
					->listCheck( true );
			}

			if ( isset( $this->items[0]->checked_out ) ) {
				$childBar->checkin( 'categories.checkin' )->listCheck( true );
			}

			if ( isset( $this->items[0]->state ) ) {
				$childBar->trash( 'categories.trash' )->listCheck( true );
			}
		}		

		// Show trash and delete for components that uses the state field
		if ( isset( $this->items[0]->state ) ) {
			if ( $this->state->get( 'filter.state' ) == ContentComponent::CONDITION_TRASHED && $canDo->{'core.delete'} ) {
				$toolbar->delete( 'categories.delete' )
					->text( 'JTOOLBAR_EMPTY_TRASH' )
					->message( 'JGLOBAL_CONFIRM_DELETE' )
					->listCheck( true );
			}
		}

		if ( $canDo->{'core.admin'} ) {
			$toolbar->preferences( 'com_yendifvideoshare' );
		}

		// Set sidebar action
		Sidebar::setAction( 'index.php?option=com_yendifvideoshare&view=categories' );
	}
	
	/**
	 * Method to order fields 
	 *
	 * @return  void 
	 * @since   2.0.0
	 */
	protected function getSortFields() {
		return array(		
			'a.`id`' => Text::_( 'JGRID_HEADING_ID' ),	
			'a.`title`' => Text::_( 'JGLOBAL_TITLE' ),
			'a.`access`' => Text::_( 'JGRID_HEADING_ACCESS' ),			
			'a.`state`' => Text::_( 'JSTATUS' ),
			'a.`ordering`' => Text::_( 'JGRID_HEADING_ORDERING' )			
		);
	}

	/**
	 * Check if state is set
	 *
	 * @param   mixed  $state  State
	 *
	 * @return  bool
	 * 
	 * @since   2.0.0
	 */
	public function getState( $state ) {
		return isset( $this->state->{$state} ) ? $this->state->{$state} : false;
	}

}
