<?php
/**
 * @package   T3 Blank
 * @copyright Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license   GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;
$uppermain_3 = $this->CountModules( 'uppermain-3' );
$uppermain_4 = $this->CountModules( 'uppermain-4' );

if ( $uppermain_3 and $uppermain_4) {
	$uppermainsize1 = 'col-xs-12 col-sm-12 col-md-8';		
	$uppermainsize2 = 'col-xs-12 col-sm-12 col-md-4';	
	} 
	
	else if ($uppermain_3) {
	$uppermainsize1 = 'col-xs-12 col-sm-12 col-md-12';	
	$uppermainsize2 = '';	
	} 
else if ($uppermain_4) {
	$uppermainsize1 = '';	
	$uppermainsize2 = 'col-xs-12 col-sm-6 col-md-12';
	} 
	 
///////////////////////////

$uppermain_6 = $this->CountModules( 'uppermain-6' );
$uppermain_7 = $this->CountModules( 'uppermain-7' );

if ( $uppermain_6 and $uppermain_7) {
	$uppermainsize6 = 'col-xs-12 col-sm-12 col-md-8';		
	$uppermainsize7 = 'col-xs-12 col-sm-12 col-md-4';	
	} 
	
	else if ($uppermain_6) {
	$uppermainsize6 = 'col-xs-12 col-sm-12 col-md-12';	
	$uppermainsize7 = '';	
	} 
else if ($uppermain_7) {
	$uppermainsize6 = '';	
	$uppermainsize7 = 'col-xs-12 col-sm-6 col-md-12';
	} 
//////////////////////////////
$uppermain_spot_1 = $this->CountModules( 'uppermain-spot-1' );
$uppermain_spot_2 = $this->CountModules( 'uppermain-spot-2' );

if ( $uppermain_spot_1 and $uppermain_spot_2) {
	$uppermainspotsize1 = 'col-xs-12 col-sm-6 col-md-6';	
	$uppermainspotsize2 = 'col-xs-12 col-sm-6 col-md-6';	
	} 
else if ($uppermain_spot_1) {
	$uppermainspotsize1 = 'col-xs-12 col-sm-12 col-md-12';	
	$uppermainspotsize2 = '';	
	} 
else if ($uppermain_spot_2) {
	$uppermainspotsize1 = '';	
	$uppermainspotsize2 = 'col-xs-12 col-sm-6 col-md-12';	
	} 
////////////////////////////////
$uppermaincol_1 = $this->CountModules( 'column-1' );
$uppermaincol_2 = $this->CountModules( 'column-2' );
$uppermaincol_3 = $this->CountModules( 'column-3' );


if ( $uppermaincol_1 and $uppermaincol_2 and $uppermaincol_3) {
	$uppermainsizecol = 'col-xs-12 col-sm-12 col-md-4';		
	} 
	else if ($uppermaincol_1 and $uppermaincol_2) {
	$uppermainsizecol = 'col-xs-12 col-sm-6 col-md-6';		
	} 
	else if ($uppermaincol_1 and $uppermaincol_3) {
	$uppermainsizecol = 'col-xs-12 col-sm-6 col-md-6';
	} 
	else if ($uppermaincol_2 and $uppermaincol_3) {
	$uppermainsizecol = 'col-xs-12 col-sm-6 col-md-6';
	}
 else if ( $uppermaincol_1 || $uppermaincol_2 || $uppermaincol_3) {
	$uppermainsizecol = 'col-xs-12 col-sm-12 col-md-12';		
	
	} 

?>
<!-- uppermain -->
	<!-- uppermain-SPOT -->
	<div style="clear:both"></div>
	<?php if ($this->countModules('uppermain-spot-1 or uppermain-spot-2')) : ?>
	<div class="uppermain-spot">
	<div class="container t3-sl">
	<div class="row">
	<?php if ($this->countModules('uppermain-spot-1')) : ?>
	<div class="uppermain-3 <?php echo $uppermainspotsize1 ?>">
		 <jdoc:include type="modules" name="<?php $this->_p('uppermain-spot-1') ?>" style="T3Xhtml" />
	</div>
	<?php endif ?>
	<?php if ($this->countModules('uppermain-spot-2')) : ?>
	<div class="uppermain-4 <?php echo $uppermainspotsize2 ?>">
		 <jdoc:include type="modules" name="<?php $this->_p('uppermain-spot-2') ?>" style="T3Xhtml" />
	</div>
	<?php endif ?>
	</div></div></div>
	<?php endif ?>
	<!-- //uppermain SPOT-->
 <?php if ($this->countModules('uppermain')) : ?>
			 <div style="clear:both"></div>
			 <div class="wrap uppermain">
			 <div class="uppermain<?php $this->_c('uppermain')?>">
			 <div class="container t3-sl">
        <jdoc:include type="modules" name="<?php $this->_p('uppermain') ?>" style="T3Xhtml" />
      </div></div></div>
	  <?php endif ?>
 <?php if ($this->countModules('uppermain-2')) : ?>
			 <div style="clear:both"></div>
			 <div class="uppermain-2<?php $this->_c('uppermain-2')?>">
			 <div class="container t3-sl">  
        <jdoc:include type="modules" name="<?php $this->_p('uppermain-2') ?>" style="T3Xhtml" />
      </div></div>
	  <?php endif ?>
	<?php if ($this->countModules('uppermain-3 or uppermain-4')) : ?>
	<!-- uppermain-3-4 -->
	<div style="clear:both"></div>
	<div class="uppermain-3-4">
	<div class="container t3-sl">
	<div class="uppermain-3 <?php echo $uppermainsize1 ?>">
		 <jdoc:include type="modules" name="<?php $this->_p('uppermain-3') ?>" style="T3Xhtml" />
	</div>
	<div class="uppermain-4 <?php echo $uppermainsize2 ?>">
		 <jdoc:include type="modules" name="<?php $this->_p('uppermain-4') ?>" style="T3Xhtml" />
	</div>
	</div></div>
	<!-- //uppermain-3-4-->
<?php endif ?>
 <?php if ($this->countModules('uppermain-5')) : ?>
			 <div style="clear:both"></div>
			 <div class="uppermain-5<?php $this->_c('uppermain-5')?>">  
			 <div class="container t3-sl">    
        <jdoc:include type="modules" name="<?php $this->_p('uppermain-5') ?>" style="T3Xhtml" />
		</div>
      </div>
	  <?php endif ?>
	<?php if ($this->countModules('uppermain-6 or uppermain-7')) : ?>
	<!-- uppermain-6-7 -->
	<div style="clear:both"></div>
	<div class="uppermain-6-7">
	<div class="uppermain-6 <?php echo $uppermainsize6 ?>">
		 <jdoc:include type="modules" name="<?php $this->_p('uppermain-6') ?>" style="T3Xhtml" />
	</div>
	<div class="uppermain-7 <?php echo $uppermainsize7 ?>">
		 <jdoc:include type="modules" name="<?php $this->_p('uppermain-7') ?>" style="T3Xhtml" />
	</div></div>
	<!-- //uppermain-6-7-->
<?php endif ?>
 <?php if ($this->countModules('column-1 or column-2 or column-3')) : ?>
	<div style="clear:both"></div>
		<div class="uppermain-8"> 
		<div class="container t3-sl">
		<?php if ($this->countModules('column-1')) : ?>
		<div class="column-1 <?php echo $uppermainsizecol ?>">
        <jdoc:include type="modules" name="<?php $this->_p('column-1') ?>" style="T3Xhtml" />
		</div>
		<?php endif ?>
		<?php if ($this->countModules('column-2')) : ?>
		<div class="column-2 <?php echo $uppermainsizecol ?>">
        <jdoc:include type="modules" name="<?php $this->_p('column-2') ?>" style="T3Xhtml" />
		</div>
		<?php endif ?>
		<?php if ($this->countModules('column-3')) : ?>
		<div class="column-3 <?php echo $uppermainsizecol ?>">
        <jdoc:include type="modules" name="<?php $this->_p('column-3') ?>" style="T3Xhtml" />
		</div>
		<?php endif ?>
		</div>
      </div>
	  <?php endif ?>
<!-- //uppermain -->