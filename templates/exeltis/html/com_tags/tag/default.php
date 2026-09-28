<?php
/**
 * Page d'un tag (un auteur ou un type de document) : titre, description et ouvrages en cartes.
 */

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

$tag   = count($this->item) === 1 ? $this->item[0] : null;
$items = $this->items ?: [];
$n     = count($items);
?>
<div class="com-tags-tag tag-category biblio-tag<?php echo $this->pageclass_sfx; ?>">
	<h1><?php echo $this->escape($tag ? $tag->title : $this->params->get('page_heading')); ?></h1>

	<?php // Pour un auteur, la description liste ses ouvrages : inutile ici puisqu'ils sont affichés en cartes ?>
	<?php if ($tag && $tag->description && strpos($tag->path, 'auteurs/') !== 0) : ?>
		<div class="category-desc"><?php echo HTMLHelper::_('content.prepare', $tag->description, '', 'com_tags.tag'); ?></div>
	<?php endif; ?>

	<?php if ($n === 0) : ?>
		<p><?php echo Text::_('COM_TAGS_NO_ITEMS'); ?></p>
	<?php else : ?>
		<p class="biblio-tag-count"><?php echo $n . ' ouvrage' . ($n > 1 ? 's' : ''); ?></p>
		<div class="biblio-cards">
			<?php foreach ($items as $item) : ?>
				<?php $images = json_decode($item->core_images ?? ''); ?>
				<?php $link   = Route::_($item->link); ?>
				<div class="biblio-card">
					<?php if (!empty($images->image_intro)) : ?>
						<a href="<?php echo $link; ?>" class="biblio-card-cover">
							<img src="<?php echo htmlspecialchars($images->image_intro); ?>" alt="<?php echo htmlspecialchars($images->image_intro_alt ?? $item->core_title); ?>" loading="lazy">
						</a>
					<?php endif; ?>
					<div class="biblio-card-body">
						<?php if (!empty($item->core_category_title)) : ?>
							<span class="biblio-card-domain"><i class="far fa-folder-open"></i> <?php echo $this->escape($item->core_category_title); ?></span>
						<?php endif; ?>
						<h3><a href="<?php echo $link; ?>"><?php echo $this->escape($item->core_title); ?></a></h3>
						<?php if (!empty($item->core_created_by_alias)) : ?>
							<p class="biblio-card-authors"><i class="fas fa-user"></i> <?php echo $this->escape($item->core_created_by_alias); ?></p>
						<?php endif; ?>
						<div class="biblio-card-text"><?php echo HTMLHelper::_('string.truncate', strip_tags($item->core_body), 160); ?></div>
						<a href="<?php echo $link; ?>" class="btn btn-primary">Voir la fiche</a>
					</div>
				</div>
			<?php endforeach; ?>
		</div>
	<?php endif; ?>

	<?php $pagesTotal = $this->pagination->pagesTotal ?? 0; ?>
	<?php if ($this->params->def('show_pagination', 2) && $pagesTotal > 1) : ?>
		<div class="pagination-wrap"><?php echo $this->pagination->getPagesLinks(); ?></div>
	<?php endif; ?>
</div>
