<?php
/**
 * @package    Com_YendifVideoShare
 * @author     PluginsWare Interactive Pvt. Ltd <admin@yendifplayer.com>
 * @copyright  Copyright (c) 2012 - 2025 PluginsWare Interactive Pvt. Ltd. All Rights Reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

defined( '_JEXEC' ) or die();

use Joomla\CMS\Factory;
use Joomla\CMS\Installer\InstallerScript;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;

if ( ! class_exists( 'YendifVideoShareFile' ) ) {
	if ( class_exists( '\Joomla\CMS\Filesystem\File' ) ) {
		class_alias( '\Joomla\CMS\Filesystem\File', 'YendifVideoShareFile' );
		class_alias( '\Joomla\CMS\Filesystem\Folder', 'YendifVideoShareFolder' );
	} else {
		class_alias( '\Joomla\Filesystem\File', 'YendifVideoShareFile' );
		class_alias( '\Joomla\Filesystem\Folder', 'YendifVideoShareFolder' );
	}
}

/**
 * Updates the database structure of the component
 *
 * @since  1.0.0
 */
class Com_YendifVideoShareInstallerScript extends InstallerScript {

	/**
	 * Method to install the component
	 *
	 * @param   mixed  $parent  Object who called this method.
	 *
	 * @return  void
	 *
	 * @since   1.0.0
	 */
	public function install( $parent ) {	
		$this->installDb( $parent );
	}

	/**
	 * Method to update the component
	 *
	 * @param   mixed  $parent  Object who called this method.
	 *
	 * @return  void
	 * 
	 * @since   1.0.0
	 */
	public function update( $parent ) {	
		$this->installDb( $parent );
	}	

	/**
	 * Method to update the DB of the component
	 *
	 * @param   mixed  $parent  Object who started the upgrading process
	 *
	 * @return  void
	 *
	 * @since   2.0.0
	 */
	private function installDb( $parent ) {
		$db = Factory::getDbo();

		// Migrate from Joomla 3.x
		if ( $this->existsTable( '#__yendifvideoshare_config' ) ) {
			$this->migrateFromJoomla3();
		}		
		
		// Install default data on a fresh installation
		$query = 'SELECT COUNT(id) FROM #__yendifvideoshare_categories';
		$db->setQuery( $query );

		if ( ! $db->loadResult() ) {		
			// Insert our first category
			$item = new stdClass();

			$item->id = 1;
			$item->title = 'Uncategorized';
			$item->alias = 'uncategorized';
			$item->parent = 0;
			$item->access = 1;
			$item->created_by = Factory::getUser()->id;
			$item->modified_by = Factory::getUser()->id;
			$item->state = 1;

			$db->insertObject( '#__yendifvideoshare_categories', $item );

			// Set Component Parameters
			$config = array(
				'image_ratio' => 56.25,
				'no_of_rows' => 3,
				'no_of_cols' => 3,
				'default_image' => Uri::root() . 'media/com_yendifvideoshare/images/placeholder.jpg',
				'title_length' => 0,
				'show_excerpt' => 1,
				'excerpt_length' => 100,
				'show_category' => 1,
				'show_user' => 0,	
				'show_date' => 1,
				'show_views' => 1,				
				'enable_popup' => 0,
				'show_rating' => 0,
				'allow_guest_rating' => 1,
				'show_likes' => 0,
				'allow_guest_like' => 1,
				'show_videos_count' => 1,
				'show_title' => 1,
				'show_description' => 1,
				'show_search' => 1,
				'show_related' => 1,							
				'related_no_of_rows' => '',
				'related_no_of_cols' => '',
				'related_orderby' =>'',
				'comments' => 'none',
				'fb_app_id' => '',
				'fb_post_count' => 5,
				'fb_color_scheme' => 'light',
				'ratio' => 56.25,
				'autoplay' => 0,
				'loop' => 0,
				'volume' => -1,
				'hotkeys' => 0,
				'show_consent' => 0,
				'analytics' => '',
				'controlbar' => 1,
				'playbtn' => 1,
				'playpause' => 1,
				'currenttime' => 1,
				'progress' => 1,
				'duration' => 1,
				'volumebtn' => 1,
				'fullscreen' => 1,
				'embed' => 0,
				'share' => 0,
				'download' => 0,
				'playlist_position' => 'right',
				'playlist_width' => 250,
				'playlist_height' => 150,
				'autoplaylist' => 0,
				'ad_engine' => 'vast',
				'enable_adverts' => 'both',
				'show_adverts_timeframe' => 1,
				'can_skip_adverts' => 1,
				'show_skip_adverts_on' => 3,
				'vasturl' => '',
				'allow_upload' => 1,
				'max_upload_size' => '',
				'allow_youtube' => 1,
				'allow_vimeo' => 1,
				'allow_hls_dash' => 1,
				'allow_subtitle' => 0,
				'schedule_video_publishing' => 0,
				'youtube_api_key' => '',
				'vimeo_access_token' => '',
				'custom_css' => '',
				'show_feed' => 0,
				'feed_icon' => Uri::root() . 'media/com_yendifvideoshare/images/rss.png',
				'feed_limit' => 20,
				'itemid_category' => 0,
				'itemid_video' => 0,
				'license' => '',
				'logo' => '',
				'logoposition' => 'bottomleft',
				'logoalpha' => 50,
				'logotarget' => 'https://yendifplayer.com/',
				'displaylogo' => 1				
			);

			$this->setComponentParams( $config );
		}

		// ...
		$query = 'SELECT id FROM #__yendifvideoshare_options WHERE name=' . $db->quote( 'access' );
		$db->setQuery( $query );
        $count = $db->loadResult();		

		if ( ! $count ) {
			$row = new stdClass();			
   			$row->id = NULL;
			$row->name = 'access';
			$row->value = 1;
			 
   			$db->insertObject( '#__yendifvideoshare_options', $row );		
		}
	}

