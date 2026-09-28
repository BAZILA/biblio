<?php
/**
 * @version    2.1.1
 * @package    Com_YendifVideoShare
 * @author     PluginsWare Interactive Pvt. Ltd <admin@yendifplayer.com>
 * @copyright  Copyright (c) 2012 - 2025 PluginsWare Interactive Pvt. Ltd. All Rights Reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace PluginsWare\Component\YendifVideoShare\Administrator\Table;

// No direct access
\defined( '_JEXEC' ) or die;

use \Joomla\CMS\Access\Access;
use \Joomla\Database\DatabaseDriver;
use \Joomla\CMS\Factory;
use \Joomla\CMS\HTML\HTMLHelper;
use \Joomla\CMS\Language\Text;
use \Joomla\CMS\Table\Table;
use \Joomla\CMS\Uri\Uri;
use \PluginsWare\Component\YendifVideoShare\Site\Helper\YendifVideoShareHelper;

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
 * Class AdvertTable.
 *
 * @since  2.0.0
 */
class AdvertTable extends Table {
	
	protected $uploadPath = 'media/com_yendifvideoshare/uploads/adverts';

	/**
	 * Constructor
	 *
	 * @param  JDatabase  &$db  A database connector object
	 * 
	 * @since  2.0.0
	 */
	public function __construct( DatabaseDriver $db ) {
		$this->typeAlias = 'com_yendifvideoshare.advert';
		parent::__construct( '#__yendifvideoshare_adverts', 'id', $db );
		$this->setColumnAlias( 'published', 'state' );
	}

	/**
	 * Get the type alias for the history table
	 *
	 * @return  string  The alias as described above
	 *
	 * @since   2.0.0
	 */
	public function getTypeAlias() {
		return $this->typeAlias;
	}

	/**
	 * Overloaded bind function to pre-process the params.
	 *
	 * @param   array  $array   Named array
	 * @param   mixed  $ignore  Optional array or list of parameters to ignore
	 *
	 * @return  boolean  True on success.
	 *
	 * @see     Table:bind
	 * @since   2.0.0
	 * @throws  \InvalidArgumentException
	 */
	public function bind( $array, $ignore = '' ) {
		$date = Factory::getDate();
		$task = Factory::getApplication()->input->get( 'task' );			

		if ( $array['id'] == 0 && empty( $array['created_by'] ) ) {
			$array['created_by'] = Factory::getUser()->id;
		}

		if ( $array['id'] == 0 && empty( $array['modified_by'] ) ) {
			$array['modified_by'] = Factory::getUser()->id;
		}

		if ( $task == 'apply' || $task == 'save' ) {
			$array['modified_by'] = Factory::getUser()->id;
		}

		// Support for type field
		if ( empty( $array['type'] ) ) {
			$array['type'] = 'both';
		}

		// Support for impressions field
		if ( isset( $array['impressions'] ) ) {
			$array['impressions'] = (int) $array['impressions'];
		} else {
			$array['impressions'] = 0;
		}

		// Support for clicks field
		if ( isset( $array['clicks'] ) ) {
			$array['clicks'] = (int) $array['clicks'];
		} else {
			$array['clicks'] = 0;
		}

		// Fallback to the OLD versions		
		$array['cat_ids'] = '';

		return parent::bind( $array, $ignore );
	}

	/**
	 * Overloaded check function
	 *
	 * @return  bool
	 * 
	 * @since   2.0.0
	 */
	public function check()	{
		// Support video file field
		$app = Factory::getApplication();
		$files = $app->input->files->get( 'jform', array(), 'raw' );
		$array = $app->input->get( 'jform', array(), 'ARRAY' );

		if ( $files['mp4']['size'] > 0 ) {
			// Deleting existing file
			$oldFile = YendifVideoShareHelper::getFile( $this->id, $this->_tbl, 'mp4' );
			YendifVideoShareHelper::deleteFile( $oldFile );

			$this->mp4 = '';
			$singleFile = $files['mp4'];

			// Check if the server found any error
			$fileError = $singleFile['error'];
			$message = '';

			if ( $fileError > 0 && $fileError != 4 ) {
				switch ( $fileError ) {
					case 1:
						$message = Text::_( 'COM_YENDIFVIDEOSHARE_FILE_UPLOAD_ERROR_1' );
						break;
					case 2:
						$message = Text::_( 'COM_YENDIFVIDEOSHARE_FILE_UPLOAD_ERROR_2' );
						break;
					case 3:
						$message = Text::_( 'COM_YENDIFVIDEOSHARE_FILE_UPLOAD_ERROR_3' );
						break;
				}

				if ( $message != '' ) {
					$app->enqueueMessage( $message, 'error' );
					return false;
				}
			} elseif ( $fileError == 4 ) {
				if ( isset( $array['mp4'] ) )	{
					$this->mp4 = $array['mp4'];
				}
			} else {
				// Check for filetype
				$okMIMETypes = 'video/mp4,video/webm,video/ogg';
				$validMIMEArray = explode( ',', $okMIMETypes );
				$fileMime = $singleFile['type'];
				$fileTemp = $singleFile['tmp_name'];

				if ( ! in_array( $fileMime, $validMIMEArray ) )	{
					$app->enqueueMessage( Text::_( 'COM_YENDIFVIDEOSHARE_FILE_UPLOAD_ERROR_FILETYPE' ), 'error' );
					return false;
				}

				if ( YendifVideoShareHelper::isVideo( $fileTemp ) === false ) {
					$app->enqueueMessage( Text::_( 'COM_YENDIFVIDEOSHARE_FILE_UPLOAD_ERROR_FILETYPE' ), 'error' );
					return false;
				}

				// Replace any special characters in the filename
				$filename = \YendifVideoShareFile::stripExt( $singleFile['name'] );
				$extension = \YendifVideoShareFile::getExt( $singleFile['name'] );
				$filename = preg_replace( '/[^\p{L}\p{N}]/u', '-', $filename );
				$filename = $filename . '.' . $extension;				

				$date = HTMLHelper::_( 'date', 'now', 'Y-m', false );
				$filePath = JPATH_ROOT . '/' . $this->uploadPath . '/' . $date . '/' . $filename;		
				
				if ( ! is_dir( JPATH_ROOT . '/' . $this->uploadPath . '/' . $date . '/' ) ) {
					\YendifVideoShareFolder::create( JPATH_ROOT . '/' . $this->uploadPath . '/' . $date . '/' );
				}

				if ( ! is_file( $filePath ) ) {
					if ( ! \YendifVideoShareFile::upload( $fileTemp, $filePath ) )	{
						$app->enqueueMessage( Text::_( 'COM_YENDIFVIDEOSHARE_FILE_UPLOAD_ERROR_MOVING_FILE' ), 'error' );
						return false;
					}
				}

				$this->mp4 = Uri::root( true ) . '/' . $this->uploadPath . '/' . $date . '/' . $filename;
			}
		} else {
			if ( isset( $array['mp4'] ) )	{
				$this->mp4 = $array['mp4'];
			}
		}

		return parent::check();
	}
	
    /**
     * Delete a record by id
     *
     * @param   mixed  $pk  Primary key value to delete. Optional
     *
     * @return  bool
	 * 
	 * @since   2.0.0
     */
    public function delete( $pk = null ) {
        $this->load( $pk );
        $result = parent::delete( $pk );
        
		if ( $result ) {
			YendifVideoShareHelper::deleteFile( $this->mp4 );
		}

        return $result;
    }
	
}
