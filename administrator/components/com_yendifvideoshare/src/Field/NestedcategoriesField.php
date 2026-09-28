<?php
/**
 * @version    2.1.1
 * @package    Com_YendifVideoShare
 * @author     PluginsWare Interactive Pvt. Ltd <admin@yendifplayer.com>
 * @copyright  Copyright (c) 2012 - 2025 PluginsWare Interactive Pvt. Ltd. All Rights Reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace PluginsWare\Component\YendifVideoShare\Administrator\Field;

\defined('JPATH_BASE') or die;

use \Joomla\CMS\Factory;
use \Joomla\CMS\Form\Field\ListField;
use \Joomla\CMS\HTML\HTMLHelper;

/**
 * Class NestedcategoriesField.
 *
 * @since  2.0.0
 */
class NestedcategoriesField extends ListField {
	
	/**
	 * The form field type.
	 *
	 * @var    string
	 * @since  2.0.0
	 */
	protected $type = 'nestedcategories';

	/**
	 * Method to get the field options.
	 *
	 * @return  array  The field option objects.
	 *
	 * @since   2.0.0
	 */
	protected function getOptions()	{
		$db = Factory::getDbo();

		$query = $db->getQuery(true)
			->select( 'a.*' )
			->from( '#__yendifvideoshare_categories AS a' )
			->where( 'a.state = 1' )
			->order( 'a.title ASC' );

		// Get the options
		$db->setQuery( $query );

		try	{
			$items = $db->loadObjectList();
		} catch ( \RuntimeException $e ) {
			// throw new \Exception( $e->getMessage(), 500 );
		}

		$children = array();

		if ( $items ) {
			foreach ( $items as $v ) {
				$v->parent_id = $v->parent;
				$pt = $v->parent;
				$list = @$children[ $pt ] ? $children[ $pt ] : array();
				array_push( $list, $v );
				$children[ $pt ] = $list;
			}
		}

		$list = HTMLHelper::_( 'menu.treerecurse', 0, '', array(), $children, 9999, 0, 0 );

		// Pad the option text with spaces using depth level as a multiplier
		$options = array();

		foreach ( $list as $item ) {
			$option = new \stdClass;
			$option->value = $item->id;
			
			$option->text  = str_ireplace( '&#160;', '—', $item->treename );
			$option->text  = str_replace( '-', ' ', $option->text );

			$options[] = $option;
		}

		// Merge any additional options in the XML definition
		$options = array_merge( parent::getOptions(), $options );

		return $options;
	}

}
