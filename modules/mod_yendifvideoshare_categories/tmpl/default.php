<?php
/**
 * @version     2.1.1
 * @package     Com_YendifVideoShare
 * @subpackage  Mod_YendifVideoShare_Categories
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
use PluginsWare\Module\YendifVideoShareCategories\Site\Helper\YendifVideoShareCategoriesHelper;

$app = Factory::getApplication();

$items = YendifVideoShareCategoriesHelper::getItems( $params );

// Vars
$player_ratio = $params->get( 'ratio', 56.25 );
$image_ratio  = $params->get( 'image_ratio', $player_ratio );
$column_class = YendifVideoShareHelper::getCSSClassNames( $params, 'grid' );

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

<div class="yendif-video-share mod-yendifvideoshare-categories">
	<?php if ( empty( $items ) ) : ?>
		<p class="text-muted">
			<?php echo Text::_( 'MOD_YENDIFVIDEOSHARE_CATEGORIES_NO_ITEMS_FOUND' ); ?>
		</p>
	<?php else : ?>
		<div class="yendif-video-share-grid">
			<div class="yendif-row">
				<?php foreach ( $items as $i => $item ) : 
					$route = YendifVideoShareRoute::getCategoryRoute( $item, (int) $params->get( 'itemid', 0 ) );
					$item_link = Route::_( $route );
					?>
					<div class="<?php echo $column_class; ?>">
						<div class="card">
							<a href="<?php echo $item_link; ?>" class="yendif-video-share-responsive-item" style="padding-bottom: <?php echo (float) $image_ratio; ?>%">
								<div class="yendif-video-share-image" style="background-image: url( '<?php echo YendifVideoShareHelper::getImage( $item ); ?>' );">&nbsp;</div>
							</a>

							<div class="card-body">
								<div class="yendif-video-share-title">
									<a href="<?php echo $item_link; ?>" class="card-link">
										<?php 
										echo YendifVideoShareHelper::Truncate( $item->title, $params->get( 'title_length', 0 ) );

										if ( $params->get( 'show_videos_count' ) ) {
											$count = YendifVideoShareHelper::getVideosCount( $item->id, $params );
											echo ' (' . $count . ')';
										}
										?>
									</a>
								</div>

								<?php if ( $params->get( 'show_excerpt' ) && ! empty( $item->description ) ) : ?>
									<div class="yendif-video-share-excerpt mt-2">
										<?php echo YendifVideoShareHelper::Truncate( $item->description, $params->get( 'excerpt_length' ) ); ?>
									</div>
								<?php endif; ?>
							</div>					
						</div>
					</div>
				<?php endforeach; ?>
			</div>

			<?php 
			// More Button
			if ( $params->get( 'show_more_btn' ) ) {
			 	$more_btn_link = $params->get( 'more_btn_link' );

				if ( empty( $more_btn_link ) ) {					
					$itemid = (int) $params->get( 'itemid', 0 );
					$catid  = (int) $params->get( 'catid', 0 );

					$route = YendifVideoShareRoute::getCategoriesRoute( $itemid, $catid );
					if ( $route ) {
						$more_btn_link = Route::_( $route );
					}
				}

				if ( ! empty( $more_btn_link ) ) {
					printf(
						'<a class="btn btn-primary mt-4" href="%s">%s</a>',
						$more_btn_link,
						$params->get( 'more_btn_label', Text::_( 'MOD_YENDIFVIDEOSHARE_CATEGORIES_SHOW_MORE_BUTTON_LABEL' ) )
					);
				}
			}
			?>
		</div>		
	<?php endif; ?>
</div>
