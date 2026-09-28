<?php
/**
 * @version     2.1.1
 * @package     Com_YendifVideoShare
 * @subpackage  Plg_Finder_YendifVideoShare
 * @author      PluginsWare Interactive Pvt. Ltd <admin@yendifplayer.com>
 * @copyright   Copyright (c) 2012 - 2025 PluginsWare Interactive Pvt. Ltd. All Rights Reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined( '_JEXEC' ) or die;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Table\Table;
use Joomla\Component\Finder\Administrator\Indexer\Adapter;
use Joomla\Component\Finder\Administrator\Indexer\Helper;
use Joomla\Component\Finder\Administrator\Indexer\Indexer;
use Joomla\Component\Finder\Administrator\Indexer\Result;
use Joomla\Database\DatabaseQuery;
use PluginsWare\Component\YendifVideoShare\Site\Helper\YendifVideoShareHelper;
use PluginsWare\Component\YendifVideoShare\Site\Helper\YendifVideoShareRoute;

/**
 * Smart Search adapter for com_yendifvideoshare.
 *
 * @since  2.1.1
 */
class PlgFinderYendifVideoShare extends Adapter {
	
	/**
     * The plugin identifier.
     *
     * @var    string
     * @since  2.1.1
     */
	protected $context = 'Yendif Video Share';

	/**
     * The extension name.
     *
     * @var    string
     * @since  2.1.1
     */
	protected $extension = 'com_yendifvideoshare';

	/**
     * The sublayout to use when rendering the results.
     *
     * @var    string
     * @since  2.1.1
     */
	protected $layout = 'video';

	/**
     * The type of content that the adapter indexes.
     *
     * @var    string
     * @since  2.1.1
     */
	protected $type_title = 'Video';

	/**
     * The table name.
     *
     * @var    string
     * @since  2.1.1
     */
	protected $table = '#__yendifvideoshare_videos';
	
	/**
     * Load the language file on instantiation.
     *
     * @var    boolean
     * @since  2.1.1
     */
	protected $autoloadLanguage = true;

	/**
     * Method to setup the indexer to be run.
     *
     * @return  boolean  True on success.
     *
     * @since   2.1.1
     */
	protected function setup() {
		return true;
	}	

	/**
     * Method to index an item. The item must be a Result object.
     *
     * @param   Result  $item  The item to index as a Result object.
     *
     * @return  void
     *
     * @since   2.1.1
     * @throws  Exception on database error.
     */
	protected function index( Result $item ) {
		// Check if the extension is enabled.
		if ( ComponentHelper::isEnabled( $this->extension ) == false ) {
			return;
		}

		$item->context = 'com_yendifvideoshare.video';

		// Initialize the item parameters.
		$item->params = ComponentHelper::getParams( 'com_yendifvideoshare', true );

		$item->metadata = null;

		$item->description = null;
		$item->summary = YendifVideoShareHelper::Truncate( $item->body, $item->params->get( 'excerpt_length' ) );

		// Create a URL as identifier to recognise items again.
		$item->url = $this->getURL( $item->id, $this->extension, $this->layout );

		// Build the necessary route and path information.
		$item->route = YendifVideoShareRoute::getVideoRoute( $item, NULL );

		// Get the menu title if it exists.
		$title = $this->getItemMenuTitle( $item->url );

		// Adjust the title if necessary.
		if ( ! empty( $title ) && $this->params->get( 'use_menu_title', true ) ) {
			$item->title = $title;
		}

        // Add the image.
        if ( ! empty( $item->image ) ) {
			$item->image = YendifVideoShareHelper::getImage( $item );

            $item->imageUrl = $item->image;
            $item->imageAlt = '';
        }

		// Add the meta-data processing instructions.
		$item->addInstruction( Indexer::META_CONTEXT, 'metakey' );
		$item->addInstruction( Indexer::META_CONTEXT, 'metadesc' );
		$item->addInstruction( Indexer::META_CONTEXT, 'author' );

		// Translate the state. Videos should only be published if the category is published.
		$item->state = $this->translateState( $item->state, $item->cat_state );

		// Add the type taxonomy data.
		$item->addTaxonomy( 'Type', 'Video' );

		// Add the category taxonomy data.
        if ( ! empty( $item->category ) ) {
            $item->addTaxonomy( 'Category', $item->category, $item->state, 1 );
        }

		// Add the author taxonomy data.
        if ( ! empty( $item->author ) ) {
            $item->addTaxonomy( 'Author', $item->author, $item->state );
        }

		// Add the language taxonomy data.
		$item->language = '*';
        $item->addTaxonomy( 'Language', $item->language );

		// Get content extras.
		Helper::getContentExtras( $item );
		
		// Index the item.
		$this->indexer->index( $item );
	}	
	
