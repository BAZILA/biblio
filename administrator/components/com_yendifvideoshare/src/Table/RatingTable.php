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

use \Joomla\CMS\Factory;
use \Joomla\CMS\Table\Table;
use \Joomla\Database\DatabaseDriver;

/**
 * Class RatingTable.
 *
 * @since  2.0.0
 */
class RatingTable extends Table {
	
	/**
	 * Constructor
	 *
	 * @param  JDatabase  &$db  A database connector object
	 * 
	 * @since  2.0.0
	 */
	public function __construct( DatabaseDriver $db ) {
		$this->typeAlias = 'com_yendifvideoshare.rating';
		parent::__construct( '#__yendifvideoshare_ratings', 'id', $db );
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
        
		if ( $result )	{
			$db = Factory::getDbo();

			$query = 'SELECT SUM(rating) as total_ratings, COUNT(id) as total_users FROM #__yendifvideoshare_ratings WHERE videoid=' . (int) $this->videoid;
			$db->setQuery( $query );
			$item = $db->loadObject();

			$rating = 0.0;
			if ( ! empty( $item->total_ratings ) ) {
				$rating = ( $item->total_ratings / ( $item->total_users * 5 ) ) * 100;
			}
						
			$query = 'UPDATE #__yendifvideoshare_videos SET rating=' . (float) $rating . ' WHERE id=' . (int) $this->videoid;
			$db->setQuery( $query );
			$db->execute();	
		}
	}
	
}
