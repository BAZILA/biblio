<?php // no direct access
defined('_JEXEC') or die('Restricted access'); ?>
<div class="exeltis articles_count<?php echo $params->get('moduleclass_sfx'); ?>" id="<?php echo $lists->id; ?>_tree">
<ul id="<?php echo $lists->id; ?>">
<?php foreach ($lists->items as $item): ?> 
<li><i class="fa fa-folder-open-o" aria-hidden="true"></i>
<?php echo $item->startLink; ?><?php echo $item->name; ?><span class="artcount"><?php echo $item->artcountText; ?></span><?php echo $item->endLink; ?></li>
<?php endforeach; ?>
</ul>
</div>