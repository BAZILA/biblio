<?php
/**
 * @version    2.1.1
 * @package    Com_YendifVideoShare
 * @author     PluginsWare Interactive Pvt. Ltd <admin@yendifplayer.com>
 * @copyright  Copyright (c) 2012 - 2025 PluginsWare Interactive Pvt. Ltd. All Rights Reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace PluginsWare\Component\YendifVideoShare\Site\Service;

// No direct access
\defined( '_JEXEC' ) or die;

use \Joomla\CMS\Component\Router\RouterView;
use \Joomla\CMS\Component\Router\Rules\RulesInterface;

/**
 * Class YendifVideoShareNomenuRules.
 *
 * @since  2.0.0
 */
class YendifVideoShareNomenuRules implements RulesInterface {

	/**
	 * Router this rule belongs to
	 *
	 * @var    RouterView
	 * @since  2.0.0
	 */
	protected $router;

	/**
	 * Class constructor.
	 *
	 * @param  RouterView  $router  Router this rule belongs to
	 *
	 * @since  2.0.0
	 */
	public function __construct( RouterView $router ) {
		$this->router = $router;
	}

	/**
	 * Dummy method to fullfill the interface requirements
	 *
	 * @param   array  &$query  The query array to process
	 *
	 * @return  void
	 *
	 * @since   2.0.0
	 */
	public function preprocess( &$query ) {
		// Do nothing
	}

	/**
	 * Parse an URL
	 *
	 * @param   array  &$segments  The URL segments to parse
	 * @param   array  &$vars      The vars that result from the segments
	 *
	 * @return  void
	 *
	 * @since   2.0.0
	 */
	public function parse( &$segments, &$vars ) {
		if ( $count = count( $segments ) ) {
			$componentViews = array( 'categories', 'category', 'videos', 'video', 'search', 'ads', 'player', 'user', 'videoform' );
 
			if ( $count == 2 ) {
				$vars['view'] = $segments[0];
				$vars['id']  = $segments[1];
			} else {
				if ( in_array( $segments[0], $componentViews ) ) {
					$vars['view'] = $segments[0];
				} else {
					$vars['id'] = $segments[0];
				}				
			}

			$segments = array();
		}

		return;
	}

	/**
	 * Build an URL
	 *
	 * @param   array  &$query     The vars that should be converted
	 * @param   array  &$segments  The URL segments to create
	 *
	 * @return  void
	 *
	 * @since   2.0.0
	 */
	public function build( &$query, &$segments ) {
		if ( isset( $query['view'] ) ) {
			$segments[] = $query['view'];
			unset( $query['view'] );
		}
		
		if ( isset( $query['id'] ) ) {
			if ( ! empty( $query['id'] ) ) {
				$segments[] = $query['id'];
			}
			
			unset( $query['id'] );
		}
	}

}