	/**
     * Method to get the SQL query used to retrieve the list of content items.
     *
     * @param   mixed  $query  A DatabaseQuery object or null.
     *
     * @return  DatabaseQuery  A database object.
     *
     * @since   2.1.1
     */
	protected function getListQuery( $query = null ) {
		$db = $this->db;

		// Check if we can use the supplied SQL query.
		$query = $query instanceof DatabaseQuery ? $query : $db->getQuery( true )
			->select( 'a.id, a.title, a.alias, a.description AS body' )
			->select( 'a.image' )
			->select( 'a.state, a.catid, a.created_date AS start_date, a.created_by' )
			->select( 'a.updated_date AS modified, a.modified_by' )
			->select( 'a.meta_keywords AS metakey, a.meta_description AS metadesc, a.access, a.ordering' )
			->select( 'a.published_up AS publish_start_date, a.published_down AS publish_end_date' )
			->select( 'c.title AS category, c.state AS cat_state, c.access AS cat_access' );

		// Handle the alias CASE WHEN portion of the query
		$case_when_item_alias = ' CASE WHEN ';
		$case_when_item_alias .= $query->charLength( 'a.alias', '!=', '0' );
		$case_when_item_alias .= ' THEN ';
		$a_id = $query->castAs( 'CHAR', 'a.id' );
		$case_when_item_alias .= $query->concatenate( array( $a_id, 'a.alias' ), ':' );
		$case_when_item_alias .= ' ELSE ';
		$case_when_item_alias .= $a_id . ' END as slug';
		$query->select( $case_when_item_alias );

		$case_when_category_alias = ' CASE WHEN ';
		$case_when_category_alias .= $query->charLength( 'c.alias', '!=', '0' );
		$case_when_category_alias .= ' THEN ';
		$c_id = $query->castAs( 'CHAR', 'c.id' );
		$case_when_category_alias .= $query->concatenate( array( $c_id, 'c.alias' ), ':' );
		$case_when_category_alias .= ' ELSE ';
		$case_when_category_alias .= $c_id . ' END as catslug';
		$query->select( $case_when_category_alias )

			->select( 'u.name AS author' )
			->from( '#__yendifvideoshare_videos AS a' )
			->join( 'LEFT', '#__yendifvideoshare_categories AS c ON c.id = a.catid' )
			->join( 'LEFT', '#__users AS u ON u.id = a.created_by' );			
		
		return $query;
	}

	/**
     * Method to remove the link information for items that have been deleted.
     *
     * @param   string  $context  The context of the action being performed.
     * @param   Table   $table    A Table object containing the record to be deleted
     *
     * @return  void
     *
     * @since   2.1.1
     * @throws  Exception on database error.
     */
	public function onFinderAfterDelete( $context, $table ): void	{
		if ( $context === 'com_yendifvideoshare.video' ) {
			$id = $table->id;
		} elseif ( $context === 'com_finder.index' ) {
			$id = $table->link_id;
		} else {
			return;
		}

		// Remove the item from the index.
		$this->remove( $id );
	}

	/**
     * Smart Search after save content method.
     * Reindexes the link information for a video that has been saved.
     * It also makes adjustments if the access level of an item or the
     * category to which it belongs has changed.
     *
     * @param   string   $context  The context of the content passed to the plugin.
     * @param   Table    $row      A Table object.
     * @param   boolean  $isNew    True if the content has just been created.
     *
     * @return  void
     *
     * @since   2.1.1
     * @throws  Exception on database error.
     */
	public function onFinderAfterSave( $context, $row, $isNew ): void {
		// We only want to handle videos here.
		if ( $context === 'com_yendifvideoshare.video' ) {
			// Check if the access levels are different.
            if ( ! $isNew && $this->old_access != $row->access ) {
                // Process the change.
                $this->itemAccessChange( $row );
            }

			// Reindex the item.
			$this->reindex( $row->id );
		}
	}

	/**
     * Method to update the link information for items that have been changed
     * from outside the edit screen. This is fired when the item is published,
     * unpublished, archived, or unarchived from the list view.
     *
     * @param   string   $context  The context for the content passed to the plugin.
     * @param   array    $pks      An array of primary key ids of the content that has changed state.
     * @param   integer  $value    The value of the state that the content has been changed to.
     *
     * @return  void
     *
     * @since   2.1.1
     */
	public function onFinderChangeState( $context, $pks, $value ) {	
		// We only want to handle videos here.	
		if ( $context === 'com_yendifvideoshare.video' ) {
			$this->itemStateChange( $pks, $value );
		}

		// Handle when the plugin is disabled.
		if ( $context === 'com_plugins.plugin' && $value === 0 ) {
			$this->pluginDisable( $pks );
		}
	}

}
