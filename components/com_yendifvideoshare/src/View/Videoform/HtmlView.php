<?php
/**
 * @version    2.1.1
 * @package    Com_YendifVideoShare
 * @author     PluginsWare Interactive Pvt. Ltd <admin@yendifplayer.com>
 * @copyright  Copyright (c) 2012 - 2025 PluginsWare Interactive Pvt. Ltd. All Rights Reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace PluginsWare\Component\YendifVideoShare\Site\View\Videoform;

// No direct access
\defined( '_JEXEC' ) or die;

use \Joomla\CMS\Factory;
use \Joomla\CMS\Language\Text;
use \Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use \PluginsWare\Component\YendifVideoShare\Site\Helper\YendifVideoShareHelper;

/**
 * Frontpage Form class.
 *
 * @since  2.0.0
 */
class HtmlView extends BaseHtmlView {

	protected $state;

	protected $item;

	protected $form;

	protected $params;

	protected $canSave;

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
		$user = Factory::getUser();

		$this->state   = $this->get( 'State' );
		$this->item    = $this->get( 'Item' );
		$this->params  = YendifVideoShareHelper::resolveParams( $app->getParams( 'com_yendifvideoshare' ) );
		$this->canDo   = YendifVideoShareHelper::canDo();
		$this->canSave = $this->get( 'CanSave' );
		$this->form	   = $this->get( 'Form' );

		// Check for errors
		$errors = $this->get( 'Errors' );
		
		if ( count( $errors ) > 0 ) {
			for ( $i = 0, $n = count( $errors ); $i < $n; $i++ ) {
				if ( $errors[ $i ] instanceof \Exception ) {
					$app->enqueueMessage( $errors[ $i ]->getMessage(), 'error' );
				} else {
					$app->enqueueMessage( $errors[ $i ], 'error' );
				}
			}

			return false;
		}

		if ( $this->item && $this->item->userid != $user->id ) {
			$app->enqueueMessage( Text::_( 'COM_YENDIFVIDEOSHARE_NO_PERMISSION_EDIT' ), 'error' );
			$app->setHeader( 'status', 403, true );

			return false;
        }
        
		$this->_prepareDocument();

		parent::display( $tpl );
	}

	/**
	 * Prepares the document
	 *
	 * @return  void
	 *
	 * @since   2.0.0
	 * @throws  Exception
	 */
	protected function _prepareDocument() {
		$app = Factory::getApplication();
		
		$title = null;
		$form_title = ! empty( $this->item->id ) ? Text::_( 'COM_YENDIFVIDEOSHARE_EDIT_VIDEO_TITLE' ) : Text::_( 'COM_YENDIFVIDEOSHARE_ADD_VIDEO_TITLE' );
		$isMenuItem = 0;

		// Because the application sets a default page title,
		// we need to get it from the menu item itself
		$menus = $app->getMenu();	
		$menu = $menus->getActive();				

		if ( $menu ) {
			if ( $menu->link == 'index.php?option=com_yendifvideoshare&view=user&layout=add' ) {
				$isMenuItem = 1;

				$title = $this->params->get( 'page_title', $menu->title );
				$this->params->def( 'page_heading', $title );
			} else {
				$title = $form_title;
				$this->params->set( 'page_heading', $title );
			}
		} else {
			$title = $this->params->get( 'page_title', $form_title );
			$this->params->def( 'page_heading', $title );
		}

		if ( empty( $title ) ) {
			$title = $app->get( 'sitename' );
		} elseif ( $app->get( 'sitename_pagetitles', 0 ) == 1 )	{
			$title = Text::sprintf( 'JPAGETITLE', $app->get( 'sitename' ), $title );
		} elseif ( $app->get( 'sitename_pagetitles', 0 ) == 2 )	{
			$title = Text::sprintf( 'JPAGETITLE', $title, $app->get( 'sitename' ) );
		}

		$this->document->setTitle( $title );

		if ( $this->params->get( 'menu-meta_description' ) ) {
			$this->document->setDescription( $this->params->get( 'menu-meta_description' ) );
		}

		if ( $this->params->get( 'menu-meta_keywords' ) ) {
			$this->document->setMetadata( 'keywords', $this->params->get( 'menu-meta_keywords' ) );
		}

		if ( $this->params->get( 'robots' ) ) {
			$this->document->setMetadata( 'robots', $this->params->get( 'robots' ) );
		}

		// Add Breadcrumbs
		if ( ! $isMenuItem ) {
			$pathway = $app->getPathway();

			if ( ! in_array( $form_title, $pathway->getPathwayNames() ) ) {
				$pathway->addItem( $form_title );    
			}
		}
	}
	
}
