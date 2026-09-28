<?php
/**
 * @package   T3 Blank
 * @copyright Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license   GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;
?>
	<?php if ($this->countModules('coloredmodule-1')) : ?>
		<!-- COLOREDMODULE 1 -->
		<div class="wrap coloredmodule-1  <?php $this->_c('coloredmodule-1') ?>"><div class="coloredmodule-1-overlay"></div>
		<div class="coloredmodule-1-inner">
		
		<div class="container">
				<jdoc:include type="modules" name="<?php $this->_p('coloredmodule-1') ?>" style="T3Xhtml" /></div>
		<div></div><div style="clear:both"></div></div></div>
		<!-- //COLOREDMODULE 1 -->
	<?php endif ?>