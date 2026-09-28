<?php
/**
 * @package   T3 Blank
 * @copyright Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license   GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;
?>
	<?php if ($this->countModules('bottomspot-2')) : ?>
		<div style=" clear:both"></div>
		<div class="wrap bottomspot-2 <?php $this->_c('bottomspot-2') ?>"><div class="container t3-sl">
			<div class="bottomspot-2-module">
				<jdoc:include type="modules" name="<?php $this->_p('bottomspot-2') ?>" style="T3Xhtml" />
				</div>
		</div></div>
	<?php endif ?>