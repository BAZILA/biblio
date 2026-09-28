<?php
/**
 * @package   T3 Blank
 * @copyright Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license   GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;
?>
	<?php if ($this->countModules('middlespot')) : ?>
		<div style="clear:both"></div>
		<!-- HOME SL 1 -->
		<div class="wrap middlespot <?php $this->_c('middlespot') ?>">
				<jdoc:include type="modules" name="<?php $this->_p('middlespot') ?>" style="T3Xhtml" />
		</div>
	<div style="clear:both"></div>
		<!-- //HOME SL 1 -->
	<?php endif ?>