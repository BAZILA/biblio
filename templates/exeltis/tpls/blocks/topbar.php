<?php
/**
 * @package   T3 Blank
 * @copyright Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license   GNU General Public License version 2 or later; see LICENSE.txt
 **/

defined('_JEXEC') or die;
$topbarmiddlesize = 'col-xs-12 col-sm-6 col-md-6 col-lg-6 topbar-middle';
if ($this->countModules('topbar-right')) {
	$topbarmiddlesize = 'col-xs-12 col-sm-5 col-md-5 col-lg-5 topbar-middle';
	}
	
?>
		<!-- topbar-->
		<?php if ($this->countModules('topbar-left or topbar-middle or topbar-right')) : ?>
		<div class="wrap topbar">
			<?php if ($this->getParam('addon_offcanvas_enable')) : ?>
				<?php $this->loadBlock ('off-canvas') ?>
			<?php endif ?>
		<div class="container">
		<div class="row">			
				<?php if ($this->countModules('topbar-left')) : ?>
			<div class="col-xs-12 col-sm-6 col-md-6 col-lg-6 topbar-left">
			<div class="topbar-left-inner <?php $this->_c('topbar-left') ?>">
	<jdoc:include type="modules" name="<?php $this->_p('topbar-left') ?>" style="raw" />
			</div>
			</div><?php endif ?>
						<?php if ($this->countModules('topbar-middle')) : ?>
			<div class="<?php echo $topbarmiddlesize ;?> <?php $this->_c('topbar-middle') ?>">
					<div class="topbar-middle-inner <?php $this->_c('topbar-middle') ?>">
						<jdoc:include type="modules" name="<?php $this->_p('topbar-middle') ?>" style="raw" />
					</div>
			</div>
			<?php endif ?>
			
			<?php if ($this->countModules('topbar-right')) : ?>
			<div class="col-xs-12 col-sm-1 col-md-1 col-lg-1 topbar-right <?php $this->_c('topbar-right') ?>">
					<div class="topbar-right-inner <?php $this->_c('topbar-right') ?>">
						<jdoc:include type="modules" name="<?php $this->_p('topbar-right') ?>" style="raw" />
					</div>
			</div>
			<?php endif ?>
		<div></div><div style="clear:both"></div></div></div></div><?php endif ?>
		<!-- //topbar -->