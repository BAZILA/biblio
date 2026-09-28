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
<vmap:VMAP xmlns:vmap="http://www.iab.net/videosuite/vmap" version="1.0">
	<?php if ( ! empty( $preroll_id ) ) : ?>
		<vmap:AdBreak timeOffset="start" breakType="linear" breakId="preroll">
			<vmap:AdSource id="preroll-ad" allowMultipleAds="false" followRedirects="true">
				<vmap:AdTagURI templateType="vast3">
					<![CDATA[<?php echo URI::root(); ?>index.php?option=com_yendifvideoshare&view=ads&type=vast&id=<?php echo (int) $preroll_id; ?>&format=xml&lang=<?php echo $locales[4]; ?>]]>
				</vmap:AdTagURI>
			</vmap:AdSource>
		</vmap:AdBreak>
	<?php endif; ?>

	<?php if ( ! empty( $postroll_id ) ) : ?>
		<vmap:AdBreak timeOffset="end" breakType="linear" breakId="postroll">
			<vmap:AdSource id="postroll-ad" allowMultipleAds="false" followRedirects="true">
				<vmap:AdTagURI templateType="vast3">
					<![CDATA[<?php echo URI::root(); ?>index.php?option=com_yendifvideoshare&view=ads&type=vast&id=<?php echo (int) $postroll_id; ?>&format=xml&lang=<?php echo $locales[4]; ?>]]>
				</vmap:AdTagURI>
			</vmap:AdSource>
		</vmap:AdBreak>
	<?php endif; ?>
</vmap:VMAP>