<?php
/**
 * @version    2.1.1
 * @package    Com_YendifVideoShare
 * @author     PluginsWare Interactive Pvt. Ltd <admin@yendifplayer.com>
 * @copyright  Copyright (c) 2012 - 2023 PluginsWare Interactive Pvt. Ltd. All Rights Reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access
defined( '_JEXEC' ) or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;
use PluginsWare\Component\YendifVideoShare\Site\Helper\YendifVideoShareHelper;
use PluginsWare\Component\YendifVideoShare\Site\Helper\YendifVideoShareRoute;

$app = Factory::getApplication();

// Vars
$player_ratio = $this->params->get( 'ratio', 56.25 );
$image_ratio  = $this->params->get( 'image_ratio', $player_ratio );
$column_class = YendifVideoShareHelper::getCSSClassNames( $this->params, 'grid' );

// Import CSS
$wa = $app->getDocument()->getWebAssetManager();

if ( $this->params->get( 'load_bootstrap' ) ) {
	$wa->useStyle( 'com_yendifvideoshare.bootstrap' );
}

$wa->useStyle( 'com_yendifvideoshare.site' );

if ( $css = $this->params->get( 'custom_css' ) ) {
    $wa->addInlineStyle( $css );
}
?>

<div id="yendif-video-share-categories" class="yendif-video-share categories exeltis mb-4">
	<?php if ( $this->params->get( 'show_page_heading' ) ) : ?>
		<div class="page-header">
			<h1>
				<?php if ( $this->escape( $this->params->get( 'page_heading' ) ) ) : ?>
					<?php echo $this->escape( $this->params->get( 'page_heading' ) ); ?>
				<?php else : ?>
					<?php echo $this->escape($this->params->get( 'page_title' ) ); ?>
				<?php endif; ?>

				<?php if ( $this->params->get( 'show_feed' ) && $this->params->get( 'feed_icon' ) ) : ?>
					<a href="<?php echo Route::_( 'index.php?option=com_yendifvideoshare&view=videos&format=feed' ); ?>" class="yendif-video-share-feed-btn" target="_blank">
						<img src="<?php echo $this->params->get( 'feed_icon' ); ?>" />
					</a>
				<?php endif; ?>
			</h1>
		</div>
	<?php endif; ?>

	<?php if ( empty( $this->items ) ) : ?>
		<div class="alert alert-info">
			<?php echo Text::_( 'COM_YENDIFVIDEOSHARE_NO_ITEMS_FOUND' ); ?>
		</div>
	<?php else : ?>
		<div class="yendif-video-share-grid">
			<div class="yendif-row">
				<?php foreach ( $this->items as $i => $item ) : 
					$route = YendifVideoShareRoute::getCategoryRoute( $item, $this->params->get( 'itemid_category' ) );
					$item_link = Route::_( $route );
					?>
					<div class="<?php echo $column_class; ?>">
						<div class="card box-shadow">
							<a href="<?php echo $item_link; ?>" class="yendif-video-share-responsive-item" style="padding-bottom: <?php echo (float) $image_ratio; ?>%">
								<div class="yendif-video-share-image" style="background-image: url( '<?php echo YendifVideoShareHelper::getImage( $item ); ?>' );">&nbsp;</div>
							</a>

							<div class="card-body">
								<div class="yendif-video-share-title"><h3>
									<a href="<?php echo $item_link; ?>" class="card-link">
										<?php
										echo YendifVideoShareHelper::Truncate( $item->title, $this->params->get( 'title_length', 0 ) );

										if ( $this->params->get( 'show_videos_count' ) ) {
											$count = YendifVideoShareHelper::getVideosCount( $item->id, $this->params );
											echo ' (' . $count . ')';
										}
										?>
									</a></h3>
								</div>

								<?php if ( $this->params->get( 'show_excerpt' ) && ! empty( $item->description ) ) : ?>
									<div class="yendif-video-share-excerpt mt-2">
										<?php echo YendifVideoShareHelper::Truncate( $item->description, $this->params->get( 'excerpt_length' ) ); ?>
									</div>
								<?php endif; ?>
							</div>					
						</div>
					</div>
				<?php endforeach; ?>
			</div>

			<?php echo $this->pagination->getListFooter(); ?>
		</div>		
	<?php endif; ?>
</div>
