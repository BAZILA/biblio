<?php
/**
 * @package   T3 Blank
 * @copyright Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license   GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;
?>

<?php if ($this->countModules('breadcrumbs')) : ?>
	<!-- NAV HELPER -->
	<nav class="wrap t3-breadcrumbs <?php $this->_c('breadcrumbs') ?>">
	<div class="t3-breadcrumbs-inner">
		<div class="container">
		<div class="t3-breadcrumbs-module">
			<jdoc:include type="modules" name="<?php $this->_p('breadcrumbs') ?>" />
		</div>
		<div></div><div style="clear:both"></div></div></div>
	</nav>
	<!-- //NAV HELPER -->
<?php endif ?>
