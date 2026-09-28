<?php
/**
 * @version     2.1.1
 * @package     Com_YendifVideoShare
 * @subpackage  Plg_Content_YendifVideoShare
 * @author      PluginsWare Interactive Pvt. Ltd <admin@yendifplayer.com>
 * @copyright   Copyright (c) 2012 - 2025 PluginsWare Interactive Pvt. Ltd. All Rights Reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined( '_JEXEC' ) or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Helper\ModuleHelper;
use Joomla\CMS\Plugin\CMSPlugin;
use PluginsWare\Component\YendifVideoShare\Site\Helper\YendifVideoSharePlayer;

/**
 * Plugin to enable loading videos into content (e.g. articles)
 * This uses the {yendifplayer} syntax
 *
 * @since  2.0.0
 */
class plgContentYendifVideoShare extends CMSPlugin {

	/**
	 * Plugin that loads videos within content
	 *
	 * @param   string   $context   The context of the content being passed to the plugin.
	 * @param   object   &$article  The article object.  Note $article->text is also available
	 * @param   mixed    &$params   The article params
	 * @param   integer  $page      The 'page' number
	 *
	 * @return  void
	 *
	 * @since   2.0.0
	 */
	public function onContentPrepare( $context, &$article, &$params, $page = 0 ) {
		// Don't run this plugin when the content is being indexed
		if ( $context === 'com_finder.indexer' ) {
			return;
		}

		// Simple performance check to determine whether bot should process further
		if ( strpos( $article->text, 'yendifplayer' ) === false ) {
			return;
		}

		// Expression to search for
		$regex = '/{yendifplayer\s*.*?}/i';

		// Find all instances of plugin and put in $matches
		preg_match_all( $regex, $article->text, $matches );

		$this->process( $article, $matches[0], $regex );
	}

	private function process( $article, $matches, $regex ) {	
		foreach ( $matches as $match ) {
			$query = str_replace( '{yendifplayer', '', $match );
			$query = str_replace( '}', '', $query );
			$query = str_replace( '"', '', $query );
			$query = str_replace( "'", '', $query );
			$query = strip_tags( $query );
			$query = trim( html_entity_decode( $query ), " \t\n\r\0\x0B\xC2\xA0" );
			$query = explode( ' ', $query );
			$query = implode( '&', $query );
			
			$player = $this->load( $query );
			$article->text = str_replace( $match, $player, $article->text );
		}

		// Removes the left tags
	   	$article->text = preg_replace( $regex, '', $article->text );	   
   }

   private function load( $query ) {	
		parse_str( $query, $params );	
		
		if ( isset( $params['catid'] ) && ! empty( $params['catid'] ) ) {
			$document = Factory::getDocument();
			$renderer = $document->loadRenderer( 'module' );

			if ( ! isset( $params['uid'] ) ) {
				static $uid = 0;
				$uid++;
				$params['uid'] = $uid;
			}
			
			$module = ModuleHelper::getModule( 'mod_yendifvideoshare_playlist', $params );
			$module->params = $params;
			
			return $renderer->render( $module );
		}

		return YendifVideoSharePlayer::load( $params );	
	}

}
