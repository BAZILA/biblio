<?php
/**
 * @version    2.1.1
 * @package    Com_YendifVideoShare
 * @author     PluginsWare Interactive Pvt. Ltd <admin@yendifplayer.com>
 * @copyright  Copyright (c) 2012 - 2025 PluginsWare Interactive Pvt. Ltd. All Rights Reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access
defined( '_JEXEC' ) or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Layout\LayoutHelper;

$displayData = [
	'textPrefix' => 'COM_YENDIFVIDEOSHARE_ADVERTS',
	'formURL' => 'index.php?option=com_yendifvideoshare&view=adverts',
	'helpURL' => 'https://yendifplayer.com/',
	'icon' => 'icon-copy',
];

$user = Factory::getApplication()->getIdentity();

if ( $user->authorise( 'core.create', 'com_yendifvideoshare' ) || count( $user->getAuthorisedCategories( 'com_yendifvideoshare', 'core.create' ) ) > 0 ) {
	$displayData['createURL'] = 'index.php?option=com_yendifvideoshare&view=adverts&task=advert.add';
}

echo LayoutHelper::render( 'joomla.content.emptystate', $displayData );
