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

<form action="<?php echo Route::_( 'index.php?option=com_yendifvideoshare&view=imports' ); ?>" method="post" name="adminForm" id="adminForm">
	<?php if ( ! $this->canDo ) : ?>
		<div class="alert alert-warning">
			<?php echo Text::_( 'COM_YENDIFVIDEOSHARE_PREMIUM_DESC_IMPORTS' ); ?>
		</div>
	<?php else : ?>
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
						<table class="table table-striped" id="importList">
							<thead>
								<tr>
									<th style="width:1%">
										<input type="checkbox" name="checkall-toggle" value="" title="<?php echo Text::_( 'JGLOBAL_CHECK_ALL' ); ?>" onclick="Joomla.checkAll( this )" />
									</th>
									<th style="width:5%" class="nowrap text-center">
										<?php echo HTMLHelper::_( 'searchtools.sort', 'JSTATUS', 'a.state', $listDirn, $listOrder ); ?>
									</th>
									<th>
										<?php echo HTMLHelper::_( 'searchtools.sort',  'JGLOBAL_TITLE', 'a.title', $listDirn, $listOrder ); ?>
									</th>
									<th class="d-none d-md-table-cell">
										<?php echo HTMLHelper::_( 'searchtools.sort',  'COM_YENDIFVIDEOSHARE_VIDEOS_CATEGORY', 'a.video_catid', $listDirn, $listOrder ); ?>
									</th>	
									<th class="d-none d-md-table-cell">
										<?php echo HTMLHelper::_( 'searchtools.sort',  'COM_YENDIFVIDEOSHARE_IMPORTS_TYPE', 'a.type', $listDirn, $listOrder ); ?>
									</th>									
									<th class="d-none d-md-table-cell">
										<?php echo HTMLHelper::_( 'searchtools.sort', 'COM_YENDIFVIDEOSHARE_IMPORTS_STATE', 'a.import_state', $listDirn, $listOrder ); ?>
									</th>	
									<th class="d-none d-md-table-cell">
										<?php echo HTMLHelper::_( 'searchtools.sort', 'COM_YENDIFVIDEOSHARE_IMPORTS_NEXT_IMPORT_DATE', 'a.next_import_date', $listDirn, $listOrder ); ?>
									</th>							
									<th class="text-center d-none d-md-table-cell">
										<?php echo HTMLHelper::_( 'searchtools.sort',  'JGLOBAL_FIELD_ID_LABEL', 'a.id', $listDirn, $listOrder ); ?>
									</th>
								</tr>
							</thead>
							<tbody>
								<?php foreach ( $this->items as $i => $item ) :
									$canCreate  = $user->authorise( 'core.create', 'com_yendifvideoshare' );
									$canEdit    = $user->authorise( 'core.edit', 'com_yendifvideoshare' );
									$canCheckin = $user->authorise( 'core.manage', 'com_yendifvideoshare' );
									$canChange  = $user->authorise( 'core.edit.state', 'com_yendifvideoshare' );
									?>
									<tr class="row<?php echo $i % 2; ?>">
										<td>
											<?php echo HTMLHelper::_( 'grid.id', $i, $item->id ); ?>
										</td>
										<td class="text-center">
											<?php echo HTMLHelper::_( 'jgrid.published', $item->state, $i, 'imports.', $canChange, 'cb' ); ?>
										</td>
										<td>
											<?php if ( isset( $item->checked_out ) && $item->checked_out && ( $canEdit || $canChange ) ) : ?>
												<?php echo HTMLHelper::_( 'jgrid.checkedout', $i, $item->uEditor, $item->checked_out_time, 'imports.', $canCheckin ); ?>
											<?php endif; ?>
											<?php if ( $canEdit ) : ?>
												<a href="<?php echo Route::_( 'index.php?option=com_yendifvideoshare&task=import.edit&id=' . (int) $item->id ); ?>">
													<?php echo $this->escape( $item->title ); ?>
												</a>
											<?php else : ?>
												<?php echo $this->escape( $item->title ); ?>
											<?php endif; ?>
										</td>
										<td class="small d-none d-md-table-cell">
											<?php echo $this->escape( $item->video_category ); ?>
										</td>
										<td class="small d-none d-md-table-cell">
											<?php echo Text::_( 'COM_YENDIFVIDEOSHARE_FORM_OPTION_IMPORT_TYPE_' . strtoupper( $item->type ) ); ?>
										</td>										
										<td class="small d-none d-md-table-cell">
											<?php 
											if ( ! empty( $item->import_state ) ) {
												if ( $item->schedule > 0 ) {
													if ( $item->state == 1 || $item->import_state == 'completed' ) {
														// Do nothing
													} else {
														$item->import_state = 'paused';
													}
												} else {
													$item->import_state = 'imported';
												}
											}

											echo Text::_( 'COM_YENDIFVIDEOSHARE_IMPORT_STATE_' . strtoupper( $item->import_state ) );
											?>
										</td>							
										<td class="small d-none d-md-table-cell">
											<?php echo ! empty( $item->next_import_date ) ? $item->next_import_date : 'N/A'; ?>
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
				</div>
			</div>
		</div>
	<?php endif; ?>

	<input type="hidden" name="task" value="" />
	<input type="hidden" name="boxchecked" value="0" />
	<input type="hidden" name="list[fullorder]" value="<?php echo $listOrder; ?> <?php echo $listDirn; ?>" />
	<?php echo HTMLHelper::_( 'form.token' ); ?>
</form>