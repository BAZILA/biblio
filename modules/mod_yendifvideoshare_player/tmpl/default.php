<?php
/**
 * @version     2.1.1
 * @package     Com_YendifVideoShare
 * @subpackage  Mod_YendifVideoShare_Player
 * @author      PluginsWare Interactive Pvt. Ltd <admin@yendifplayer.com>
 * @copyright   Copyright (c) 2012 - 2025 PluginsWare Interactive Pvt. Ltd. All Rights Reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined( '_JEXEC' ) or die; 

use Joomla\CMS\Factory;
use PluginsWare\Component\YendifVideoShare\Site\Helper\YendifVideoSharePlayer;
use PluginsWare\Module\YendifVideoSharePlayer\Site\Helper\YendifVideoSharePlayerHelper;

$item = YendifVideoSharePlayerHelper::getItem( $params );

if ( empty( $item ) ) {
	return;
}
?>

<div class="yendif-video-share mod-yendifvideoshare-player">
	<?php if ( $params->get( 'show_title' ) ) : ?>
		<h3 class="yendif-video-share-title mt-0 mb-4"><?php echo $item->title; ?></h3>
	<?php endif; ?>

	<div class="yendif-video-share-player">
		<?php
		$args = array( 
			'videoid' => $item->id
		);

		$options = array(
			'width',
			'ratio',
			'volume',
			'autoplay',
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

		foreach ( $options as $option ) {
			$value = $params->get( $option, 'global' );
			if ( $value != 'global' ) {
				$args[ $option ] = $value;
			}
		}

		echo YendifVideoSharePlayer::load( $args, $params ); 
		?>
	</div>

	<?php if ( $params->get( 'show_description' ) ) : ?>
		<div class="yendif-video-share-description mt-2"><?php echo $item->description; ?></div>
	<?php endif; ?>
</div>
