<?php
/**
 * @package   T3 Blank
 * @copyright Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license   GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;
?>
<?php if ($this->countModules('bottomspot-3')) : ?>
	<div style=" clear:both"></div>
	<div class="wrap bottomspot-3 <?php $this->_c('bottomspot-3') ?>">
		<div class="container t3-sl">
			<jdoc:include type="modules" name="<?php $this->_p('bottomspot-3') ?>" />
		</div>
	</div>
<?php endif ?>
