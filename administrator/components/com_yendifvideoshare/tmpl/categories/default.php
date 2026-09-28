<?php
/**
 * @version    2.1.1
 * @package    Com_YendifVideoShare
 * @author     PluginsWare Interactive Pvt. Ltd <admin@yendifplayer.com>
 * @copyright  Copyright (c) 2012 - 2025 PluginsWare Interactive Pvt. Ltd. All Rights Reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access
defined( '_JEXEC' ) or die;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Session\Session;
use Joomla\CMS\Uri\Uri;

HTMLHelper::addIncludePath( JPATH_COMPONENT . '/src/Helper/' );
HTMLHelper::_( 'bootstrap.tooltip' );

// Import CSS
$wa = Factory::getApplication()->getDocument()->getWebAssetManager();
$wa->useStyle( 'com_yendifvideoshare.admin' )
    ->useScript( 'com_yendifvideoshare.admin' );

$user      = Factory::getUser();
$userId    = $user->get( 'id' );
$listOrder = $this->state->get( 'list.ordering' );
$listDirn  = $this->state->get( 'list.direction' );
$canOrder  = $user->authorise( 'core.edit.state', 'com_yendifvideoshare' );
$saveOrder = $listOrder == 'a.ordering';

if ( $saveOrder ) {
	$saveOrderingUrl = 'index.php?option=com_yendifvideoshare&task=categories.saveOrderAjax&tmpl=component&' . Session::getFormToken() . '=1';
	HTMLHelper::_( 'draggablelist.draggable' );
}
?>

<form action="<?php echo Route::_( 'index.php?option=com_yendifvideoshare&view=categories' ); ?>" method="post" name="adminForm" id="adminForm">
	<div class="row">
		<div class="col-md-12">
			<div id="j-main-container" class="j-main-container">
				<?php echo LayoutHelper::render( 'joomla.searchtools.default', array( 'view' => $this ) ); ?>
				<div class="clearfix"></div>

				<?php if ( empty( $this->items ) ) : ?>
					<div class="alert alert-warning">
						<?php echo Text::_( 'JGLOBAL_NO_MATCHING_RESULTS' ); ?>
					</div>
				<?php else : ?>
					<table class="table table-striped" id="categoryList">
						<thead>
							<tr>
								<th width="1%">
									<input type="checkbox" name="checkall-toggle" value="" title="<?php echo Text::_( 'JGLOBAL_CHECK_ALL' ); ?>" onclick="Joomla.checkAll( this )" />
								</th>
								<th width="1%" class="nowrap text-center d-none d-md-table-cell">
									<?php echo HTMLHelper::_( 'searchtools.sort', '', 'a.ordering', $listDirn, $listOrder, null, 'asc', 'JGRID_HEADING_ORDERING', 'icon-menu-2' ); ?>
								</th>
								<th width="5%" class="nowrap text-center">
									<?php echo HTMLHelper::_( 'searchtools.sort', 'JSTATUS', 'a.state', $listDirn, $listOrder ); ?>
								</th>
								<th>
									<?php echo HTMLHelper::_( 'searchtools.sort', 'JGLOBAL_TITLE', 'a.title', $listDirn, $listOrder ); ?>
								</th>								
								<th class="d-none d-md-table-cell">
									<?php echo HTMLHelper::_( 'searchtools.sort', 'JGRID_HEADING_ACCESS', 'a.access', $listDirn, $listOrder ); ?>
								</th>								
								<th class="text-center d-none d-md-table-cell">
									<?php echo HTMLHelper::_( 'searchtools.sort', 'JGLOBAL_FIELD_ID_LABEL', 'a.id', $listDirn, $listOrder ); ?>
								</th>
							</tr>
						</thead>
						<tbody <?php if ( $saveOrder ) : ?> class="js-draggable" data-url="<?php echo $saveOrderingUrl; ?>" data-direction="<?php echo strtolower( $listDirn ); ?>" data-nested="true" <?php endif; ?>>
							<?php foreach ( $this->items as $i => $item ) :
								$ordering   = ( $listOrder == 'a.ordering' );
								$canCreate  = $user->authorise( 'core.create', 'com_yendifvideoshare' );
								$canEdit    = $user->authorise( 'core.edit', 'com_yendifvideoshare' );
								$canCheckin = $user->authorise( 'core.manage', 'com_yendifvideoshare' );
								$canChange  = $user->authorise( 'core.edit.state', 'com_yendifvideoshare' );
								?>
								<tr class="row<?php echo $i % 2; ?>" data-draggable-group="<?php echo (int) $item->parent; ?>" data-item-id="<?php echo (int) $item->id; ?>" data-parents="<?php echo implode( ' ', $item->parents ); ?>">
									<td>
										<?php echo HTMLHelper::_( 'grid.id', $i, $item->id ); ?>
									</td>
									<td class="order nowrap text-center d-none d-md-table-cell">
										<?php
										$iconClass = '';
										if ( ! $canChange )	{
											$iconClass = ' inactive';
										} elseif ( ! $saveOrder ) {
											$iconClass = ' inactive" title="' . Text::_( 'JORDERINGDISABLED' );
										}
										?>
										<span class="sortable-handler<?php echo $iconClass; ?>">
											<span class="icon-ellipsis-v" aria-hidden="true"></span>
										</span>
										<?php if ( $canChange && $saveOrder ) : ?>
											<input type="text" name="order[]" size="5" value="<?php echo $item->ordering; ?>" class="width-20 text-area-order hidden">
										<?php endif; ?>
									</td>
									<td class="nowrap text-center">
										<?php echo HTMLHelper::_( 'jgrid.published', $item->state, $i, 'categories.', $canChange, 'cb' ); ?>
									</td>
									<td>
										<?php if ( isset( $item->checked_out ) && $item->checked_out && ( $canEdit || $canChange ) ) : ?>
											<?php echo HTMLHelper::_( 'jgrid.checkedout', $i, $item->uEditor, $item->checked_out_time, 'categories.', $canCheckin ); ?>
										<?php endif; ?>

										<?php echo $item->spcr; ?>

										<?php if ( $canEdit ) : ?>
											<a href="<?php echo Route::_( 'index.php?option=com_yendifvideoshare&task=category.edit&id=' . (int) $item->id ); ?>">
												<?php echo $this->escape( $item->title ); ?>
											</a>
										<?php else : ?>
											<?php echo $this->escape( $item->title ); ?>
										<?php endif; ?>
									</td>									
									<td class="small d-none d-md-table-cell">
										<?php echo $item->access; ?>
									</td>									
									<td class="text-center d-none d-md-table-cell">
										<?php echo (int) $item->id; ?>
									</td>
								</tr>
							<?php endforeach; ?>
						</tbody>
					</table>

					<?php echo $this->pagination->getListFooter(); ?>
				<?php endif; ?>

				<input type="hidden" name="task" value="" />
				<input type="hidden" name="boxchecked" value="0" />
				<input type="hidden" name="list[fullorder]" value="<?php echo $listOrder; ?> <?php echo $listDirn; ?>" />
				<?php echo HTMLHelper::_( 'form.token' ); ?>
			</div>
		</div>
	</div>
</form>