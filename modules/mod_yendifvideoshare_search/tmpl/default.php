<?php
/**
 * @version     2.1.1
 * @package     Com_YendifVideoShare
 * @subpackage  Mod_YendifVideoShare_Search
 * @author      PluginsWare Interactive Pvt. Ltd <admin@yendifplayer.com>
 * @copyright   Copyright (c) 2012 - 2025 PluginsWare Interactive Pvt. Ltd. All Rights Reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined( '_JEXEC' ) or die; 

use Joomla\CMS\Factory;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Language\Text;
use PluginsWare\Component\YendifVideoShare\Site\Helper\YendifVideoShareHelper;
use PluginsWare\Component\YendifVideoShare\Site\Helper\YendifVideoShareRoute;

// Vars
$context = 'com_yendifvideoshare.search';

$itemId = $params->get( 'itemid', 0 );
$route  = YendifVideoShareRoute::getSearchRoute( $itemId );

$search = $app->input->getString( 's', '' );

// Import CSS
$wa = $app->getDocument()->getWebAssetManager();

if ( ! $wa->assetExists( 'style', 'com_yendifvideoshare.site' ) ) {
	$wr = $wa->getRegistry();
	$wr->addRegistryFile( 'media/com_yendifvideoshare/joomla.asset.json' );
}

if ( $params->get( 'load_bootstrap' ) ) {
	$wa->useStyle( 'com_yendifvideoshare.bootstrap' );
}

$wa->useStyle( 'com_yendifvideoshare.site' );

if ( $css = $params->get( 'custom_css' ) ) {
    $wa->addInlineStyle( $css );
}
?>

<form class="yendif-video-share mod-yendifvideoshare-search" action="<?php echo Route::_( $route ); ?>" method="GET" role="search">
	<?php if ( ! YendifVideoShareHelper::isSEF() ) : ?>
		<input type="hidden" name="option" value="com_yendifvideoshare" />
		<input type="hidden" name="view" value="search" />
		<?php if ( ! empty( $itemId ) ) : ?>
			<input type="hidden" name="Itemid" value="<?php echo (int) $itemId; ?>" />
		<?php endif; ?>
	<?php endif; ?>

	<div class="input-group">
		<input type="text" name="s" class="form-control" placeholder="<?php echo Text::_( 'JSEARCH_FILTER_SUBMIT' ); ?>..." value="<?php echo htmlspecialchars( $search, ENT_COMPAT, 'UTF-8' ); ?>" />
		<button class="btn btn-primary" type="submit">
			<span class="icon-search icon-white" aria-hidden="true"></span> <?php echo Text::_( 'JSEARCH_FILTER_SUBMIT' ); ?>
		</button>
	</div>
</form>