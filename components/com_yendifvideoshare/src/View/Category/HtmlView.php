<?php
/**
 * @version    2.1.1
 * @package    Com_YendifVideoShare
 * @author     PluginsWare Interactive Pvt. Ltd <admin@yendifplayer.com>
 * @copyright  Copyright (c) 2012 - 2025 PluginsWare Interactive Pvt. Ltd. All Rights Reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace PluginsWare\Component\YendifVideoShare\Site\View\Category;

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

	protected $params;

	protected $category;	

	protected $items;

	protected $pagination;	

	protected $subCategories;

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

		$this->state = $this->get( 'State' );		
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

		$this->category = $this->get( 'Category' );

		if ( ! $this->category ) {
			$app->enqueueMessage( Text::_( 'COM_YENDIFVIDEOSHARE_NO_ITEMS_FOUND' ), 'error' );
			$app->setHeader( 'status', 404, true );

			return false;
        } 

		if ( ! empty( $this->category->access ) && ! in_array( $this->category->access, $user->getAuthorisedViewLevels() ) ) {
			$app->enqueueMessage( Text::_( 'COM_YENDIFVIDEOSHARE_NO_PERMISSION_VIEW' ), 'error' );
			$app->setHeader( 'status', 403, true );
			
			return false;
        } 

		$this->items = $this->get( 'Items' );
		$this->pagination = $this->get( 'Pagination' );
		$this->subCategories = $this->get( 'CategoriesByParent' );
		
		if ( empty( $this->items ) && empty( $this->category->description ) && empty( $this->subCategories ) ) {
			$app->enqueueMessage( Text::_( 'COM_YENDIFVIDEOSHARE_NO_ITEMS_FOUND' ), 'error' );
			$app->setHeader( 'status', 404, true );

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
		$meta_description = null;
		$meta_keywords = null;

		$isMenuItem = 0;

		// Because the application sets a default page title,
		// we need to get it from the menu item itself
		$menus = $app->getMenu();
		$menu = $menus->getActive();				

		if ( $menu ) {
			$meta_description = $this->params->get( 'menu-meta_description', $this->category->meta_description );
			$meta_keywords = $this->params->get( 'menu-meta_keywords', $this->category->meta_keywords );

			if ( $menu->link == 'index.php?option=com_yendifvideoshare&view=category&id=' . $this->category->id ) {
				$isMenuItem = 1;

				$title = $this->params->get( 'page_title', $menu->title );
				$this->params->def( 'page_heading', $title );				
			} else {
				$title = $this->category->title;
				$this->params->set( 'page_heading', $title );

				if ( ! empty( $this->category->meta_description ) ) {
					$meta_description = $this->category->meta_description;
				}

				if ( ! empty( $this->category->meta_keywords ) ) {
					$meta_keywords = $this->category->meta_keywords;
				}
			}			
		} else {
			$title = $this->params->get( 'page_title', $this->category->title );
			$this->params->def( 'page_heading', $title );

			$meta_description = $this->category->meta_description;
			$meta_keywords = $this->category->meta_keywords;
		}

		if ( empty( $title ) ) {
			$title = $app->get( 'sitename' );
		} elseif ($app->get( 'sitename_pagetitles', 0 ) == 1 ) {
			$title = Text::sprintf( 'JPAGETITLE', $app->get( 'sitename' ), $title );
		} elseif ($app->get( 'sitename_pagetitles', 0 ) == 2 ) {
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
		if ( $fbAppId = $this->params->get( 'fb_app_id' ) ) {
			$this->document->addCustomTag( '<meta property="fb:app_id" content="' . $fbAppId . '">' );
		}

		$this->document->addCustomTag( '<meta property="og:site_name" content="' . $app->get( 'sitename' ) . '" />' );

		$pageURL = Route::_( 'index.php?option=com_yendifvideoshare&view=category&id=' . $this->category->id . ':' . $this->category->alias, true, 0, true );
		$this->document->addCustomTag( '<meta property="og:url" content="' . $pageURL . '" />' );

		$this->document->addCustomTag( '<meta property="og:type" content="article" />' );
		$this->document->addCustomTag( '<meta property="og:title" content="' . $this->category->title . '" />' );

		$description = $meta_description;
		if ( empty( $description ) && ! empty( $this->category->description ) ) {
			$description = YendifVideoShareHelper::Truncate( $this->category->description );
			$description = str_replace( '...', '', $description );
		}

		if ( ! empty( $description ) ) {
			$this->document->addCustomTag( '<meta property="og:description" content="' . $description . '" />' );
		}

		if ( ! empty( $this->category->image ) ) {
			$this->document->addCustomTag( '<meta property="og:image" content="' . YendifVideoShareHelper::getImage( $this->category ) . '" />' );
		}

		$this->document->addCustomTag( '<meta name="twitter:card" content="summary">' );
		$this->document->addCustomTag( '<meta name="twitter:title" content="' . $this->category->title . '">' );

		if ( ! empty( $description ) ) {
			$this->document->addCustomTag( '<meta property="twitter:description" content="' . $description . '" />' );
		}
		
		if ( ! empty( $this->category->image ) ) {
			$this->document->addCustomTag( '<meta property="twitter:image" content="' . YendifVideoShareHelper::getImage( $this->category ) . '" />' );
		}
		
		// Add Breadcrumbs
		if ( ! $isMenuItem ) {
			$model   = $this->getModel();
			$pathway = $app->getPathway();

			$parent = $this->category->parent;

			while ( $parent != 0 ) {
				if ( $category = $model->getCategory( $parent ) ) {
					if ( ! in_array( $category->title, $pathway->getPathwayNames() ) ) {
						$pathway->addItem( $category->title, 'index.php?option=com_yendifvideoshare&view=category&id=' . $category->id . ':' . $category->alias );
					}

					$parent = $category->parent;
				} else {
					$parent = 0;
				}
			}

			if ( ! in_array( $this->category->title, $pathway->getPathwayNames() ) ) {
				$pathway->addItem( $this->category->title );    
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
