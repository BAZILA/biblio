<?php
/**
 * @package   T3 Blank
 * @copyright Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license   GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;
?>


<!-- SLIDER -->
  <?php if ($this->countModules('slider')) : ?><div style="clear:both"></div>
<div class="slider_bg">
  <div id="slidermodul"><div id="slider-module-wrapper"><div class="slider<?php $this->_c('slider')?>">     
        <jdoc:include type="modules" name="<?php $this->_p('slider') ?>" style="raw" />
      </div></div></div>
</div>
<div style="clear:both"></div>
<!-- //SLIDER -->
<?php endif ?>