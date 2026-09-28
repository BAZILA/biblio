<?php
/**
 * @version    2.1.1
 * @package    Com_YendifVideoShare
 * @author     PluginsWare Interactive Pvt. Ltd <admin@yendifplayer.com>
 * @copyright  Copyright (c) 2012 - 2025 PluginsWare Interactive Pvt. Ltd. All Rights Reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace PluginsWare\Component\YendifVideoShare\Administrator\Field;

\defined( 'JPATH_BASE' ) or die;

use \Joomla\CMS\Date\Date;
use \Joomla\CMS\Factory;
use \Joomla\CMS\Form\FormField;
use \Joomla\CMS\Language\Text;

/**
 * Class TimecreatedField.
 *
 * @since  2.0.0
 */
class TimecreatedField extends FormField {

	/**
	 * The form field type.
	 *
	 * @var    string
	 * @since  2.0.0
	 */
	protected $type = 'timecreated';

	/**
	 * Method to get the field input markup.
	 *
	 * @return  string  The field input markup.
	 *
	 * @since   2.0.0
	 */
	protected function getInput() {
		// Initialize variables
		$html = array();

		$time_created = $this->value;

		if ( ! strtotime( $time_created ) )	{
			$time_created = Factory::getDate( 'now', Factory::getConfig()->get( 'offset' ) )->toSql( true );
			$html[]       = '<input type="hidden" name="' . $this->name . '" value="' . $time_created . '" />';
		}

		$hidden = (boolean) $this->element['hidden'];

		if ( $hidden == null || !$hidden ) {
			$jdate       = new Date( $time_created );
			$pretty_date = $jdate->format( Text::_( 'DATE_FORMAT_LC2' ) );
			$html[]      = "<div>" . $pretty_date . "</div>";
		}

		return implode( $html );
	}
	
}
