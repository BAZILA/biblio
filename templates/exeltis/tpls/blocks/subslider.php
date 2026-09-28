<?php
/**
 * @package   T3 Blank
 * @copyright Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license   GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;
$subslider_1 = $this->CountModules( 'subslider-1' );
$subslider_2 = $this->CountModules( 'subslider-2' );



if ( $subslider_1 and $subslider_2) {
	$subslidersize1 = 'col-xs-12 col-sm-12 col-md-4';		
	$subslidersize2 = 'col-xs-12 col-sm-12 col-md-8';	
	
	} 
	
	else if ($subslider_1) {
	$subslidersize1 = 'col-xs-12 col-sm-12 col-md-12';	
	$subslidersize2 = '';	
	} 
else if ($subslider_2) {
	$subslidersize1 = '';	
	$subslidersize2 = 'col-xs-12 col-sm-12 col-md-12';
	} 
?>
<!-- SUBSLIDER -->
<?php if ($this->countModules('subslider-1 or subslider-2')) : ?>
<div class="wrap subslider_bg"><div class="subslider_inner container t3-sl">
	<!-- subslider-1-2 -->
	<div class="subslider-1-2">
	<?php if ($this->countModules('subslider-1')) : ?>
	<div class="subslider-1 <?php echo $subslidersize1 ?> <?php $this->_c('subslider-1') ?>">
		 <jdoc:include type="modules" name="<?php $this->_p('subslider-1') ?>" style="T3Xhtml" />
	</div>
	<?php endif ?>
	<?php if ($this->countModules('subslider-2')) : ?>
	<div class="subslider-2 <?php echo $subslidersize2 ?> <?php $this->_c('subslider-2') ?>">
		 <jdoc:include type="modules" name="<?php $this->_p('subslider-2') ?>" style="T3Xhtml" />
	</div>
	<?php endif ?>
	<div></div><div style="clear:both"></div></div>
	<!-- //subslider-1-2-->
</div><div></div><div style="clear:both"></div></div>
<!-- //SUBSLIDER -->
<?php endif ?>
<!-- TOPSL -->
<?php if ($this->countModules('topsl')) : ?>
<div class="wrap topsl_bg <?php $this->_c('topsl') ?>">
<div class="topsl_inner container t3-sl">
	 <jdoc:include type="modules" name="<?php $this->_p('topsl') ?>" style="T3Xhtml" />
	</div></div>
		<div style="clear:both"></div>
		<?php endif ?>
		<!-- //TOPSL -->
<!-- TOPSL-2 -->
<?php if ($this->countModules('topsl-2')) : ?>
<div class="wrap topsl-2_bg <?php $this->_c('topsl-2') ?>"><div class="topsl-2_bg-overlay"></div>
<div class="topsl-2_inner container t3-sl">
	 <jdoc:include type="modules" name="<?php $this->_p('topsl-2') ?>" style="T3Xhtml" />
	</div></div>
		<div style="clear:both"></div>
		<?php endif ?>
		<!-- //TOPSL-2 -->
<!-- TOPSL-3 -->
<?php if ($this->countModules('topsl-3')) : ?>
<div class="wrap topsl-3_bg <?php $this->_c('topsl-3') ?>"><div class="topsl-3_bg-overlay"></div>
<div class="topsl-3_inner container t3-sl">
	 <jdoc:include type="modules" name="<?php $this->_p('topsl-3') ?>" style="T3Xhtml" />
	<div></div><div style="clear:both"></div></div></div>
		<div style="clear:both"></div>
		<?php endif ?>
		<!-- //TOPSL-3 -->

