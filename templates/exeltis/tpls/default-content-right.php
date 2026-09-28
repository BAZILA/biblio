<?php
/** 
 *------------------------------------------------------------------------------
 * @package       T3 Framework for Joomla!
 *------------------------------------------------------------------------------
 * @copyright     Copyright (C) 2004-2013 JoomlArt.com. All Rights Reserved.
 * @license       GNU General Public License version 2 or later; see LICENSE.txt
 * @authors       JoomlArt, JoomlaBamboo, (contribute to this project at github 
 *                & Google group to become co-author)
 * @Google group: https://groups.google.com/forum/#!forum/t3fw
 * @Link:         http://t3-framework.org 
 *------------------------------------------------------------------------------
 */


defined('_JEXEC') or die;
?>

<!DOCTYPE html>
<html lang="<?php echo $this->language; ?>" dir="<?php echo $this->direction; ?>" class='<jdoc:include type="pageclass" />'>

<head>
	<jdoc:include type="head" />
	<?php $this->loadBlock('head') ?>
</head>

<body class="fullwidth">

<div class="t3-wrapper"> <!-- Need this wrapper for off-canvas menu. Remove if you don't use of-canvas -->

  <?php // bandeau supérieur retiré ?>
  
  <?php $this->loadBlock('header') ?>
  
  <?php $this->loadBlock('highlighter') ?>
    
  <?php $this->loadBlock('slider') ?>
  
  <?php $this->loadBlock('breadcrumbs') ?>
  
  <?php $this->loadBlock('subslider') ?>

  <?php $this->loadBlock('uppermain') ?>

  <?php $this->loadBlock('upperspot') ?>
  
  <?php $this->loadBlock('spotlight-1') ?>
  
  <?php $this->loadBlock('coloredmodule-1') ?>

  <?php $this->loadBlock('mainbody-content-right') ?>

  <?php $this->loadBlock('bottomline') ?>
  
  <?php $this->loadBlock('spotlight-2') ?>

  <?php $this->loadBlock('bottomspot') ?>

  <?php $this->loadBlock('bottomspot-2') ?>

  <?php $this->loadBlock('bottomspot-3') ?>

  <?php $this->loadBlock('navhelper') ?>

  <?php $this->loadBlock('footer') ?>
</div>

</body>

</html>