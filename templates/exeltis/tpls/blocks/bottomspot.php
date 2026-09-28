<?php
/**
 * @package   T3 Blank
 * @copyright Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license   GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;
?>
	<?php if ($this->countModules('bottomspot')) : ?>
		<!-- HOME SL 1 -->
		<div style="clear:both"></div>
		<div class="wrap bottomspot <?php $this->_c('bottomspot') ?>">
		<div class="bottomspot-inner">
				<jdoc:include type="modules" name="<?php $this->_p('bottomspot') ?>" style="T3Xhtml" />
		</div></div>
		<!-- //HOME SL 1 -->
	<?php endif ?>