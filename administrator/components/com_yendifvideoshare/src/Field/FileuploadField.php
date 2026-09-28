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

use \Joomla\CMS\Component\ComponentHelper;
use \Joomla\CMS\Factory;
use \Joomla\CMS\Form\Field\FileField;
use \Joomla\CMS\Language\Text;

/**
 * Class FileuploadField.
 *
 * @since  2.0.0
 */
class FileuploadField extends FileField {

	/**
	 * The form field type.
	 *
	 * @var    string
	 * @since  2.0.0
	 */
	protected $type = 'fileupload';

	/**
	 * Method to get the field input markup.
	 *
	 * @return  string  The field input markup.
	 *
	 * @since   2.0.0
	 */
	protected function getInput() {
		$app = Factory::getApplication();

		$canUpload = 1;
		if ( $app->isClient( 'site' ) ) {
			$params = ComponentHelper::getParams( 'com_yendifvideoshare' );
			if ( ! $params->get( 'allow_upload' ) ) {
				$canUpload = 0;
			}
		}

		$classes = array( 'form-control' );
		if ( ! empty( $this->class ) ) {
			$classes[] = $this->class;
		}

		$html = '<div class="yendif-video-share-form-field-type mb-2">';

		$html .= '<div class="form-check form-check-inline yendif-video-share-form-field-type-url">
			<input class="form-check-input" type="radio" name="' . $this->id . '_field_type" id="' . $this->id . '_field_type_url" value="url" checked="checked" />
			<label class="form-check-label" for="' . $this->id . '_field_type_url">' . Text::_( 'COM_YENDIFVIDEOSHARE_FORM_LBL_DIRECT_URL' ) . '</label>
		</div>';

		if ( $canUpload ) {
			$html .= '<div class="form-check form-check-inline yendif-video-share-form-field-type-upload">
				<input class="form-check-input" type="radio" name="' . $this->id . '_field_type" id="' . $this->id . '_field_type_upload" value="upload" />
				<label class="form-check-label" for="' . $this->id . '_field_type_upload">' . Text::_( 'COM_YENDIFVIDEOSHARE_FORM_LBL_UPLOAD_FILE' ) . '</label>
			</div>';
		}

		$html .= '</div>';

		$html .= '<div class="yendif-video-share-form-field-url">
			<input type="text" name="' . $this->name . '" id="' . $this->id . '_url" class="' . implode( ' ', $classes ) . '" placeholder="' . Text::_( 'COM_YENDIFVIDEOSHARE_FORM_DESC_DIRECT_URL' ) . '" value="' . $this->value . '" />
		</div>';

		if ( $canUpload ) {
			$html .= '<div class="yendif-video-share-form-field-upload" style="display: none">
				' . $this->getRenderer( $this->layout )->render( $this->getLayoutData() ) . '
			</div>';
		}

		return $html;
	}

}
