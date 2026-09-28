<?php
/**
 * @package   T3 Blank
 * @copyright Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license   GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Mainbody 1 columns, content only
 */
?>

<div id="t3-mainbody" class="container t3-mainbody">
	<div class="row">

		<!-- MAIN CONTENT -->
		<div id="t3-content" class="t3-content col-xs-12">
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

	</div>
</div> 