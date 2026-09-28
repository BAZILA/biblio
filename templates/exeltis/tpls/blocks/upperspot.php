<?php
/**
 * @package   T3 Blank
 * @copyright Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license   GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;
?>
	<?php if ($this->countModules('upperspot')) : ?>
		<!-- HOME SL 1 -->
		<div class="wrap upperspot <?php $this->_c('upperspot') ?>">
		<div class="container upperspot-inner">
				<jdoc:include type="modules" name="<?php $this->_p('upperspot') ?>" style="raw" />
				</div>
		</div>
		<!-- //HOME SL 1 -->
	<?php endif ?>