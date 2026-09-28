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

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Date\Date;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;
use PluginsWare\Component\YendifVideoShare\Site\Helper\YendifVideoShareHelper;
use PluginsWare\Component\YendifVideoShare\Site\Helper\YendifVideoSharePlayer;
use PluginsWare\Component\YendifVideoShare\Site\Helper\YendifVideoShareRoute;

$app = Factory::getApplication();

// Vars
$global       = ComponentHelper::getParams( 'com_yendifvideoshare' );
$itemid       = $app->input->getInt( 'Itemid', 0 );
$player_ratio = $this->params->get( 'ratio', 56.25 );
$image_ratio  = $this->params->get( 'image_ratio', $player_ratio );
$column_class = YendifVideoShareHelper::getCSSClassNames( $this->params, 'grid' );
$popup_class  = YendifVideoShareHelper::getCSSClassNames( $this->params, 'popup' );

$player_options = array(
	'ratio',
	'volume',
	'autoplay',
	'controlbar',
	'playbtn',
	'playpause',
	'currenttime',
	'progress',
	'duration',
	'volumebtn',
	'fullscreen',
	'embed',
	'share',
	'download'
);

$player_args = array(
	'uid'          => $itemid,
	'autoplaylist' => (int) $this->params->get( 'autoplaylist' ),
	'loop'         => (int) $this->params->get( 'loop' )
);

foreach ( $player_options as $option ) {
	$value   = $this->params->get( $option );
	$default = $global->get( $option );
	
	if ( $value != $default ) {
		$player_args[ $option ] = $value;
	}
}

$player_src = YendifVideoSharePlayer::getURL( $player_args );

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

