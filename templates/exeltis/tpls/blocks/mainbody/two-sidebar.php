<?php
/**
 * @package   T3 Blank
 * @copyright Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license   GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Mainbody 3 columns, content in center: sidebar1 - content - sidebar2
 */
?>
<div id="t3-mainbody" class="container t3-mainbody">
	<div class="row">

		<!-- MAIN CONTENT -->
		<div id="t3-content" class="t3-content col-xs-12 col-md-6  col-md-push-3">
  <?php $this->loadBlock('middlespot') ?>
						<?php if ($this->countModules('main-top')) : ?>
					<!-- MAIN TOP -->
					<div class="main-top <?php $this->_c('main-top') ?>">
						<jdoc:include type="modules" name="<?php $this->_p('main-top') ?>" style="T3Xhtml" />
					<div></div><div style="clear:both"></div></div>
					<!-- //MAIN TOP  -->
				<?php endif ?>
			<jdoc:include type="component" />
			<?php $this->loadBlock('submain') ?>
		</div>
		<!-- //MAIN CONTENT -->

		<!-- SIDEBAR 1 -->
		<div class="t3-sidebar t3-sidebar-1 t3-sidebar-left col-xs-6  col-md-3  col-md-pull-6 <?php $this->_c($vars['sidebar1']) ?>">
			<jdoc:include type="modules" name="<?php $this->_p($vars['sidebar1']) ?>" style="T3Xhtml" />
		</div>
		<!-- //SIDEBAR 1 -->
	
		<!-- SIDEBAR 2 -->
		<div class="t3-sidebar t3-sidebar-2 t3-sidebar-right col-xs-6  col-md-3 <?php $this->_c($vars['sidebar2']) ?>">
			<jdoc:include type="modules" name="<?php $this->_p($vars['sidebar2']) ?>" style="T3Xhtml" />
		</div>
		<!-- //SIDEBAR 2 -->
	
	</div>
</div> 