<?php
/**
 * @version    2.1.1
 * @package    Com_YendifVideoShare
 * @author     PluginsWare Interactive Pvt. Ltd <admin@yendifplayer.com>
 * @copyright  Copyright (c) 2012 - 2025 PluginsWare Interactive Pvt. Ltd. All Rights Reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace PluginsWare\Component\YendifVideoShare\Administrator\Field;

\defined( '_JEXEC' ) or die;

use \Joomla\CMS\Factory;
use \Joomla\CMS\Form\Field\RadioField;
use \Joomla\CMS\Language\Text;
use \PluginsWare\Component\YendifVideoShare\Site\Helper\YendifVideoShareHelper;

/**
 * Class CustomradioField.
 *
 * @since  2.0.0
 */
class CustomradioField extends RadioField {

	/**
	 * The form field type.
	 *
	 * @var    string
	 * @since  2.0.0
	 */
	protected $type = 'customradio';

	/**
	 * Method to get the field input markup for a generic list.
	 * Use the multiple attribute to enable multiselect.
	 *
	 * @return  string  The field input markup.
	 *
	 * @since   2.0.0
	 */
	protected function getInput() {
		if ( ! YendifVideoShareHelper::canDo() ) {
			$extension = isset( $this->element['extension'] ) ? $this->element['extension'] : 'com_yendifvideoshare';
			return '<label class="control-label text-danger">' . Text::_( strtoupper( $extension ) . '_PREMIUM_LBL_PREMIUM_ONLY' ) . '</label>';
		}

		$data = $this->getLayoutData();

		$data['options'] = (array) $this->getOptions();

		return $this->getRenderer( $this->layout )->render( $data );
	}

}
