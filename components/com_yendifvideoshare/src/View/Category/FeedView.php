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

use \Joomla\CMS\Component\ComponentHelper;
use \Joomla\CMS\Document\Feed\FeedItem;
use \Joomla\CMS\Factory;
use \Joomla\CMS\Language\Text;
use \Joomla\CMS\MVC\View\AbstractView;
use \Joomla\CMS\Router\Route;
use \Joomla\CMS\Uri\Uri;
use \PluginsWare\Component\YendifVideoShare\Site\Helper\YendifVideoShareHelper;
use \PluginsWare\Component\YendifVideoShare\Site\Helper\YendifVideoShareRoute;

/**
 * Frontpage View class
 *
 * @since  2.0.0
 */
class FeedView extends AbstractView {

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

		$params = ComponentHelper::getParams( 'com_yendifvideoshare' );

        $category = YendifVideoShareHelper::getCategory( $app->input->getInt( 'id' ) );
		if ( ! $category ) {
            throw new \Exception( Text::_( 'JERROR_PAGE_NOT_FOUND' ), 404 );
        } 

		$siteEmail = $app->get( 'mailfrom' );
		$fromName  = $app->get( 'fromname' );
		$feedEmail = $app->get( 'feed_email', 'none' );

		$this->document->editor = $fromName;

		if ( $feedEmail !== 'none' ) {
			$this->document->editorEmail = $siteEmail;
		}

		$route = YendifVideoShareRoute::getCategoryRoute( $category, $params->get( 'itemid_category' ), 0, 'feed' );
		$this->document->link = Route::_( $route );

		// Get some data from the model
		$items = $this->get( 'Items' );

		foreach ( $items as $item ) {
			// Strip HTML from feed item title
			$title = htmlspecialchars( $item->title, ENT_QUOTES, 'UTF-8' );
			$title = html_entity_decode( $title, ENT_COMPAT, 'UTF-8' );

			// Strip HTML from feed item description text
			$description = $item->meta_description;

			if ( empty( $description ) ) {
				$description = $item->description;
			}

			if ( ! empty( $item->image ) ) {
				$description .= '<p><img src="' . YendifVideoShareHelper::getImage( $item ) . '" /></p>';
			}

			$description = '<div class="feed-description">' . $description . '</div>';

			$author = Factory::getUser( $item->userid );
			
			$date = $item->published_up ? $item->published_up : $item->created_date;
			$date = $date ? date( 'r', strtotime( $date ) ) : '';

			// Load individual item creator class
			$feeditem = new FeedItem;
			$feeditem->title       = $title;
			$feeditem->link        = '/index.php?option=com_yendifvideoshare&view=video&id=' . $item->id . ':' . $item->alias;
			$feeditem->description = $description;
			$feeditem->date        = $date;
			$feeditem->category    = $category->title;
			$feeditem->author      = $author->name;

			if  ( $feedEmail === 'site' ) {
				$feeditem->authorEmail = $siteEmail;
			}

			if ( $feedEmail === 'author' ) {
				$feeditem->authorEmail = $author->email;
			}

			// Loads item info into RSS array
			$this->document->addItem( $feeditem );
		}
	}

}
