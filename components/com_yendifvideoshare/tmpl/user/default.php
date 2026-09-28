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

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Session\Session;
use Joomla\CMS\Uri\Uri;
use \PluginsWare\Component\YendifVideoShare\Site\Helper\YendifVideoShareHelper;
use \PluginsWare\Component\YendifVideoShare\Site\Helper\YendifVideoShareRoute;

HTMLHelper::_( 'bootstrap.tooltip' );

try {
	HTMLHelper::_( 'formbehavior.chosen', 'select' );
} catch ( \Throwable $e ) {
	// Fallback: TODO
}

$app = Factory::getApplication();
$user = Factory::getUser();

// Vars
$userId    = $user->get( 'id' );
$listOrder = $this->state->get( 'list.ordering' );
$listDirn  = $this->state->get( 'list.direction' );
$canCreate = $user->authorise( 'core.create', 'com_yendifvideoshare' ) && file_exists( JPATH_COMPONENT . DIRECTORY_SEPARATOR . 'forms' . DIRECTORY_SEPARATOR . 'videoform.xml' );
$canEdit   = ( $user->authorise( 'core.edit', 'com_yendifvideoshare' ) || $user->authorise( 'core.edit.own', 'com_yendifvideoshare' ) ) && file_exists( JPATH_COMPONENT .  DIRECTORY_SEPARATOR . 'forms' . DIRECTORY_SEPARATOR . 'videoform.xml' );
$canState  = $user->authorise( 'core.edit.state', 'com_yendifvideoshare' );
$canDelete = $user->authorise( 'core.delete', 'com_yendifvideoshare' );
$canDo     = YendifVideoShareHelper::canDo();

// Import CSS
$wa = $app->getDocument()->getWebAssetManager();

if ( $this->params->get( 'load_bootstrap' ) ) {
	$wa->useStyle( 'com_yendifvideoshare.bootstrap' );
}

$wa->useStyle( 'com_yendifvideoshare.site' );

if ( $css = $this->params->get( 'custom_css' ) ) {
    $wa->addInlineStyle( $css );
}
?>

