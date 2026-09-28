<?php
/**
 * @version     2.1.1
 * @package     Com_YendifVideoShare
 * @subpackage  Mod_YendifVideoShare_Playlist
 * @author      PluginsWare Interactive Pvt. Ltd <admin@yendifplayer.com>
 * @copyright   Copyright (c) 2012 - 2023 PluginsWare Interactive Pvt. Ltd. All Rights Reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined( '_JEXEC' ) or die;

use Joomla\CMS\Date\Date;
use Joomla\CMS\Factory;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;
use PluginsWare\Component\YendifVideoShare\Site\Helper\YendifVideoShareHelper;
use PluginsWare\Component\YendifVideoShare\Site\Helper\YendifVideoSharePlayer;
use PluginsWare\Component\YendifVideoShare\Site\Helper\YendifVideoShareRoute;
use PluginsWare\Module\YendifVideoSharePlaylist\Site\Helper\YendifVideoSharePlaylistHelper;

$app = Factory::getApplication();

$items = YendifVideoSharePlaylistHelper::getItems( $params );

// Player args
$uid = (int) $params->get( 'uid', @$module->id );

$args = array( 
	'uid' => $uid
);

$player_options = array(
	'ratio',
	'volume',
	'autoplay',
	'autoplaylist',
	'loop',
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

foreach ( $player_options as $option ) {
	$value = $params->get( $option, 'global' );
	if ( $value != 'global' ) {
		$args[ $option ] = $value;
	}
}

// Import CSS & JS
$wa = $app->getDocument()->getWebAssetManager();

if ( ! $wa->assetExists( 'style', 'com_yendifvideoshare.site' ) ) {
	$wr = $wa->getRegistry();
	$wr->addRegistryFile( 'media/com_yendifvideoshare/joomla.asset.json' );
}

if ( $params->get( 'load_bootstrap' ) ) {
	$wa->useStyle( 'com_yendifvideoshare.bootstrap' );
}

$wa->useStyle( 'com_yendifvideoshare.site' )
	->useScript( 'com_yendifvideoshare.site' );

$inlineStyle = $params->get( 'custom_css' );

if ( $params->get( 'playlist_position' ) == 'right' ) {
	$inlineStyle .= "
		#yendif-video-share-playlist-" . $uid . " .yendif-video-share-playlist-player {
			width: calc(100% - " . (int) $params->get( 'playlist_width', 250 ) . "px);
		}

		#yendif-video-share-playlist-" . $uid . " .yendif-video-share-playlist-videos {
			width: " . (int) $params->get( 'playlist_width', 250 ) . "px;
		}

		@media only screen and (max-width: 768px) {
			#yendif-video-share-playlist-" . $uid . " .yendif-video-share-playlist-player,
			#yendif-video-share-playlist-" . $uid. " .yendif-video-share-playlist-videos {
				width: 100%;
			}

			#yendif-video-share-playlist-" . $uid . " .yendif-video-share-playlist-videos {
				max-height: " . (int) $params->get( 'playlist_height', 250 ) . "px;
			}
		}
	";
} else {
	$inlineStyle .= "
		#yendif-video-share-playlist-" . $uid . " .yendif-video-share-playlist-videos {
			max-height: " . (int) $params->get( 'playlist_height', 250 ) . "px;
		}
	";
}

$wa->addInlineStyle( $inlineStyle );
?>

<div id="yendif-video-share-playlist-<?php echo $uid; ?>" class="yendif-video-share exeltis mod-yendifvideoshare-playlist">
	<?php if ( empty( $items ) ) : ?>
		<p class="text-muted">
			<?php echo Text::_( 'MOD_YENDIFVIDEOSHARE_PLAYLIST_NO_ITEMS_FOUND' ); ?>
		</p>
	<?php else : ?>
		<div class="yendif-video-share-playlist exeltis">
			<!-- Player -->
			<div class="yendif-video-share-playlist-player">
				<?php
				$args['videoid'] = $items[0]->id;
				echo YendifVideoSharePlayer::load( $args, $params );
				?>
			</div>

			<!-- Playlist -->
			<div class="yendif-video-share-playlist-videos <?php echo $params->get( 'playlist_position', 'right' ); ?>">
				<div class="yendif-video-share-playlist-items">
					<?php foreach ( $items as $index => $item ) :
						$args['videoid']  = $item->id;
						$args['autoplay'] = 1;

						$iframe_src = YendifVideoSharePlayer::getURL( $args );
						?>
						<div class="yendif-video-share-playlist-item<?php if( $index == 0 ) echo ' active'; ?>" data-index="<?php echo (int) $index; ?>" data-src="<?php echo $iframe_src; ?>">
							<div class="playlist-item d-flex p-2">
								<div class="flex-shrink-0">
									<div class="yendif-video-share-responsive-item" style="padding-bottom: <?php echo (float) $params->get( 'image_ratio', 56.25 ); ?>%">
										<div class="yendif-video-share-image" style="background-image: url( '<?php echo YendifVideoShareHelper::getImage( $item ); ?>' );">&nbsp;</div>

										<?php if ( ! empty( $item->duration ) ) : ?>
											<span class="yendif-video-share-duration small"><?php echo $item->duration; ?></span>
										<?php endif; ?> 
									</div>
								</div>
								<div class="flex-grow-1 ms-3">
									<div class="yendif-video-share-title">
										<?php echo YendifVideoShareHelper::Truncate( $item->title, $params->get( 'title_length', 0 ) ); ?>
									</div>									

									<?php
									$meta = array();
											
									// Author Name
									if ( $params->get( 'show_user' ) ) {
										$meta[] = sprintf(
											'<span class="yendif-video-share-meta-user"><span class="icon-user icon-fw"></span> %s</span>',
											Factory::getUser( $item->userid )->username
										);
									}

									// Date Added
									if ( $params->get( 'show_date' ) ) {
										$date  = ( ! empty( $item->published_up ) && $item->published_up !== '0000-00-00 00:00:00' ) ? $item->published_up : $item->created_date;
										$jdate = new Date( $date );
										$prettyDate = $jdate->format( Text::_( 'DATE_FORMAT_LC3' ) );

										$meta[] = sprintf(
											'<span class="yendif-video-share-meta-date"><span class="icon-calendar icon-fw"></span> %s</span>',
											$prettyDate
										);
									}

									// Views Count
									if ( $params->get( 'show_views' ) ) {
										$meta[] = sprintf(
											'<span class="yendif-video-share-meta-views"><span class="icon-eye icon-fw"></span> %s</span>',
											Text::sprintf( 'MOD_YENDIFVIDEOSHARE_PLAYLIST_N_VIEWS_COUNT', $item->views )
										);
									}	
									
									if ( count( $meta ) ) {
										printf( 
											'<div class="yendif-video-share-meta text-muted small mt-1">%s</div>',		
											implode('', $meta)
										);
									}
									?>	

									<?php 
									// Categories
									if ( $params->get( 'show_category' ) && $category = YendifVideoShareHelper::getCategory( $item->catid, array( 'id', 'title', 'alias' ) ) ) : ?>
										<div class="yendif-video-share-category text-muted small mt-1">
											<?php
											printf(
												'<span class="icon-folder-open icon-fw"></span> <a href="%s" class="text-muted">%s</a>',
												YendifVideoShareRoute::getCategoryRoute( $category, $params->get( 'itemid_category' ) ),
												$category->title
											);
											?>
										</div>
									<?php endif; ?>
									
									<?php 
									// Ratings
									if ( $params->get( 'show_rating' ) ) : ?>
										<div class="yendif-video-share-ratings-small mt-1">
											<span class="yendif-video-share-ratings-stars">
												<span class="yendif-video-share-ratings-current" style="width: <?php echo (float) $item->rating; ?>%;"></span>
											</span>
										</div>
									<?php endif; ?>

									<?php 
									// Short Description
									if ( $params->get( 'show_excerpt' ) && ! empty( $item->description ) ) : ?>
										<div class="yendif-video-share-excerpt text-muted small mt-2">
											<?php echo YendifVideoShareHelper::Truncate( $item->description, $params->get( 'excerpt_length' ) ); ?>
										</div>
									<?php endif; ?>
								</div>
							</div>
						</div>
					<?php endforeach; ?>
				</div>
			</div>
		</div>	
	<?php endif; ?>
</div>