	/**
	 * Checks if a certain table exists on the current database
	 *
	 * @param   string   $table_name  Name of the table
	 *
	 * @return  boolean  True if it exists, false if it does not
	 * 
	 * @since   2.0.0
	 */
	private function existsTable( $table_name ) {
		$db = Factory::getDbo();
		$table_name = str_replace( '#__', $db->getPrefix(), (string) $table_name );
		return in_array( $table_name, $db->getTableList() );
	}	

	/**
	 * Migrate from Joomla 3.x
	 * 
	 * @since  2.0.0
	 */
	private function migrateFromJoomla3() {
		$app = Factory::getApplication();
		$db  = Factory::getDbo();		

		// Get options
		$query = 'SELECT * FROM #__yendifvideoshare_options';
		$db->setQuery( $query );
		$options = $db->loadObjectList();

		// Set component params
		$params = array();
		$params['itemid_category'] = -1;
		$params['itemid_video'] = -1;

		foreach ( $options as $option ) {
			$name  = $option->name;
			$value = $option->value;

			switch ( $name ) {
				case 'ratio':
					$value = (float) $value * 100;
					break;
				case 'allow_youtube_upload':
					$name = 'allow_youtube';
					break;
				case 'allow_rtmp_upload':
					$name = 'allow_hls_dash';
					break;
				case 'google_api_key':
					$name = 'youtube_api_key';
					break;
				case 'playlist_desc_limit':
					$name = 'excerpt_length';
					break;
				case 'show_media_count':
					$name = 'show_videos_count';
					break;
				case 'show_likes_dislikes':
					$name = 'show_likes';
					break;
				case 'responsive_css':
					$name = 'custom_css';
					break;
			}

			$params[ $name ] = $value;
		}

		$this->setComponentParams( $params );

		// Drop the config table
		$query = 'DROP TABLE IF EXISTS #__yendifvideoshare_config';
		$db->setQuery( $query );
		$db->execute();

		// Clean unwanted directories from the back-end components folder
		$folders = array(
			'controllers',
			'elements',
			'libraries',
			'models',
			'tables',
			'views'
		);

		foreach ( $folders as $folder ) {
			$path = JPATH_ROOT . '/administrator/components/com_yendifvideoshare/' . $folder;

			if ( is_dir( $path ) ) {
				\YendifVideoShareFolder::delete( $path );
			}
		}

		$files = array(
			'yendifvideoshare.php',
			'install.mysql.sql',
			'uninstall.mysql.sql'
		);

		foreach ( $files as $file ) {
			$path = JPATH_ROOT . '/administrator/components/com_yendifvideoshare/' . $file;
			
			if ( is_file( $path ) ) {
				\YendifVideoShareFile::delete( $path );
			}
		}

		// Clean unwanted directories from the front-end components folder
		$folders = array(
			'controllers',
			'models',
			'views'
		);

		foreach ( $folders as $folder ) {
			$path = JPATH_ROOT . '/components/com_yendifvideoshare/' . $folder;

			if ( is_dir( $path ) ) {
				\YendifVideoShareFolder::delete( $path );
			}
		}

		$files = array(
			'yendifvideoshare.php',
			'komento_plugin.php',
			'router.php'
		);

		foreach ( $files as $file ) {
			$path = JPATH_ROOT . '/components/com_yendifvideoshare/' . $file;
			
			if ( is_file( $path ) ) {
				\YendifVideoShareFile::delete( $path );
			}
		}
	}

	/**
	 * Set component configuration parameters
	 * 
	 * @param  int  $config  Array of default configuration values
	 * 
	 * @since  2.0.0
	 */
	private function setComponentParams( $config ) {
		$db = Factory::getDbo();

		$componentName = 'com_yendifvideoshare';

		$params = array();
		foreach ( $config as $key => $value ) {
			$params[ $key ] = $value;
		}	

		// Save the parameters		
		$query = $db->getQuery( true );

		$query
			->clear()
			->update( '#__extensions' )
			->set( 'params = ' . $db->quote( json_encode( $params ) ) )
			->where(
				array(
					'type LIKE ' . $db->quote( 'component' ),
					'element LIKE ' . $db->quote( $componentName )
				)
			);
		$db->setQuery( $query );
		$db->execute();
	}
	
}
