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

use Joomla\CMS\Date\Date;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;
use PluginsWare\Component\YendifVideoShare\Site\Helper\YendifVideoShareHelper;
use PluginsWare\Component\YendifVideoShare\Site\Helper\YendifVideoShareHtml;
use PluginsWare\Component\YendifVideoShare\Site\Helper\YendifVideoSharePlayer;
use PluginsWare\Component\YendifVideoShare\Site\Helper\YendifVideoShareRoute;

$app = Factory::getApplication();

// Import CSS & JS
$wa = $app->getDocument()->getWebAssetManager();

if ( $this->params->get( 'load_bootstrap' ) ) {
	$wa->useStyle( 'com_yendifvideoshare.bootstrap' );
}

if ( $this->params->get( 'enable_popup' ) ) {
	$wa->useStyle( 'com_yendifvideoshare.popup' )
		->useScript( 'com_yendifvideoshare.popup' );
}

$wa->useStyle( 'com_yendifvideoshare.site' );

if ( $css = $this->params->get( 'custom_css' ) ) {
    $wa->addInlineStyle( $css );
}

$wa->useScript( 'com_yendifvideoshare.site' );

$inlineScript = "
	if ( typeof( yendif ) === 'undefined' ) {
		var yendif = {};
	};

	yendif.baseurl = '" . URI::root() . "';
	yendif.userid = " . Factory::getUser()->get( 'id' ) . ";
	yendif.allow_guest_like = " . $this->params->get( 'allow_guest_like' ) . ";
	yendif.allow_guest_rating = " . $this->params->get( 'allow_guest_rating' ) . ";
	yendif.message_login_required = '" . Text::_( 'COM_YENDIFVIDEOSHARE_ALERT_MSG_LOGIN_REQUIRED' ) . "';
";

$wa->addInlineScript( $inlineScript, [ 'position' => 'before' ], [], [ 'com_yendifvideoshare.site' ] );
?>

<div id="yendif-video-share-video" class="yendif-video-share video mb-4">    
	<?php 
	// Search Form
	if ( $this->hasAccess && $this->params->get( 'show_search' ) ) :
		$route = YendifVideoShareRoute::getSearchRoute(); 
		?>
		<form class="yendif-video-share-search-form mb-4" action="<?php echo Route::_( $route ); ?>" method="GET" role="search">
			<?php if ( ! YendifVideoShareHelper::isSEF() ) : ?>
				<input type="hidden" name="option" value="com_yendifvideoshare" />
				<input type="hidden" name="view" value="search" />
				<input type="hidden" name="Itemid" value="<?php echo $app->input->getInt( 'Itemid' ); ?>" />
			<?php endif; ?>

			<div class="input-group">
				<input type="text" name="s" class="form-control" placeholder="<?php echo Text::_( 'JSEARCH_FILTER_SUBMIT' ); ?>..." />
				<button class="btn btn-primary" type="submit">
					<span class="icon-search icon-white" aria-hidden="true"></span> <?php echo Text::_( 'JSEARCH_FILTER_SUBMIT' ); ?>
				</button>
			</div>
		</form>
	<?php endif; ?>

	<?php
	// Video Player
	$args = array( 
		'videoid' => $this->video->id,
		'Itemid'  => $app->input->getInt( 'Itemid' )
	);

	echo '<div class="yendif-video-share-player mb-4">' . YendifVideoSharePlayer::load( $args, $this->params ) . '</div>';
	?>

	<div class="yendif-video-share-info mb-4">
		<?php 
		// Video Title
		if ( $this->params->get( 'show_page_heading' ) ) : ?>
			<h2 class="yendif-video-share-title mt-0 mb-1">
				<?php if ( $this->escape( $this->params->get( 'page_heading' ) ) ) : ?>
					<?php echo $this->escape( $this->params->get( 'page_heading' ) ); ?>
				<?php else : ?>
					<?php echo $this->escape($this->params->get( 'page_title' ) ); ?>
				<?php endif; ?>
			</h2>
		<?php endif; ?>

		<?php
		$meta = array();

		// Author Name
		if ( $this->params->get( 'show_user' ) ) {
			$meta[] = sprintf(
				'<span class="yendif-video-share-meta-user"><span class="icon-user icon-fw"></span> %s</span>',
				Factory::getUser( $this->video->userid )->username
			);
		}
				
		// Date Added
		if ( $this->params->get( 'show_date' ) ) {
			$date  = ( ! empty( $this->video->published_up ) && $this->video->published_up !== '0000-00-00 00:00:00' ) ? $this->video->published_up : $this->video->created_date;
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
				Text::sprintf( 'COM_YENDIFVIDEOSHARE_N_VIEWS_COUNT', $this->video->views )
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
		if ( $this->hasAccess && $this->params->get( 'show_category' ) ) : ?>
			<div class="yendif-video-share-category text-muted small mt-1">
				<?php
				printf(
					'<span class="icon-folder-open icon-fw"></span> <a href="%s">%s</a>',
					YendifVideoShareRoute::getCategoryRoute( $this->video->category, $this->params->get( 'itemid_category' ) ),
					$this->escape( $this->video->category->title )
				);
				?>
			</div>
		<?php endif; ?>

		<?php
		// Ratings & Likes
		if ( $this->hasAccess && ( $this->params->get( 'show_rating' ) || $this->params->get( 'show_likes' ) ) ) : ?>
			<?php if ( $this->params->get( 'show_rating' ) ) : ?>
				<div id="yendif-video-share-ratings-widget" class="mt-1">
					<?php echo YendifVideoShareHtml::RatingWidget( $this->video, $this->params ); ?>
				</div>
			<?php endif; ?>	
			
			<?php if ( $this->params->get( 'show_likes' ) ) : ?>
				<div id="yendif-video-share-likes-dislikes-widget" class="mt-1">
					<?php echo YendifVideoShareHtml::LikesDislikesWidget( $this->video, $this->params ); ?>
				</div>
			<?php endif; ?>
		<?php endif; ?>	

		<?php
		// Video Description	
		if ( $this->params->get( 'show_description' ) && ! empty( $this->video->description ) ) {
			echo '<div class="yendif-video-share-description mt-3">' . $this->video->description . '</div>';
		}
		?>
	</div>		

	<?php
	// Comments
	if ( $this->hasAccess && $this->params->get( 'comments' ) != 'none' ) {
		echo $this->loadTemplate( 'comments' );
	}
	?>
	
	<?php
	// Relates Videos
	if ( $this->hasAccess && $this->params->get( 'show_related' ) ) {
		echo $this->loadTemplate( 'related' );
	}
	?>
</div>