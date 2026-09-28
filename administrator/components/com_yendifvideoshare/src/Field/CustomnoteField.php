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

use \Joomla\CMS\Factory;
use \Joomla\CMS\Form\FormField;
use \Joomla\CMS\Language\Text;
use \PluginsWare\Component\YendifVideoShare\Site\Helper\YendifVideoShareHelper;

/**
 * Class CustomnoteField.
 *
 * @since  2.0.0
 */
class CustomnoteField extends FormField {

	/**
	 * The form field type.
	 *
	 * @var    string
	 * @since  2.0.0
	 */
	protected $type = 'customnote';

	/**
	 * Hide the label when rendering the form field.
	 *
	 * @var    boolean
	 * @since  2.0.0
	 */
	protected $hiddenLabel = true;

	/**
	 * Hide the description when rendering the form field.
	 *
	 * @var    boolean
	 * @since  2.0.0
	 */
	protected $hiddenDescription = true;

	/**
	 * Method to get the field label markup.
	 *
	 * @return  string  The field label markup.
	 *
	 * @since   2.0.0
	 */
	protected function getInput() {
		if ( ! YendifVideoShareHelper::canDo() ) {
			$extension = isset( $this->element['extension'] ) ? $this->element['extension'] : 'com_yendifvideoshare';
			return '<div class="alert alert-warning" style="margin: 0;">' . Text::_( strtoupper( $extension ) . '_PREMIUM_DESC_PREMIUM_ONLY' ) . '</div>';
		}
		
		return '';
	}	

}