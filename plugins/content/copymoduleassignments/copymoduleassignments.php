<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  Content.CopyAssignments
 *
 * @copyright   Copyright (C) 2016 Aleksandar Jovanović (himself@alexxandar.me). All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Copy Module Assignments Plugin
 */
class PlgContentCopymoduleassignments extends JPlugin
{
	/**
	 * Database object
	 *
	 * @var    JDatabaseDriver
	 * @since  3.3
	 */
	protected $db;

	/**
	 * Plugin that copies module assignments from original to new menu item when using "Save as copy"
	 *
	 * @param   string   $context  The context of the content being passed to the plugin.
	 * @param   mixed    &$row     An object with a "text" property
	 * @param   mixed    $params   Additional parameters. See {@see PlgContentContent()}.
	 * @param   integer  $page     Optional page number. Unused. Defaults to zero.
	 *
	 * @return  boolean	True on success.
	 */
	public function onContentAfterSave( $context, &$table, $isNew )
	{
	    // Return if invalid context
		if ( $context != 'com_menus.item' )
			return true;

        // Return if items is not a product of "Save as copy"
        if ( !( $table->get( '_location_id' ) > 1 && $isNew == false ) )
            return true;

        // Find all assigned modules
        $query1 = $this->db->getQuery(true)
			->select($this->db->quoteName('moduleid'))
			->from($this->db->quoteName('#__modules_menu'))
			->where($this->db->quoteName('menuid') . ' = ' . $table->get( '_location_id' ) );
		$this->db->setQuery($query1);
        $modules = (array) $this->db->loadColumn();

        // Assing all found modules to copied menu item
        if( !empty( $modules ) )
        {
            foreach( $modules as $mid )
            {
                $mdl = new stdClass();
                $mdl->moduleid = $mid;
                $mdl->menuid = $table->get( 'id' );
                $this->db->insertObject( '#__modules_menu', $mdl );
            }
        }

        // Check if menu item is on the assign to all except list
        $query2 = $this->db->getQuery(true)
			->select($this->db->quoteName('moduleid'))
			->from($this->db->quoteName('#__modules_menu'))
			->where($this->db->quoteName('menuid') . ' = -' . $table->get( '_location_id' ) );
		$this->db->setQuery($query2);
        $modulesExcept = (array) $this->db->loadColumn();

        // Add menu item to the exception list for all modules that have the original one in there
        if( !empty( $modulesExcept ) )
        {
            foreach( $modulesExcept as $mid )
            {
                $mdl = new stdClass();
                $mdl->moduleid = $mid;
                $mdl->menuid = $table->get( 'id' ) * -1;
                $this->db->insertObject( '#__modules_menu', $mdl );
            }
        }

		return true;
	}
}
