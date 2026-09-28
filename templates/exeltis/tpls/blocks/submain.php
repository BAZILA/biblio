<?php
/**
 * @package   T3 Blank
 * @copyright Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license   GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;
$submain_3 = $this->CountModules( 'submain-3' );
$submain_4 = $this->CountModules( 'submain-4' );

if ( $submain_3 and $submain_4) {
	$submainsize1 = 'col-xs-12 col-sm-12 col-md-8';		
	$submainsize2 = 'col-xs-12 col-sm-12 col-md-4';	
	} 
	
	else if ($submain_3) {
	$submainsize1 = 'col-xs-12 col-sm-12 col-md-12';	
	$submainsize2 = '';	
	} 
else if ($submain_4) {
	$submainsize1 = '';	
	$submainsize2 = 'col-xs-12 col-sm-6 col-md-12';
	} 
	 
///////////////////////////

$submain_6 = $this->CountModules( 'submain-6' );
$submain_7 = $this->CountModules( 'submain-7' );

if ( $submain_6 and $submain_7) {
	$submainsize6 = 'col-xs-12 col-sm-12 col-md-8';		
	$submainsize7 = 'col-xs-12 col-sm-12 col-md-4';	
	} 
	
	else if ($submain_6) {
	$submainsize6 = 'col-xs-12 col-sm-12 col-md-12';	
	$submainsize7 = '';	
	} 
else if ($submain_7) {
	$submainsize6 = '';	
	$submainsize7 = 'col-xs-12 col-sm-6 col-md-12';
	} 
//////////////////////////////
$submain_spot_1 = $this->CountModules( 'submain-spot-1' );
$submain_spot_2 = $this->CountModules( 'submain-spot-2' );

if ( $submain_spot_1 and $submain_spot_2) {
	$submainspotsize1 = 'col-xs-12 col-sm-6 col-md-6';	
	$submainspotsize2 = 'col-xs-12 col-sm-6 col-md-6';	
	} 
else if ($submain_spot_1) {
	$submainspotsize1 = 'col-xs-12 col-sm-12 col-md-12';	
	$submainspotsize2 = '';	
	} 
else if ($submain_spot_2) {
	$submainspotsize1 = '';	
	$submainspotsize2 = 'col-xs-12 col-sm-6 col-md-12';	
	} 
////////////////////////////////
$submaincol_1 = $this->CountModules( 'subcolumn-1' );
$submaincol_2 = $this->CountModules( 'subcolumn-2' );
$submaincol_3 = $this->CountModules( 'subcolumn-3' );


if ( $submaincol_1 and $submaincol_2 and $submaincol_3) {
	$submainsizecol = 'col-xs-12 col-sm-12 col-md-4';		
	} 
	else if ($submaincol_1 and $submaincol_2) {
	$submainsizecol = 'col-xs-12 col-sm-6 col-md-6';		
	} 
	else if ($submaincol_1 and $submaincol_3) {
	$submainsizecol = 'col-xs-12 col-sm-6 col-md-6';
	} 
	else if ($submaincol_2 and $submaincol_3) {
	$submainsizecol = 'col-xs-12 col-sm-6 col-md-6';
	}
 else if ( $submaincol_1 || $submaincol_2 || $submaincol_3) {
	$submainsizecol = 'col-xs-12 col-sm-12 col-md-12';		
	
	} 

?>
<!-- submain -->
	<!-- SUBMAIN-SPOT -->
	<div style="clear:both"></div>
	<?php if ($this->countModules('submain-spot-1 or submain-spot-2')) : ?>
	<div class="submain-spot t3-sl">
	<div class="row">
	<?php if ($this->countModules('submain-spot-1')) : ?>
	<div class="submain-3 <?php echo $submainspotsize1 ?>">
		 <jdoc:include type="modules" name="<?php $this->_p('submain-spot-1') ?>" style="T3Xhtml" />
	</div>
	<?php endif ?>
	<?php if ($this->countModules('submain-spot-2')) : ?>
	<div class="submain-4 <?php echo $submainspotsize2 ?>">
		 <jdoc:include type="modules" name="<?php $this->_p('submain-spot-2') ?>" style="T3Xhtml" />
	</div>
	<?php endif ?>
	</div></div>
	<?php endif ?>
	<!-- //SUBMAIN SPOT-->
 <?php if ($this->countModules('submain')) : ?>
			 <div style="clear:both"></div>
			 <div class="colormodule t3-sl submain<?php $this->_c('submain')?>">
        <jdoc:include type="modules" name="<?php $this->_p('submain') ?>" style="T3Xhtml" />
      </div>
	  <?php endif ?>
 <?php if ($this->countModules('submain-2')) : ?>
			 <div style="clear:both"></div>
			 <div class="t3-sl submain-2<?php $this->_c('submain-2')?>">     
        <jdoc:include type="modules" name="<?php $this->_p('submain-2') ?>" style="T3Xhtml" />
      </div>
	  <?php endif ?>
	<?php if ($this->countModules('submain-3 or submain-4')) : ?>
	<!-- SUBMAIN-3-4 -->
	<div style="clear:both"></div>
	<div class="submain-3-4 t3-sl">
	<div class="row">
	<div class="submain-3 <?php echo $submainsize1 ?>">
		 <jdoc:include type="modules" name="<?php $this->_p('submain-3') ?>" style="T3Xhtml" />
	</div>
	<div class="submain-4 <?php echo $submainsize2 ?>">
		 <jdoc:include type="modules" name="<?php $this->_p('submain-4') ?>" style="T3Xhtml" />
	</div>
	</div></div>
	<!-- //SUBMAIN-3-4-->
<?php endif ?>
 <?php if ($this->countModules('submain-5')) : ?>
			 <div style="clear:both"></div>
			 <div class="t3-sl submain-5<?php $this->_c('submain-5')?>">     
        <jdoc:include type="modules" name="<?php $this->_p('submain-5') ?>" style="T3Xhtml" />
      </div>
	  <?php endif ?>
	<?php if ($this->countModules('submain-6 or submain-7')) : ?>
	<!-- SUBMAIN-6-7 -->
	<div style="clear:both"></div>
	<div class="submain-6-7 t3-sl">
	<div class="row">
	<div class="submain-6 <?php echo $submainsize6 ?>">
		 <jdoc:include type="modules" name="<?php $this->_p('submain-6') ?>" style="T3Xhtml" />
	</div>
	<div class="submain-7 <?php echo $submainsize7 ?>">
		 <jdoc:include type="modules" name="<?php $this->_p('submain-7') ?>" style="T3Xhtml" />
	</div>
	</div></div>
	<!-- //SUBMAIN-6-7-->
<?php endif ?>
 <?php if ($this->countModules('subcolumn-1 or subcolumn-2 or subcolumn-3')) : ?>
	<div style="clear:both"></div>
		<div class="submain-8 t3-sl"> 
		<?php if ($this->countModules('subcolumn-1')) : ?>
		<div class="column-1 <?php echo $submainsizecol ?>">
        <jdoc:include type="modules" name="<?php $this->_p('subcolumn-1') ?>" style="T3Xhtml" />
		</div>
		<?php endif ?>
		<?php if ($this->countModules('subcolumn-2')) : ?>
		<div class="column-2 <?php echo $submainsizecol ?>">
        <jdoc:include type="modules" name="<?php $this->_p('subcolumn-2') ?>" style="T3Xhtml" />
		</div>
		<?php endif ?>
		<?php if ($this->countModules('subcolumn-3')) : ?>
		<div class="column-3 <?php echo $submainsizecol ?>">
        <jdoc:include type="modules" name="<?php $this->_p('subcolumn-3') ?>" style="T3Xhtml" />
		</div>
		<?php endif ?>
      </div>
	  <?php endif ?>
<!-- //submain -->