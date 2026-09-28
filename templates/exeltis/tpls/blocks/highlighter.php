<?php
/**
 * @package   T3 Blank
 * @copyright Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license   GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

$date_format    = $this->params->get('date_format', '');
?>
		<!-- highlighter-->
		<?php if ($this->countModules('highlighter')) : ?>
		<div class="wrap highlighter <?php $this->_c('highlighter') ?>">
				<div class="container">
			<div class="highlighter-inner">
			<div class="highlighter-left-inner">
<jdoc:include type="modules" name="<?php $this->_p('highlighter') ?>" style="raw" />
			</div>
			</div>
		<div></div><div style="clear:both"></div></div></div>
		<?php endif ?>
		<!-- //highlighter -->