<?php
defined('_JEXEC') || die('Restricted Access');
?>
<div class="cell">
	<acym-media :value="<?php echo acym_escape($vModel); ?>" :text="imageText" v-on:change="<?php echo acym_escape($vModel); ?> = $event">
</div>
