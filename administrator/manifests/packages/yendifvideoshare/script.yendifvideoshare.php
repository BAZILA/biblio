<?php
/**
 * @package    Pkg_YendifVideoShare
 * @author     PluginsWare Interactive Pvt. Ltd <admin@yendifplayer.com>
 * @copyright  Copyright (c) 2012 - 2025 PluginsWare Interactive Pvt. Ltd. All Rights Reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

defined( '_JEXEC' ) or die();

use Joomla\CMS\Factory;
use Joomla\CMS\Installer\InstallerScript;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;

/**
 * Updates the database structure of the component
 *
 * @since  2.1.5
 */
class Pkg_YendifVideoShareInstallerScript extends InstallerScript {

	/**
	 * The minimum Joomla! version required to install this extension
	 *
	 * @var  string
	 */
	protected $minimumJoomla = '4.4.0';

	/**
	 * The minimum Php version required to install this extension
	 *
	 * @var  string
	 */
    protected $minimumPhp = '7.4.0';

	/**
	 * Method called before install/update the component
	 * 
	 * Note: This method won't be called during uninstall process
	 *
	 * @param   string  $type    Type of process [install | update]
	 * @param   mixed   $parent  Object who called this method
	 *
	 * @return  boolean  True if the process should continue, false otherwise
	 * 
	 * @since   2.1.5
	 */
	public function preflight( $type, $parent )	{
		if ( version_compare( PHP_VERSION, $this->minimumPhp, '<' ) ) {
            Factory::getApplication()->enqueueMessage( sprintf( Text::_( 'JLIB_INSTALLER_MINIMUM_PHP' ), $this->minimumPhp ), 'error' );
            return false;
        }

        if ( version_compare( JVERSION, $this->minimumJoomla, '<' ) ) {
            Factory::getApplication()->enqueueMessage( sprintf( Text::_( 'JLIB_INSTALLER_MINIMUM_JOOMLA' ), $this->minimumJoomla ), 'error' );
            return false;
        }

		return true;
	}

	/**
	 * Method called after install/update the component.
	 * 
	 * @param   string  $type    type
	 * @param   string  $parent  parent
	 *
	 * @return  boolean
	 * 
	 * @since   2.1.5
	 */
	public function postflight( $type, $parent ) {
		$db = Factory::getDbo();

        $plugins = array(
			array( 'folder' => 'finder', 'element' => 'yendifvideoshare' ),
            array( 'folder' => 'content', 'element' => 'yendifvideoshare' ),
            array( 'folder' => 'system', 'element' => 'yendifvideoshare' )          
		);

        foreach ( $plugins as $plugin ) {
            $query = $db->getQuery( true )
                ->update( '#__extensions' )
                ->set( 'enabled = 1' )
                ->where( 'type = ' . $db->quote( 'plugin' ) )
                ->where( 'folder = ' . $db->quote( $plugin['folder'] ) )
                ->where( 'element = ' . $db->quote( $plugin['element'] ) );

            $db->setQuery( $query );
			$db->execute();
        }

		return true;
	}
	
}