<div id="yendif-video-share-category" class="yendif-video-share category mb-4">
	<?php if ( $this->params->get( 'show_page_heading' ) ) : ?>
		<div class="page-header">
			<h1>
				<?php if ( $this->escape( $this->params->get( 'page_heading' ) ) ) : ?>
					<?php echo $this->escape( $this->params->get( 'page_heading' ) ); ?>
				<?php else : ?>
					<?php echo $this->escape($this->params->get( 'page_title' ) ); ?>
				<?php endif; ?>

				<?php if ( $this->params->get( 'show_videos_count' ) ) : ?>
            		(<?php echo YendifVideoShareHelper::getVideosCount( $this->category->id, $this->params ); ?>)
            	<?php endif; ?>

				<?php if ( $this->params->get( 'show_feed' ) && $this->params->get( 'feed_icon' ) ) : ?>
					<a href="<?php echo YendifVideoShareRoute::getCategoryRoute( $this->category, $this->params->get( 'itemid_category' ), 0, 'feed' ); ?>" class="yendif-video-share-feed-btn" target="_blank">
						<img src="<?php echo $this->params->get( 'feed_icon' ); ?>" />
					</a>
				<?php endif; ?>
			</h1>
		</div>		
	<?php endif; ?>

	<?php if ( ! empty( $this->category->description ) ) : ?>
		<p class="yendif-video-share-description"><?php echo $this->category->description; ?></p>
	<?php endif; ?>

	<?php if ( ! empty( $this->items ) ) : ?>
		<div class="yendif-video-share-grid<?php echo $popup_class; ?> mb-4" data-player_ratio="<?php echo (float) $player_ratio; ?>">
			<div class="yendif-row">
				<?php foreach ( $this->items as $i => $item ) : 
					$route = YendifVideoShareRoute::getVideoRoute( $item, $this->params->get( 'itemid_video' ) );
					$item_link = Route::_( $route );							

					if ( $this->params->get( 'enable_popup' ) ) {
						$item_link = 'javascript:void(0)';
					}

					$player_args['videoid']  = $item->id;
					$player_args['autoplay'] = 1;
					$player_src = YendifVideoSharePlayer::getURL( $player_args );
					?>
					<div class="yendif-video-share-grid-item yendif-video-share-video-<?php echo (int) $item->id; ?> <?php echo $column_class; ?>" data-mfp-src="<?php echo $player_src; ?>">
						<div class="card">
							<a href="<?php echo $item_link; ?>" class="yendif-video-share-responsive-item" style="padding-bottom: <?php echo (float) $image_ratio; ?>%">
								<div class="yendif-video-share-image" style="background-image: url( '<?php echo YendifVideoShareHelper::getImage( $item ); ?>' );">&nbsp;</div>
								
								<svg class="yendif-video-share-svg-icon yendif-video-share-svg-icon-play" width="32" height="32" viewBox="0 0 32 32">
									<path d="M16 0c-8.837 0-16 7.163-16 16s7.163 16 16 16 16-7.163 16-16-7.163-16-16-16zM16 29c-7.18 0-13-5.82-13-13s5.82-13 13-13 13 5.82 13 13-5.82 13-13 13zM12 9l12 7-12 7z"></path>
								</svg>

								<?php if ( ! empty( $item->duration ) ) : ?>
									<span class="yendif-video-share-duration"><?php echo $item->duration; ?></span>
								<?php endif; ?> 
							</a>

							<div class="card-body">
								<div class="yendif-video-share-title">
									<a href="<?php echo $item_link; ?>" class="card-link"><?php echo YendifVideoShareHelper::Truncate( $item->title, $this->params->get( 'title_length', 0 ) ); ?></a>
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
									$prettyDate = $jdate->format( $this->params->get( 'date_format', Text::_( 'DATE_FORMAT_LC3' ) ) );

									$meta[] = sprintf(
										'<span class="yendif-video-share-meta-date"><span class="icon-calendar icon-fw"></span> %s</span>',
										$prettyDate
									);
								}

								// Views Count
								if ( $this->params->get( 'show_views' ) ) {
									$meta[] = sprintf(
										'<span class="yendif-video-share-meta-views"><span class="icon-eye icon-fw"></span> %s</span>',
										Text::sprintf( 'COM_YENDIFVIDEOSHARE_N_VIEWS_COUNT', $item->views )
									);
								}	
								
								if ( count( $meta ) ) {
									printf( 
										'<div class="yendif-video-share-meta text-muted small mt-1">%s</div>',
										implode( ' / ', $meta )
									);
								}
								?>			
								
								<?php 
								// Categories
								if ( $this->params->get( 'show_category' ) ) : ?>
									<div class="yendif-video-share-category text-muted small mt-1">
										<?php
										printf(
											'<span class="icon-folder-open icon-fw"></span> <a href="%s">%s</a>',
											YendifVideoShareRoute::getCategoryRoute( $this->category, $this->params->get( 'itemid_category' ) ),
											$this->category->title
										);
										?>
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

								<?php 
								// Short Description
								if ( $this->params->get( 'show_excerpt' ) && ! empty( $item->description ) ) : ?>
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

	<?php if ( ! empty( $this->subCategories ) ) : ?>
		<?php if ( ! empty( $this->items ) ) : ?>
			<p class="lead mt-4"><?php echo Text::_( 'COM_YENDIFVIDEOSHARE_TITLE_SUBCATEGORIES' ); ?></p>
		<?php endif; ?>

		<div class="yendif-video-share-grid">
			<div class="yendif-row">
				<?php foreach ( $this->subCategories as $i => $item ) : 
					$route = YendifVideoShareRoute::getCategoryRoute( $item, $this->params->get( 'itemid_category' ) );
					$item_link = Route::_( $route );
					?>
					<div class="<?php echo $column_class; ?>">
						<div class="card">
							<a href="<?php echo $item_link; ?>" class="yendif-video-share-responsive-item" style="padding-bottom: <?php echo $image_ratio; ?>%">
								<div class="yendif-video-share-image" style="background-image: url( '<?php echo YendifVideoShareHelper::getImage( $item ); ?>' );">&nbsp;</div>
							</a>

							<div class="card-body">
								<div class="yendif-video-share-title">
									<a href="<?php echo $item_link; ?>" class="card-link">
										<?php
										echo YendifVideoShareHelper::Truncate( $item->title, $this->params->get( 'title_length', 0 ) );

										if ( $this->params->get( 'show_videos_count' ) ) {
											$count = YendifVideoShareHelper::getVideosCount( $item->id, $this->params );
											echo ' (' . $count . ')';
										}
										?>
									</a>
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
		</div>
	<?php endif; ?>
</div>
