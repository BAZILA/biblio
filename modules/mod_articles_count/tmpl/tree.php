<?php // no direct access
defined('_JEXEC') or die('Restricted access'); ?>
<div class="articles_count<?php echo $params->get('moduleclass_sfx'); ?>" id="<?php echo $lists->id; ?>_tree">
<ul id="<?php echo $lists->id; ?>">
<?php foreach ($lists->items as $item): ?> 
<?php echo $item->startTag; ?><?php echo $item->startLink; ?><?php echo $item->name; ?><?php echo $item->artcountText; ?><?php echo $item->endLink; ?>
<?php endforeach; ?>
<?php echo $lists->closeTag; ?>
</ul>
</div>