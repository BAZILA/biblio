<?php
defined('_JEXEC') || die('Restricted Access');
?>
<div class="cell">
	<spectrum :name="'<?php echo acym_escape($name); ?>'" v-model="<?php echo acym_escape($vModel); ?>" :value="'<?php echo acym_escape($value); ?>'">
</div>
