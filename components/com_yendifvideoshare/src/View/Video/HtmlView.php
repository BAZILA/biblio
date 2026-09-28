<?php
/**
 * @version    2.1.1
 * @package    Com_YendifVideoShare
 * @author     PluginsWare Interactive Pvt. Ltd <admin@yendifplayer.com>
 * @copyright  Copyright (c) 2012 - 2025 PluginsWare Interactive Pvt. Ltd. All Rights Reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace PluginsWare\Component\YendifVideoShare\Site\View\Video;

// No direct access
\defined( '_JEXEC' ) or die;

use \Joomla\CMS\Factory;
use \Joomla\CMS\Language\Text;
use \Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use \Joomla\CMS\Router\Route;
use \Joomla\CMS\Uri\Uri;
use \PluginsWare\Component\YendifVideoShare\Site\Helper\YendifVideoShareHelper;
use \PluginsWare\Component\YendifVideoShare\Site\Helper\YendifVideoShareRoute;

/**
 * Frontpage View class.
 *
 * @since  2.0.0
 */
class HtmlView extends BaseHtmlView {

	protected $state;

	protected $video;

	protected $items;

	protected $pagination;	

	protected $params;

	protected $hasAccess;

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

		$this->state  = $this->get( 'State' );
		$this->video  = $this->get( 'Video' );
		$this->canDo  = YendifVideoShareHelper::canDo();
		$this->params = YendifVideoShareHelper::resolveParams( $app->getParams( 'com_yendifvideoshare' ) );		

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

		if ( ! $this->video ) {
			$app->enqueueMessage( Text::_( 'COM_YENDIFVIDEOSHARE_NO_ITEMS_FOUND' ), 'error' );
			$app->setHeader( 'status', 404, true );

			return false;
        } 

		$this->hasAccess = 1;
		if ( ! empty( $this->video->access ) && ! in_array( $this->video->access, $user->getAuthorisedViewLevels() ) && $this->video->userid != $user->id ) {
			$this->hasAccess = 0;
        }      
		
		if ( $this->params->get( 'show_related' ) ) {
			$this->items = $this->get( 'Items' );
			$this->pagination = $this->get( 'Pagination' );
		}

		$this->params->set( 'show_page_heading', $this->params->get( 'show_title' ) );
		
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
		$meta_description = null;
		$meta_keywords = null;

		$isMenuItem = 0;

		// Because the application sets a default page title,
		// We need to get it from the menu item itself
		$menus = $app->getMenu();	
		$menu = $menus->getActive();			

		if ( $menu ) {
			$meta_description = $this->params->get( 'menu-meta_description', $this->video->meta_description );
			$meta_keywords = $this->params->get( 'menu-meta_keywords', $this->video->meta_keywords );

			if ( $menu->link == 'index.php?option=com_yendifvideoshare&view=video&id=' . $this->video->id ) {
				$isMenuItem = 1;

				$title = $this->params->get( 'page_title', $menu->title );
				$this->params->def( 'page_heading', $title );
			} else {
				$title = $this->video->title;
				$this->params->set( 'page_heading', $title );

				if ( ! empty( $this->video->meta_description ) ) {
					$meta_description = $this->video->meta_description;
				}

				if ( ! empty( $this->video->meta_keywords ) ) {
					$meta_keywords = $this->video->meta_keywords;
				}
			}
		} else {
			$title = $this->params->get( 'page_title', $this->video->title );
			$this->params->def( 'page_heading', $title );

			$meta_description = $this->video->meta_description;
			$meta_keywords = $this->video->meta_keywords;
		}

		if ( empty( $title ) ) {
			$title = $app->get( 'sitename' );
		} elseif ( $app->get( 'sitename_pagetitles', 0 ) == 1 )	{
			$title = Text::sprintf( 'JPAGETITLE', $app->get( 'sitename' ), $title );
		} elseif ( $app->get( 'sitename_pagetitles', 0 ) == 2 )	{
			$title = Text::sprintf( 'JPAGETITLE', $title, $app->get( 'sitename' ) );
		}

