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
use \PluginsWare\Component\YendifVideoShare\Site\Helper\YendifVideoShareHelper;

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
?>

<form action="<?php echo Route::_( 'index.php?option=com_yendifvideoshare&view=users' ); ?>" method="post" name="adminForm" id="adminForm">
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
					<table class="table table-striped" id="videoList">
						<thead>
							<tr>
								<th style="width:1%">
									<input type="checkbox" name="checkall-toggle" value="" title="<?php echo Text::_( 'JGLOBAL_CHECK_ALL' ); ?>" onclick="Joomla.checkAll( this )" />
								</th>
								<th class="d-none d-md-table-cell">
									<?php echo HTMLHelper::_( 'searchtools.sort',  'COM_YENDIFVIDEOSHARE_VIDEOS_FEATURED', 'a.featured', $listDirn, $listOrder ); ?>
								</th>
								<th style="width:5%" class="nowrap text-center">
									<?php echo HTMLHelper::_( 'searchtools.sort', 'JSTATUS', 'a.state', $listDirn, $listOrder ); ?>
								</th>
								<th>
									<?php echo HTMLHelper::_( 'searchtools.sort',  'JGLOBAL_TITLE', 'a.title', $listDirn, $listOrder ); ?>
								</th>		
								<th class="d-none d-md-table-cell">
									<?php echo HTMLHelper::_( 'searchtools.sort',  'COM_YENDIFVIDEOSHARE_VIDEOS_USER', 'a.userid', $listDirn, $listOrder ); ?>
								</th>		
								<th class="d-none d-md-table-cell">
									<?php echo HTMLHelper::_( 'searchtools.sort',  'JGRID_HEADING_ACCESS', 'a.access', $listDirn, $listOrder ); ?>
								</th>														
								<th class="text-center d-none d-md-table-cell">
									<?php echo HTMLHelper::_( 'searchtools.sort',  'JGLOBAL_FIELD_ID_LABEL', 'a.id', $listDirn, $listOrder ); ?>
								</th>
								<th class="text-center d-none d-md-table-cell">
									<?php echo Text::_( 'COM_YENDIFVIDEOSHARE_VIDEOS_PLAY' ); ?>
								</th>
							</tr>
						</thead>
						<tfoot>
							<tr>
								<td colspan="10">
									<?php echo $this->pagination->getListFooter(); ?>
								</td>
							</tr>
						</tfoot>
						<tbody>
							<?php foreach ( $this->items as $i => $item ) :
								$canCreate  = $user->authorise( 'core.create', 'com_yendifvideoshare' );
								$canEdit    = $user->authorise( 'core.edit', 'com_yendifvideoshare' );
								$canCheckin = $user->authorise( 'core.manage', 'com_yendifvideoshare' );
								$canChange  = $user->authorise( 'core.edit.state', 'com_yendifvideoshare' );
								?>
								<tr class="row<?php echo $i % 2; ?>" data-item-id="<?php echo $item->id; ?>">
									<td>
										<?php echo HTMLHelper::_( 'grid.id', $i, $item->id ); ?>
									</td>
									<td class="d-none d-md-table-cell text-center">
										<?php echo HTMLHelper::_( 'listhelper.toggle', $item->featured, 'users', 'featured', $i ); ?>
									</td>
									<td class="text-center">
										<?php echo HTMLHelper::_( 'jgrid.published', $item->state, $i, 'users.', $canChange, 'cb' ); ?>
									</td>	
									<td>
										<?php if ( isset( $item->checked_out ) && $item->checked_out && ( $canEdit || $canChange ) ) : ?>
											<?php echo HTMLHelper::_( 'jgrid.checkedout', $i, $item->uEditor, $item->checked_out_time, 'users.', $canCheckin ); ?>
										<?php endif; ?>
										<?php if ( $canEdit ) : ?>
											<a href="<?php echo Route::_( 'index.php?option=com_yendifvideoshare&task=user.edit&id=' . (int) $item->id ); ?>">
												<?php echo $this->escape( $item->title ); ?>
											</a>
										<?php else : ?>
											<?php echo $this->escape( $item->title ); ?>
										<?php endif; ?>

										<!-- Category -->
										<div class="small mt-1">
											<?php echo Text::_( 'COM_YENDIFVIDEOSHARE_VIDEOS_CATEGORY' ); ?>: 
											<?php if ( $canEdit ) : ?>
												<a href="<?php echo Route::_( 'index.php?option=com_yendifvideoshare&task=category.edit&id=' . (int) $item->catid ); ?>">
													<?php echo $this->escape( $item->category ); ?>
												</a>
											<?php else : ?>
												<?php echo $this->escape( $item->category ); ?>
											<?php endif; ?>
										</div>
									</td>								
									<td class="small d-none d-md-table-cell">
										<?php echo $item->user; ?>
									</td>	
									<td class="small d-none d-md-table-cell">
										<?php echo $item->access; ?>
									</td>																
									<td class="text-center d-none d-md-table-cell">
										<?php echo (int) $item->id; ?>
									</td>
									<td class="text-center d-none d-md-table-cell">
										<a
                                            class="btn fs-1 pt-0 mt-0 lh-1" 
                                            data-bs-toggle="modal" 
                                            data-bs-target="#yendif-video-share-modal-box" 
                                            data-bs-title="<?php echo $item->title; ?>" 
                                            data-bs-url="<?php echo Uri::root() . 'index.php?option=com_yendifvideoshare&view=player&format=raw&id=' . $item->id . '&autoplay=1'; ?>"
                                            onclick="return false;">
												<span class="icon-play-circle text-info" aria-hidden="true"></span>
										</a>
									</td>
								</tr>
							<?php endforeach; ?>
						</tbody>
					</table>
				<?php endif; ?>

				<input type="hidden" name="task" value="" />
				<input type="hidden" name="boxchecked" value="0" />
				<input type="hidden" name="list[fullorder]" value="<?php echo $listOrder; ?> <?php echo $listDirn; ?>" />
				<?php echo HTMLHelper::_( 'form.token' ); ?>
			</div>
		</div>
	</div>
</form>

<?php 
echo HTMLHelper::_(
	'bootstrap.renderModal',
	'yendif-video-share-modal-box', // selector
	array( // options
		'modal-dialog-scrollable' => true,
		'title'  => '&nbsp;'
	),
	'<div class="modal-body">&nbsp;</div>'
);
