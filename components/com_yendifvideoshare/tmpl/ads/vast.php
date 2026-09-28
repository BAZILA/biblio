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

use Joomla\CMS\Uri\Uri;

echo '<?xml version="1.0" encoding="utf-8"?>' . "\n"; 
?>
<VAST xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:noNamespaceSchemaLocation="vast.xsd" version="3.0">
	<Ad id="<?php echo (int) $item->id; ?>">
		<InLine>
			<AdSystem><?php echo $siteName; ?></AdSystem>
			<AdTitle><?php echo $item->title; ?></AdTitle>
			<Impression><![CDATA[<?php echo URI::root(); ?>index.php?option=com_yendifvideoshare&task=ad.impression&id=<?php echo (int) $item->id; ?>]]></Impression>
			<Creatives>
				<Creative>
					<?php if( ! empty( $timeFormat ) ) : ?>
					<Linear skipoffset="<?php echo $timeFormat; ?>">
					<?php else : ?>
					<Linear>
					<?php endif; ?>
						<Duration>00:00:30</Duration>
						<TrackingEvents>
							<Tracking event="start"><![CDATA[<?php echo $pixelImage; ?>]]></Tracking>
							<Tracking event="firstQuartile"><![CDATA[<?php echo $pixelImage; ?>]]></Tracking>
							<Tracking event="midpoint"><![CDATA[<?php echo $pixelImage; ?>]]></Tracking>
							<Tracking event="thirdQuartile"><![CDATA[<?php echo $pixelImage; ?>]]></Tracking>
							<Tracking event="complete"><![CDATA[<?php echo $pixelImage; ?>]]></Tracking>
							<Tracking event="pause"><![CDATA[<?php echo $pixelImage; ?>]]></Tracking>
							<Tracking event="mute"><![CDATA[<?php echo $pixelImage; ?>]]></Tracking>
							<Tracking event="fullscreen"><![CDATA[<?php echo $pixelImage; ?>]]></Tracking>
						</TrackingEvents>
						<VideoClicks>
							<ClickThrough><![CDATA[<?php echo $item->link; ?>]]></ClickThrough>
							<ClickTracking><![CDATA[<?php echo URI::root(); ?>index.php?option=com_yendifvideoshare&task=ad.click&id=<?php echo (int) $item->id; ?>]]></ClickTracking>
						</VideoClicks>
						<MediaFiles>
							<MediaFile type="video/mp4" bitrate="300" width="480" height="270">
								<![CDATA[<?php echo $item->mp4; ?>]]>
							</MediaFile>
						</MediaFiles>
					</Linear>
				</Creative>
			</Creatives>
		</InLine>
	</Ad>
</VAST>