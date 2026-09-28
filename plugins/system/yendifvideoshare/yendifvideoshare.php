<?php
/**
 * @version     2.1.1
 * @package     Com_YendifVideoShare
 * @subpackage  Plg_System_YendifVideoShare
 * @author      PluginsWare Interactive Pvt. Ltd <admin@yendifplayer.com>
 * @copyright   Copyright (c) 2012 - 2025 PluginsWare Interactive Pvt. Ltd. All Rights Reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined( '_JEXEC' ) or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\Event\SubscriberInterface;
use PluginsWare\Component\YendifVideoShare\Administrator\Helper\YouTubeImport;

/**
 * Adds cron functionality to the component
 *
 * @since  2.0.0
 */
class plgSystemYendifVideoShare extends CMSPlugin implements SubscriberInterface {

	/**
	 * Load the language file on instantiation
	 *
	 * @var    boolean
	 * @since  2.0.0
	 */
	protected $autoloadLanguage = true;

	/**
     * Returns an array of events this subscriber will listen to.
     *
     * @return  array
	 * 
	 * @since   2.1.5
     */
    public static function getSubscribedEvents(): array {
        return [ 'onAfterRoute' => 'onAfterRoute' ];
    }

	/**
	 * Listener for the "onAfterRoute" event
	 *
	 * @return  void
	 *
	 * @since   2.1.5
	 */
	public function onAfterRoute() {
		// If we are on admin don't process
		$app = Factory::getApplication();

		if ( ! $app->isClient( 'site' ) ) {
			return;
		}

		$db        = Factory::getDbo();
		$date      = Factory::getDate();
		$row       = null;
		$canImport = false;

		try {
			// Start transaction
			$db->transactionStart();

			// Select ID + next_import_date
			$query = $db->getQuery( true )
				->select([
					$db->quoteName( 'id' ),
					$db->quoteName( 'next_import_date' )
				])
				->from( $db->quoteName( '#__yendifvideoshare_imports' ) )
				->where( 'state = 1' )
				->where( 'next_import_date IS NOT NULL' )
				->where( 'next_import_date != ' . $db->quote( $db->getNullDate() ) )
				->where( 'next_import_date <= ' . $db->quote( $date->toSql() ) )
				->setLimit( 1 );

			$row = $db->setQuery( $query )->loadAssoc();

			if ( $row ) {
				// Only update if the timestamp is unchanged.
				$update = $db->getQuery( true )
					->update( $db->quoteName( '#__yendifvideoshare_imports' ) )
					->set( $db->quoteName( 'next_import_date' ) . ' = NULL' )
					->where( $db->quoteName( 'id' ) . ' = ' . (int) $row['id'] )
					->where( $db->quoteName( 'next_import_date' ) . ' = ' . $db->quote( $row['next_import_date'] ) );

				$db->setQuery( $update )->execute();

				// Check if updated and can import
				$canImport = ( $db->getAffectedRows() === 1 );
			}

			// Commit transaction
			$db->transactionCommit();

		} catch ( Exception $e ) {
			$db->transactionRollback();
			return; // Exit safely
		}

		// Run the import
		if ( $canImport && $row ) {
			$import = YouTubeImport::getInstance();
			$import->process( $row['id'] );
		}
	}

}
