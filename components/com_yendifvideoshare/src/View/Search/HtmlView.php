<?php
/**
 * @version    2.1.1
 * @package    Com_YendifVideoShare
 * @author     PluginsWare Interactive Pvt. Ltd <admin@yendifplayer.com>
 * @copyright  Copyright (c) 2012 - 2025 PluginsWare Interactive Pvt. Ltd. All Rights Reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace PluginsWare\Component\YendifVideoShare\Site\View\Search;

// No direct access
\defined( '_JEXEC' ) or die;

use \Joomla\CMS\Factory;
use \Joomla\CMS\Language\Text;
use \Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use \PluginsWare\Component\YendifVideoShare\Site\Helper\YendifVideoShareHelper;

/**
 * Frontpage View class.
 *
 * @since  2.0.0
 */
class HtmlView extends BaseHtmlView {

	protected $state;

	protected $items;

	protected $search;

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
		$this->params = YendifVideoShareHelper::resolveParams( $app->getParams( 'com_yendifvideoshare' ) );		

		$this->pagination->setAdditionalUrlParam( 'id', NULL );
		
		$itemid = $app->input->getInt( 'Itemid', 0 );
		if ( empty( $itemid ) ) {
			$this->pagination->setAdditionalUrlParam( 'Itemid', NULL );
		}

		$this->search = $app->input->getString( 's', '' );	
		if ( ! empty( $this->search ) ) {
			$this->pagination->setAdditionalUrlParam( 's', $this->search );
		}

		// Check for errors
		if ( count( $errors = $this->get( 'Errors' ) ) ) {
			throw new \Exception( implode( "\n", $errors ) );
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

		$heading = Text::sprintf(
			'COM_YENDIFVIDEOSHARE_TITLE_SEARCH_RESULTS', 
			$this->search
		);

		$this->params->set( 'page_heading', $heading );

		$title = $this->params->get( 'page_title', '' );

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