<div id="yendif-video-share-manage-videos" class="yendif-video-share manage-videos mb-4">
	<form action="<?php echo htmlspecialchars( Uri::getInstance()->toString() ); ?>" method="post" name="adminForm" id="adminForm">
		<?php if ( $this->params->get( 'show_page_heading' ) ) : ?>
			<div class="page-header">
				<h1>
					<?php if ( $this->escape( $this->params->get( 'page_heading' ) ) ) : ?>
						<?php echo $this->escape( $this->params->get( 'page_heading' ) ); ?>
					<?php else : ?>
						<?php echo $this->escape( $this->params->get( 'page_title' ) ); ?>
					<?php endif; ?>
				</h1>
			</div>
		<?php endif; ?>

		<?php if ( ! empty( $this->filterForm ) ) { echo LayoutHelper::render( 'joomla.searchtools.default', array( 'view' => $this ) ); } ?>
		
		<div class="table-responsive">
			<table class="table table-striped" id="userVideoList">
				<thead>
					<tr>				
						<th class="text-center d-none d-md-table-cell">
							<?php echo HTMLHelper::_( 'grid.sort',  'JGLOBAL_FIELD_ID_LABEL', 'a.id', $listDirn, $listOrder ); ?>
						</th>
						<th>
							<?php echo HTMLHelper::_( 'grid.sort',  'JGLOBAL_TITLE', 'a.title', $listDirn, $listOrder ); ?>
						</th>
						<th class="d-none d-md-table-cell">
							<?php echo HTMLHelper::_( 'grid.sort',  'COM_YENDIFVIDEOSHARE_USER_CATEGORIES', 'a.catid', $listDirn, $listOrder ); ?>
						</th>
						<th class="text-center d-none d-md-table-cell">
							<?php echo HTMLHelper::_( 'grid.sort',  'COM_YENDIFVIDEOSHARE_USER_VIEWS', 'a.views', $listDirn, $listOrder ); ?>
						</th>
						<th class="text-center d-none d-md-table-cell">
							<?php echo HTMLHelper::_( 'grid.sort',  'COM_YENDIFVIDEOSHARE_USER_FEATURED', 'a.featured', $listDirn, $listOrder ); ?>
						</th>
						<th class="text-center d-none d-md-table-cell">
							<?php echo HTMLHelper::_( 'grid.sort', 'JPUBLISHED', 'a.state', $listDirn, $listOrder ); ?>
						</th>
						<?php if ( $canEdit || $canDelete ) : ?>
							<th class="text-center">
								<?php echo Text::_( 'COM_YENDIFVIDEOSHARE_USER_ACTIONS' ); ?>
							</th>
						<?php endif; ?>
					</tr>
				</thead>
				<tbody>
					<?php foreach ( $this->items as $i => $item ) : ?>
						<tr class="row<?php echo $i % 2; ?>">						
							<td class="text-center d-none d-md-table-cell">
								<?php echo $item->id; ?>
							</td>
							<td>
								<a href="<?php echo YendifVideoShareRoute::getVideoRoute( $item ); ?>">
									<?php echo $this->escape( $item->title ); ?>
								</a>
							</td>
							<td class="d-none d-md-table-cell">
								<?php echo $item->category; ?>
							</td>
							<td class="text-center d-none d-md-table-cell">
								<?php echo (int) $item->views; ?>
							</td>
							<td class="text-center d-none d-md-table-cell">
								<?php if ( ! empty( $item->featured ) ) : ?>
									<?php echo Text::_( 'JYES' ); ?>
								<?php else : ?>
									<?php echo Text::_( 'JNO' ); ?>
								<?php endif; ?>
							</td>
							<td class="text-center d-none d-md-table-cell">
								<?php $class = ( $canState ) ? 'active' : 'disabled'; ?>

								<a class="btn btn-sm <?php echo $class; ?>" href="<?php echo ( $canState ) ? Route::_( 'index.php?option=com_yendifvideoshare&task=videoform.publish&id=' . $item->id . '&state=' . ( ( $item->state + 1 ) % 2 ), false, 2 ) : '#'; ?>">
									<?php if ( $item->state == 1 ): ?>
										<i class="icon-publish"></i>
									<?php else: ?>
										<i class="icon-unpublish"></i>
									<?php endif; ?>
								</a>
							</td>
							<?php if ( $canEdit || $canDelete ) : ?>
								<td class="text-center">
									<?php if ( $canEdit ) : ?>
										<a href="<?php echo Route::_( 'index.php?option=com_yendifvideoshare&task=videoform.edit&id=' . $item->id, false, 2 ); ?>" class="btn btn-mini" type="button"><i class="icon-edit" ></i></a>
									<?php endif; ?>

									<?php if ( $canDelete ) : ?>
										<a href="<?php echo Route::_( 'index.php?option=com_yendifvideoshare&task=videoform.remove&id=' . $item->id, false, 2 ); ?>" class="btn btn-mini delete-button" type="button"><i class="icon-trash" ></i></a>
									<?php endif; ?>
								</td>
							<?php endif; ?>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>

			<?php if ( empty( $this->items ) ) : ?>
				<p class="text-center text-muted">
					<?php echo Text::_( 'COM_YENDIFVIDEOSHARE_NO_ITEMS_FOUND' ); ?>
				</p>
			<?php endif; ?>
		</div>

		<?php echo $this->pagination->getListFooter(); ?>
		
		<?php if ( $canCreate ) : ?>
			<a href="<?php echo Route::_( 'index.php?option=com_yendifvideoshare&task=videoform.edit&id=0', false, 0 ); ?>" class="btn btn-success btn-small"><i class="icon-plus"></i>
				<?php echo Text::_( 'COM_YENDIFVIDEOSHARE_ADD_NEW_VIDEO' ); ?>
			</a>
		<?php endif; ?>

		<input type="hidden" name="task" value="" />
		<input type="hidden" name="boxchecked" value="0" />
		<input type="hidden" name="filter_order" value="" />
		<input type="hidden" name="filter_order_Dir" value="" />
		<?php echo HTMLHelper::_( 'form.token' ); ?>
	</form>

	<?php
	if ( $canDelete ) {
		$wa->addInlineScript("
			jQuery(document).ready(function() {
				jQuery( '.delete-button' ).click( deleteItem );
			});

			function deleteItem() {
				if ( ! confirm( \"" . Text::_( 'COM_YENDIFVIDEOSHARE_DELETE_MESSAGE' ) . "\" ) ) {
					return false;
				}
			}
		", [], [], ["jquery"]);
	}
	?>
</div>