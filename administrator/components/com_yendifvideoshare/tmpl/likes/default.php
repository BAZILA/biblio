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
use Joomla\CMS\Session\Session;
use Joomla\CMS\Router\Route;
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
?>

<form action="<?php echo Route::_( 'index.php?option=com_yendifvideoshare&view=likes' ); ?>" method="post" name="adminForm" id="adminForm">
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
					<table class="table table-striped" id="likeList">
						<thead>
							<tr>
								<th width="1%">
									<input type="checkbox" name="checkall-toggle" value="" title="<?php echo Text::_( 'JGLOBAL_CHECK_ALL' ); ?>" onclick="Joomla.checkAll( this )"/>
								</th>															
								<th>
									<?php echo HTMLHelper::_( 'searchtools.sort',  'JGLOBAL_TITLE', 'a.title', $listDirn, $listOrder ); ?>
								</th>
								<th class="text-center">
									<?php echo HTMLHelper::_( 'searchtools.sort',  'COM_YENDIFVIDEOSHARE_TITLE_LIKES', 'a.likes', $listDirn, $listOrder ); ?>
								</th>
								<th class="text-center">
									<?php echo HTMLHelper::_( 'searchtools.sort',  'COM_YENDIFVIDEOSHARE_TITLE_DISLIKES', 'a.dislikes', $listDirn, $listOrder ); ?>
								</th>
								<th class="d-none d-md-table-cell">
									<?php echo HTMLHelper::_( 'searchtools.sort',  'COM_YENDIFVIDEOSHARE_TITLE_USER', 'a.userid', $listDirn, $listOrder ); ?>
								</th>
								<th class="text-center d-none d-md-table-cell">
									<?php echo HTMLHelper::_( 'searchtools.sort',  'JGLOBAL_FIELD_ID_LABEL', 'a.id', $listDirn, $listOrder ); ?>
								</th>
							</tr>
						</thead>
						<tbody>
						<?php foreach ( $this->items as $i => $item ) :
							?>
							<tr class="row<?php echo $i % 2; ?>">
								<td>
									<?php echo HTMLHelper::_( 'grid.id', $i, $item->id ); ?>
								</td>			
								<td>
									<?php echo $this->escape( $item->title ); ?>
								</td>
								<td class="text-center">
									<?php echo (int) $item->likes; ?>
								</td>
								<td class="text-center">
									<?php echo (int) $item->dislikes; ?>
								</td>
								<td class="small d-none d-md-table-cell">
									<?php echo ! empty( $item->user ) ? $item->user : 'N/A'; ?>
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