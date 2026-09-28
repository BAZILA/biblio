<?php
/**
 * @version    2.1.1
 * @package    Com_YendifVideoShare
 * @author     PluginsWare Interactive Pvt. Ltd <admin@yendifplayer.com>
 * @copyright  Copyright (c) 2012 - 2025 PluginsWare Interactive Pvt. Ltd. All Rights Reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace PluginsWare\Component\YendifVideoShare\Site\View\Player;

// No direct access
\defined( '_JEXEC' ) or die;

use \Joomla\CMS\Factory;
use \Joomla\CMS\Language\Text;
use \Joomla\CMS\MVC\View\AbstractView;
use \Joomla\CMS\Router\Route;
use \Joomla\CMS\Uri\Uri;
use \Joomla\Component\Content\Site\Helper\RouteHelper;
use \PluginsWare\Component\YendifVideoShare\Site\Helper\YendifVideoShareHelper;

/**
 * Frontpage View class
 *
 * @since  2.0.0
 */
class RawView extends AbstractView {

	protected $state;

	protected $params;

	protected $item;	

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
		$user = Factory::getUser();

		$this->state  = $this->get( 'State' );
		$this->params = $this->get( 'Params' );
		$this->item   = $this->get( 'Item' ); 
		
		if ( empty( $this->item ) ) {
            return false;
        }			

		if ( ! empty( $this->item->access ) && ! in_array( $this->item->access, $user->getAuthorisedViewLevels() ) && $this->item->userid != $user->id ) {
			require JPATH_ROOT . '/components/com_yendifvideoshare/tmpl/player/access.php';
			return false;
        }

		if ( $this->params->get( 'show_consent' ) && $app->input->cookie->get( 'com_yendifvideoshare_gdpr', null ) == null ) {
			if ( in_array( $this->item->type, array( 'youtube', 'vimeo', 'thirdparty' ) ) ) {
				require JPATH_ROOT . '/components/com_yendifvideoshare/tmpl/player/gdpr.php';
				return false;
			}
		}

		if ( $this->item->type == 'thirdparty' ) {
			require JPATH_ROOT . '/components/com_yendifvideoshare/tmpl/player/iframe.php';
		} else {
			$this->canDo = YendifVideoShareHelper::canDo(); 
			require JPATH_ROOT . '/components/com_yendifvideoshare/tmpl/player/html5.php';
		}	
	}

	public function getTitle() {	
		$app = Factory::getApplication();

		$title = $this->item->title;

		if ( $this->item->id > 0 ) {
			if ($app->get( 'sitename_pagetitles', 0 ) == 1 ) {
				$title = Text::sprintf( 'JPAGETITLE', $app->get( 'sitename' ), $title );
			} elseif ($app->get( 'sitename_pagetitles', 0 ) == 2 ) {
				$title = Text::sprintf( 'JPAGETITLE', $title, $app->get( 'sitename' ) );
			}
		}
	
		return $title;
    }

	public function getURL() {
		if ( $this->item->id > 0 ) {
			return Route::_( 'index.php?option=com_yendifvideoshare&view=video&id=' . $this->item->id . ':' . $this->item->alias, true, 0, true );
		} else {
			return Uri::root();
		}        	
    }

	public function hasAds() {
		if ( $this->canDo ) {
			if ( $this->params->get( 'ad_engine' ) == 'custom' ) {
				$lang = Factory::getLanguage();
				$locales = $lang->getLocale();

				if ( $this->params->get( 'enable_adverts' ) != 'none' ) {
					$this->params->set( 'vasturl', URI::root() . 'index.php?option=com_yendifvideoshare&view=ads&type=vmap&id=' . $this->item->id . '&format=xml&lang=' . $locales[4] );
					return true;
				}
			} elseif ( $this->params->get( 'ad_engine' ) == 'vast' ) {
				if ( $this->params->get( 'vasturl' ) != '' ) {
					return true;
				}
			}
		}

        return false;
    }

	public function getIpAddress() {
        // Whether ip is from share internet
        if ( ! empty( $_SERVER['HTTP_CLIENT_IP'] ) ) {
            $ip_address = $_SERVER['HTTP_CLIENT_IP'];
        }
        
        // Whether ip is from proxy
        elseif ( ! empty( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) {
            $ip_address = $_SERVER['HTTP_X_FORWARDED_FOR'];
        }
        
        // Whether ip is from remote address
        else {
            $ip_address = $_SERVER['REMOTE_ADDR'];
        }
        
        return $ip_address;        
    }

}