		$this->document->setTitle( $title );

		if ( ! empty( $meta_description ) ) {
			$this->document->setDescription( $meta_description );
		}

		if ( ! empty( $meta_keywords ) ) {
			$this->document->setMetadata( 'keywords', $meta_keywords );
		}

		if ( $this->params->get( 'robots' ) ) {
			$this->document->setMetadata( 'robots', $this->params->get( 'robots' ) );
		}

		// Add Custom Tags
		if ( $this->params->get( 'comments' ) == 'facebook' ) {
			$language = str_replace( '-', '_', Factory::getLanguage()->getTag() );
			$this->document->addCustomTag( '<script async defer crossorigin="anonymous" src="https://connect.facebook.net/' . $language . '/sdk.js#xfbml=1&version=v16.0" nonce="' . $app->get( 'csp_nonce' ) . '"></script>' );
		}

		if ( $fbAppId = $this->params->get( 'fb_app_id' ) ) {
			$this->document->addCustomTag( '<meta property="fb:app_id" content="' . $fbAppId . '">' );
		}

		$this->document->addCustomTag( '<meta property="og:site_name" content="' . $app->get( 'sitename' ) . '" />' );

		$pageURL = Route::_( 'index.php?option=com_yendifvideoshare&view=video&id=' . $this->video->id . ':' . $this->video->alias, true, 0, true );
		$this->document->addCustomTag( '<meta property="og:url" content="' . $pageURL . '" />' );

		$this->document->addCustomTag( '<meta property="og:type" content="video" />' );
		$this->document->addCustomTag( '<meta property="og:title" content="' . $this->video->title . '" />' );

		$description = $meta_description;
		if ( empty( $description ) && ! empty( $this->video->description ) ) {
			$description = YendifVideoShareHelper::Truncate( $this->video->description );
			$description = str_replace( '...', '', $description );
		}

		if ( ! empty( $description ) ) {
			$this->document->addCustomTag( '<meta property="og:description" content="' . $description . '" />' );
		}

		if ( ! empty( $this->video->image ) ) {
			$this->document->addCustomTag( '<meta property="og:image" content="' . YendifVideoShareHelper::getImage( $this->video ) . '" />' );
		}

		$videoURL = Uri::root() . 'index.php?option=com_yendifvideoshare&view=player&id=' . $this->video->id . '&format=raw';
		$this->document->addCustomTag( '<meta property="og:video:url" content="' . $videoURL . '" />' );

		if ( stripos( $pageURL, 'https://' ) === 0 ) {
			$this->document->addCustomTag( '<meta property="og:video:secure_url" content="' . $videoURL . '" />' );
		}

		$this->document->addCustomTag( '<meta property="og:video:type" content="text/html">' );
		$this->document->addCustomTag( '<meta property="og:video:width" content="1280">' );
		$this->document->addCustomTag( '<meta property="og:video:height" content="720">' );

		$this->document->addCustomTag( '<meta name="twitter:card" content="summary">' );
		$this->document->addCustomTag( '<meta name="twitter:title" content="' . $this->video->title . '">' );

		if ( ! empty( $description ) ) {
			$this->document->addCustomTag( '<meta property="twitter:description" content="' . $description . '" />' );
		}
		
		if ( ! empty( $this->video->image ) ) {
			$this->document->addCustomTag( '<meta property="twitter:image" content="' . YendifVideoShareHelper::getImage( $this->video ) . '" />' );
		}

		// Add Breadcrumbs
		if ( ! $isMenuItem ) {
			$pathway = $app->getPathway();
				
			if ( $this->video->category && ! in_array( $this->video->category->title, $pathway->getPathwayNames() ) ) {
				$pathway->addItem( $this->video->category->title, 'index.php?option=com_yendifvideoshare&view=category&id=' . $this->video->category->id . ':' . $this->video->category->alias );
			}

			if ( ! in_array( $this->video->title, $pathway->getPathwayNames() ) ) {
				$pathway->addItem( $this->video->title );    
			}
		}
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
