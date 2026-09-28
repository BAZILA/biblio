<?php
/**
 * @version    2.1.1
 * @package    Com_YendifVideoShare
 * @author     PluginsWare Interactive Pvt. Ltd <admin@yendifplayer.com>
 * @copyright  Copyright (c) 2012 - 2025 PluginsWare Interactive Pvt. Ltd. All Rights Reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace PluginsWare\Component\YendifVideoShare\Site\Model;

// No direct access
\defined( '_JEXEC' ) or die;

use \Joomla\CMS\Factory;
use \Joomla\CMS\Language\Text;
use \Joomla\CMS\MVC\Model\ItemModel;
use \Joomla\CMS\Table\Table;
use \PluginsWare\Component\YendifVideoShare\Site\Helper\YendifVideoShareHelper;

/**
 * Class PlayerModel.
 *
 * @since  2.0.0
 */
class PlayerModel extends ItemModel {

	public $_item;	

	/**
	 * Method to auto-populate the model state.
	 *
	 * Note. Calling getState in this method will result in recursion.
	 *
	 * @return  void
	 *
	 * @since   2.0.0
	 * @throws  Exception
	 */
	protected function populateState() {
		$app = Factory::getApplication();

		// Video Id
		$id = $app->input->getInt( 'id', 0 );
		$this->setState( 'video.id', $id );
	}

	/**
	 * Get player params
	 * 
	 * @return  object  The player params
	 * 
	 * @since   2.0.0
	 */
	public function getParams() {
		$app = Factory::getApplication();

		$params = YendifVideoShareHelper::resolveParams( $app->getParams( 'com_yendifvideoshare' ) );

		$properties = array( 'autoplay', 'loop', 'volume', 'playbtn', 'controlbar', 'playpause', 'currenttime', 'progress', 'duration', 'volumebtn', 'fullscreen', 'embed', 'share', 'autoplaylist' );

		foreach ( $properties as $property ) {
			$value = $app->input->getInt( $property, -1 );

			if ( $value > -1 ) {
				$params->set( $property, $value );
			}
		}

		return $params;
	}

	/**
	 * Method to get an object.
	 *
	 * @param   integer  $id  The id of the object to get.
	 *
	 * @return  mixed    Object on success, false on failure.
	 *
	 * @since   2.0.0
	 * @throws  Exception
	 */
	public function getItem( $id = null ) {
		if ( $this->_item === null ) {
			$app = Factory::getApplication();
			
			$this->_item = false;	

			if ( empty( $id ) ) {
				$id = $this->getState( 'video.id' );
			}

			if ( $id > 0 ) {
				// Get a level row instance
				$table = $this->getTable();

				// Attempt to load the row
				if ( $table && $table->load( $id ) ) {
					// Convert the Table to a clean object
					$properties  = $table->getProperties( 1 );
					$this->_item = (object) $properties;			
				}				
			} else {
				$properties = array( 
					'id'    => 0,
					'title' => $app->get( 'sitename' )
				);

				$sources = array( 'image', 'mp4', 'mp4_hd', 'webm', 'ogv', 'youtube', 'vimeo', 'hls', 'dash', 'captions' );

				foreach ( $sources as $source ) {
					$src = $app->input->get( $source, '', 'BASE64' );

					if ( empty( $src ) ) {
						continue;
					}

					$src = base64_decode( $src );
					$properties[ $source ] = $src;

					switch ( $source ) {
						case 'mp4':
						case 'mp4_hd':
						case 'webm':
						case 'ogv':
							$properties['type'] = 'video';
							break;
						case 'youtube':
							$properties['type'] = 'youtube';

							if ( ! isset( $properties['image'] ) ) {
								$properties['image'] = YendifVideoShareHelper::getYouTubeVideoImage( $src );
							}
							break;
						case 'vimeo':
							$properties['type'] = 'vimeo';

							if ( ! isset( $properties['image'] ) ) {
								$properties['image'] = YendifVideoShareHelper::getVimeoVideoImage( $src );
							}
							break;
						case 'hls':
						case 'dash':
							$properties['type'] = 'rtmp';
							break;							
					}					
				}

				if ( isset( $properties['type'] ) ) {					
					$this->_item = (object) $properties;
				}
			}

			if ( empty( $this->_item ) ) {
				return false;
			}
		}		

		return $this->_item;
	}

	/**
	 * Get an instance of Table class
	 *
	 * @param   string  $type    Name of the Table class to get an instance of.
	 * @param   string  $prefix  Prefix for the table class name. Optional.
	 * @param   array   $config  Array of configuration values for the Table object. Optional.
	 *
	 * @return  Table|bool  Table if success, false on failure.
	 * 
	 * @since   2.0.0
	 */
	public function getTable( $type = 'Video', $prefix = 'Administrator', $config = array() ) {
		return parent::getTable( $type, $prefix, $config );
	}
	
}
