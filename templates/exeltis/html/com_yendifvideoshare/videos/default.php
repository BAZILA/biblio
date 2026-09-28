<?php
/**
 * @version    2.1.1
 * @package    Com_YendifVideoShare
 * @author     PluginsWare Interactive Pvt. Ltd <admin@yendifplayer.com>
 * @copyright  Copyright (c) 2012 - 2023 PluginsWare Interactive Pvt. Ltd. All Rights Reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access
defined('_JEXEC') or die;
use Joomla\CMS\Uri\Uri;


use Joomla\CMS\Date\Date;
use Joomla\CMS\Factory;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Language\Text;

use PluginsWare\Component\YendifVideoShare\Site\Helper\YendifVideoShareHelper;
use PluginsWare\Component\YendifVideoShare\Site\Helper\YendifVideoShareRoute;

$app = Factory::getApplication();

// Vars
$player_ratio = $this->params->get( 'ratio', 56.25 );
$image_ratio  = $this->params->get( 'image_ratio', $player_ratio );
$column_class = YendifVideoShareHelper::getCSSClassNames( $this->params, 'grid' );
$popup_class  = YendifVideoShareHelper::getCSSClassNames( $this->params, 'popup' );

// Import CSS
$wa = $app->getDocument()->getWebAssetManager();

if ( $this->params->get( 'load_bootstrap' ) ) {
	$wa->useStyle( 'com_yendifvideoshare.bootstrap' );
}

if ( $this->params->get( 'enable_popup' ) ) {
	$wa->useStyle( 'com_yendifvideoshare.popup' )
		->useScript( 'com_yendifvideoshare.popup' )
		->useScript( 'com_yendifvideoshare.site' );
}

$wa->useStyle( 'com_yendifvideoshare.site' );

if ( $css = $this->params->get( 'custom_css' ) ) {
    $wa->addInlineStyle( $css );
}
?>

<div id="yendif-video-share-videos" class="yendif-video-share videos exeltis mb-4">
	<?php if ( $this->params->get( 'show_page_heading' ) ) : ?>
		<div class="page-header">
			<h1>
				<?php if ( $this->escape( $this->params->get( 'page_heading' ) ) ) : ?>
					<?php echo $this->escape( $this->params->get( 'page_heading' ) ); ?>
				<?php else : ?>
					<?php echo $this->escape( $this->params->get( 'page_title' ) ); ?>
				<?php endif; ?>

				<?php if ( $this->params->get( 'show_feed' ) && $this->params->get( 'feed_icon' ) ) : ?>
					<a href="<?php echo Route::_( 'index.php?option=com_yendifvideoshare&view=videos&format=feed' ); ?>">
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
		<div class="yendif-video-share-grid<?php echo $popup_class; ?>" data-player_ratio="<?php echo (float) $player_ratio; ?>">
			<div class="yendif-row">
				<?php foreach ( $this->items as $i => $item ) : 
					$route = YendifVideoShareRoute::getVideoRoute( $item, $this->params->get( 'itemid_video' ) );
					$item_link = Route::_( $route );

					if ( $this->params->get( 'popup' ) ) {
						$item_link = 'javascript:void(0)';
					}			

					$iframe_src = URI::root() . 'index.php?option=com_yendifvideoshare&view=player&id=' . $item->id . "&format=raw&autoplay=1";
					?>
					<div class="yendif-video-share-grid-item yendif-video-share-video-<?php echo (int) $item->id; ?> <?php echo $column_class; ?>" data-mfp-src="<?php echo $iframe_src; ?>">
						<div class="card box-shadow">
							<a href="<?php echo $item_link; ?>" class="yendif-video-share-responsive-item" style="padding-bottom: <?php echo (float) $image_ratio; ?>%">
								<div class="yendif-video-share-image" style="background-image: url( '<?php echo YendifVideoShareHelper::getImage( $item ); ?>' );">&nbsp;</div>
					<img class="yendif-play-icon" src="<?php echo URI::root( true ); ?>/templates/exeltis/images/play-btn.png" alt="High Rise Construction Video HD">
								<?php if ( ! empty( $item->duration ) ) : ?>
									<span class="yendif-video-share-duration small"><?php echo $item->duration; ?></span>
								<?php endif; ?> 
							</a>

							<div class="card-body">
								<div class="yendif-video-share-title"><h4>
									<a href="<?php echo $item_link; ?>" class="card-link"><?php echo YendifVideoShareHelper::Truncate( $item->title, $this->params->get( 'title_length', 0 ) ); ?></a></h4>
								</div>								

								<?php
								$meta = array();
										
								// Author Name
								if ( $this->params->get( 'show_user' ) ) {
									$meta[] = sprintf(
										'<span class="yendif-video-share-meta-user"><span class="icon-user icon-fw"></span> %s</span>',
										Factory::getUser( $item->userid )->username
									);
								}

								// Date Added
								if ( $this->params->get( 'show_date' ) ) {
									$date  = ( ! empty( $item->published_up ) && $item->published_up !== '0000-00-00 00:00:00' ) ? $item->published_up : $item->created_date;
									$jdate = new Date( $date );
									$prettyDate = $jdate->format( Text::_( 'DATE_FORMAT_LC3' ) );

									$meta[] = sprintf(
										'<span class="yendif-video-share-meta-date"><span class="icon-calendar icon-fw"></span> %s</span>',
										$prettyDate
									);
								}

								
								?>	
								
								<?php
								// Categories
								if ( $this->params->get( 'show_category' ) && $category = YendifVideoShareHelper::getCategory( $item->catid, array( 'id', 'title', 'alias' ) ) ) : ?> 
									<div class="yendif-video-share-category text-muted small mt-1">
										<?php
										printf(
											'<span class="icon-folder-open icon-fw"></span> <a href="%s">%s</a>',
											YendifVideoShareRoute::getCategoryRoute( $category, $this->params->get( 'itemid_category' ) ),
											$category->title
										);
										?>
									</div>
								<?php endif; ?>

								

								<?php 
								// Short Description
								if ( $this->params->get( 'show_excerpt' ) && ! empty( $item->description ) ) : ?>
									<div class="yendif-video-share-excerpt mt-2">
										<?php echo YendifVideoShareHelper::Truncate( $item->description, $this->params->get( 'excerpt_length' ) ); ?>
									</div>
								<?php endif; ?>	
								<?php 
								// Ratings
								if ( $this->params->get( 'show_rating' ) ) : ?>
									<div class="yendif-video-share-ratings-small mt-1">
										<span class="yendif-video-share-ratings-stars">
											<span class="yendif-video-share-ratings-current" style="width: <?php echo (float) $item->rating; ?>%;"></span>
										</span>
									</div>
								<?php endif; ?>		
								<?php // Views Count
								if ( $this->params->get( 'show_views' ) ) {
									$meta[] = sprintf(
										'<span class="yendif-video-share-meta-views"><span class="icon-eye icon-fw"></span> %s</span>',
										Text::sprintf( 'COM_YENDIFVIDEOSHARE_N_VIEWS_COUNT', $item->views )
									);
								}	
								
								if ( count( $meta ) ) {
									printf( 
										'<div class="yendif-video-share-meta text-muted  mt-1">%s</div>',
										
implode('', $meta)
									);
								}
								?>					 
							</div>					
						</div>
					</div>
				<?php endforeach; ?>
			</div>

			<?php echo $this->pagination->getListFooter(); ?>
		</div>		
	<?php endif; ?>
</div>
