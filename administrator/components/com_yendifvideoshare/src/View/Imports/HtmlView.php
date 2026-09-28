<?php
/**
 * @version    2.1.1
 * @package    Com_YendifVideoShare
 * @author     PluginsWare Interactive Pvt. Ltd <admin@yendifplayer.com>
 * @copyright  Copyright (c) 2012 - 2025 PluginsWare Interactive Pvt. Ltd. All Rights Reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace PluginsWare\Component\YendifVideoShare\Administrator\View\Imports;

// No direct access
\defined( '_JEXEC' ) or die;

use \Joomla\CMS\Component\ComponentHelper;
use \Joomla\CMS\Factory;
use \Joomla\CMS\Form\Form;
use \Joomla\CMS\HTML\Helpers\Sidebar;
use \Joomla\CMS\Language\Text;
use \Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use \Joomla\CMS\Router\Route;
use \Joomla\CMS\Toolbar\Toolbar;
use \Joomla\CMS\Toolbar\ToolbarHelper;
use \Joomla\CMS\Uri\Uri;
use \Joomla\Component\Content\Administrator\Extension\ContentComponent;
use \PluginsWare\Component\YendifVideoShare\Site\Helper\YendifVideoShareHelper;

/**
 * View class for a list of imports.
 *
 * @since  2.0.0
 */
class HtmlView extends BaseHtmlView {

	protected $state;

	protected $items;

	protected $pagination;	

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
		$app = Factory::getApplication();

		$this->state = $this->get( 'State' );
		$this->items = $this->get( 'Items' );
		$this->pagination = $this->get( 'Pagination' );
		$this->params = ComponentHelper::getParams( 'com_yendifvideoshare' );
		$this->canDo = YendifVideoShareHelper::canDo();

		if ( $this->canDo ) {
			if ( empty( $this->params->get( 'youtube_api_key' ) ) ) {
				$app->enqueueMessage(
					Text::_( 'COM_YENDIFVIDEOSHARE_YOUTUBE_API_KEY_REQUIRED' ),
					'error'
				);
				
				$app->redirect( 'index.php?option=com_config&view=component&component=com_yendifvideoshare&return=' . base64_encode( Uri::getInstance()->toString() ) );
			}

			if ( ! count( $this->items ) && $this->get( 'IsEmptyState' ) ) {
				$this->setLayout( 'emptystate' );
			}	
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
	protected function addToolbar() {		
		$state = $this->get( 'State' );
		$canDo = YendifVideoShareHelper::getActions();

		ToolbarHelper::title( Text::_( 'COM_YENDIFVIDEOSHARE_TITLE_IMPORTS' ), 'jcc fa-download' );

		$toolbar = Toolbar::getInstance( 'toolbar' );

		if ( $this->canDo ) {
			// Check if the form exists before showing the add/edit buttons
			$formPath = JPATH_COMPONENT_ADMINISTRATOR . '/src/View/Import';

			if ( file_exists( $formPath ) )	{
				if ( $canDo->{'core.create'} )	{
					$toolbar->addNew( 'import.add' );
				}
			}

			if ( $canDo->{'core.edit.state'} || count( $this->transitions ) ) {
				$dropdown = $toolbar->dropdownButton( 'status-group' )
					->text( 'JTOOLBAR_CHANGE_STATUS' )
					->toggleSplit( false )
					->icon( 'fas fa-ellipsis-h' )
					->buttonClass( 'btn btn-action' )
					->listCheck( true );

				$childBar = $dropdown->getChildToolbar();

				if ( isset( $this->items[0]->state ) ) {
					$childBar->publish( 'imports.publish' )->listCheck( true );
					$childBar->unpublish( 'imports.unpublish' )->listCheck( true );

					if ( $canDo->{'core.delete'} ) {
						$toolbar->delete( 'imports.delete' )
							->text( 'JTOOLBAR_DELETE' )
							->message( 'JGLOBAL_CONFIRM_DELETE' )
							->listCheck( true );
					}
				}

				if ( isset( $this->items[0]->checked_out ) ) {
					$childBar->checkin( 'imports.checkin' )->listCheck( true );
				}
			}		
		}

		if ( $canDo->{'core.admin'} ) {
			$toolbar->preferences( 'com_yendifvideoshare' );
		}

		// Set sidebar action
		Sidebar::setAction( 'index.php?option=com_yendifvideoshare&view=imports' );
	}
	
	/**
	 * Method to order fields 
	 *
	 * @return  void 
	 * 
	 * @since   2.0.0
	 */
	protected function getSortFields() {
		return array(			
			'a.`id`' => Text::_( 'JGRID_HEADING_ID' ),
			'a.`title`' => Text::_( 'JGLOBAL_TITLE' ),
			'a.`type`' => Text::_( 'COM_YENDIFVIDEOSHARE_IMPORTS_TYPE' ),
			'a.`import_state`' => Text::_( 'JSTATUS' ),
			'a.`state`' => Text::_( 'COM_YENDIFVIDEOSHARE_IMPORTS_STATE' ),
			'a.`next_import_date`' => Text::_( 'COM_YENDIFVIDEOSHARE_IMPORTS_NEXT_IMPORT_DATE' ),
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
