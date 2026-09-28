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

use \Joomla\CMS\Table\Table;
use \Joomla\Database\DatabaseDriver;

/**
 * Class LikeTable.
 *
 * @since  2.0.0
 */
class LikeTable extends Table {
	
	/**
	 * Constructor
	 *
	 * @param  JDatabase  &$db  A database connector object
	 * 
	 * @since  2.0.0
	 */
	public function __construct( DatabaseDriver $db ) {
		$this->typeAlias = 'com_yendifvideoshare.like';
		parent::__construct( '#__yendifvideoshare_likes_dislikes', 'id', $db );
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
	
}
