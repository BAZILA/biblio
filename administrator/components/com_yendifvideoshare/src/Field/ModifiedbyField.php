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

use \Joomla\CMS\Form\FormField;
use \Joomla\CMS\Factory;

/**
 * Class ModifiedbyField.
 *
 * @since  2.0.0
 */
class ModifiedbyField extends FormField {

	/**
	 * The form field type.
	 *
	 * @var    string
	 * @since  2.0.0
	 */
	protected $type = 'modifiedby';

	/**
	 * Method to get the field input markup.
	 *
	 * @return  string  The field input markup.
	 *
	 * @since   2.0.0
	 */
	protected function getInput() {
		// Initialize variables
		$user = Factory::getUser();

		$html   = array();		
		$html[] = '<input type="hidden" name="' . $this->name . '" value="' . $user->id . '" />';
		if ( ! $this->hidden ) {
			$html[] = "<div>" . $user->name . " (" . $user->username . ")</div>";
		}

		return implode( $html );
	}

}
