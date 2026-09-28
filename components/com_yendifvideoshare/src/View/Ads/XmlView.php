<?php
/**
 * @version    2.1.1
 * @package    Com_YendifVideoShare
 * @author     PluginsWare Interactive Pvt. Ltd <admin@yendifplayer.com>
 * @copyright  Copyright (c) 2012 - 2025 PluginsWare Interactive Pvt. Ltd. All Rights Reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace PluginsWare\Component\YendifVideoShare\Site\View\Ads;

// No direct access
\defined( '_JEXEC' ) or die;

use \Joomla\CMS\Component\ComponentHelper;
use \Joomla\CMS\Factory;
use \Joomla\CMS\MVC\View\AbstractView;
use \Joomla\CMS\Uri\Uri;

/**
 * Frontpage View class
 *
 * @since  2.0.0
 */
class XmlView extends AbstractView {

	protected $params;

	/**
	 * Display the view
	 *
	 * @param   string  $tpl  Template name
	 *
	 * @return  void
	 *
	 * @since   2.0.0
	 * @throws  Exception
	 */
	public function display( $tpl = null ) {
		$app = Factory::getApplication();

		$this->params = ComponentHelper::getParams( 'com_yendifvideoshare' );
		$this->setHeader();

		$type = $app->input->getCmd( 'type' );		
		if ( empty( $type ) ) {
			$type = $app->input->getCmd( 'task', 'vmap' ); // Fallback to our old versions
		}		

		if ( $type == 'vmap' ) {
			$this->vmap();
		} else {
			$this->vast();
		}
	}

	private function vmap() {
		$item = $this->get( 'Video' );

		$preroll_id = 0;
		$postroll_id = 0;

		if ( in_array( $this->params->get( 'enable_adverts' ), array( 'preroll_only', 'preroll', 'both' ) ) ) {
			if ( empty( $item->preroll ) || $item->preroll == -1 ) {
				$preroll_id = $this->get( 'PrerollId' );
			} else {
				$preroll_id = $item->preroll;
			}			
		}
        
		if ( in_array( $this->params->get( 'enable_adverts' ), array( 'postroll_only', 'postroll', 'both' ) ) ) {
			if ( empty( $item->postroll ) || $item->postroll == -1 ) {
				$postroll_id = $this->get( 'PostrollId' );
			} else {
				$postroll_id = $item->postroll;
			}	
		}

		$lang = Factory::getLanguage();
		$locales = $lang->getLocale();

		include JPATH_ROOT . '/components/com_yendifvideoshare/tmpl/ads/vmap.php';
 	}

	private function vast() {
		$item = $this->get( 'Ad' );        
        if ( empty( $item ) ) {
            return;
        }

		$app = Factory::getApplication();
		$siteName = $app->get( 'sitename' );

		$timeFormat = 0;
		if ( $this->params->get( 'can_skip_adverts' ) == 1 ) {
			$duration = $this->params->get( 'show_skip_adverts_on' );
			$timeFormat = gmdate( "H:i:s", $duration );
		}

		$pixelImage = Uri::root() . 'media/com_yendifvideoshare/images/pixel.png';

		include JPATH_ROOT . '/components/com_yendifvideoshare/tmpl/ads/vast.php';
	}

	public function setHeader() {
        $u = Uri::getInstance( Uri::base() );
		if ( $u->getScheme() ) {
			$origin = $u->getScheme() . '://imasdk.googleapis.com';
        } else {
            $origin = 'https://imasdk.googleapis.com';
        }

        $app = Factory::getApplication();
        $app->setHeader( 'Access-Control-Allow-Origin', $origin );
        $app->setHeader( 'Access-Control-Allow-Credentials', 'true' );
    }

}
