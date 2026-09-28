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

$app = Factory::getApplication();

HTMLHelper::_( 'bootstrap.tooltip' );

// Import CSS
$wa = Factory::getApplication()->getDocument()->getWebAssetManager();
$wa->useScript( 'com_yendifvideoshare.modal' );

$listOrder = $this->state->get( 'list.ordering' );
$listDirn  = $this->state->get( 'list.direction' );
?>

<div class="container-popup">
	<form action="<?php echo Route::_('index.php?option=com_yendifvideoshare&view=videos&layout=related&tmpl=component&' . Session::getFormToken() . '=1' ); ?>" method="post" name="adminForm" id="adminForm" class="form-inline">
		<div class="row">
			<div class="col-md-12">
				<div id="j-main-container">
					<?php echo LayoutHelper::render( 'joomla.searchtools.default', array( 'view' => $this ) ); ?>
					<div class="clearfix"></div>

					<?php if ( empty( $this->items ) ) : ?>
						<div class="alert alert-warning">
							<?php echo Text::_( 'JGLOBAL_NO_MATCHING_RESULTS' ); ?>
						</div>
					<?php else : ?>
						<table class="table table-striped" id="relatedVideosList">
							<thead>
								<tr>	
									<th style="width:1%">
										<input type="checkbox" name="checkall-toggle" value="" title="<?php echo Text::_( 'JGLOBAL_CHECK_ALL' ); ?>" onclick="Joomla.checkAll( this )" />
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
										<?php echo HTMLHelper::_( 'searchtools.sort',  'COM_YENDIFVIDEOSHARE_VIDEOS_VIEWS', 'a.views', $listDirn, $listOrder ); ?>
									</th>	
									<th class="d-none d-md-table-cell">
										<?php echo HTMLHelper::_( 'searchtools.sort',  'COM_YENDIFVIDEOSHARE_VIDEOS_FEATURED', 'a.featured', $listDirn, $listOrder ); ?>
									</th>
									<th style="width:5%" class="nowrap text-center">
										<?php echo HTMLHelper::_( 'searchtools.sort', 'JSTATUS', 'a.state', $listDirn, $listOrder ); ?>
									</th>																
									<th class="text-center d-none d-md-table-cell">
										<?php echo HTMLHelper::_( 'searchtools.sort',  'JGLOBAL_FIELD_ID_LABEL', 'a.id', $listDirn, $listOrder ); ?>
									</th>
								</tr>
							</thead>
							<tbody>
								<?php foreach ( $this->items as $i => $item ) :	
									$params = array(
										'id'       => $item->id,
										'title'    => $item->title,
										'category' => $item->category,
										'user'     => $item->user,
										'access'   => $item->access,
										'views'    => $item->views,
										'featured' => $item->featured,
										'state'    => $item->state
									);
									?>
									<tr id="yendif-video-share-related-item-<?php echo $item->id; ?>" class="row<?php echo $i % 2; ?>" data-params='<?php echo json_encode( $params ); ?>'>	
										<td>
											<?php echo HTMLHelper::_( 'grid.id', $i, $item->id ); ?>
										</td>									
										<td>
											<a href="javascript:void(0)" class="yendif-video-share-related-item">
												<?php echo $this->escape( $item->title ); ?>
											</a>

											<!-- Category -->
											<div class="small mt-1">
												<?php echo Text::_( 'COM_YENDIFVIDEOSHARE_VIDEOS_CATEGORY' ); ?>: <?php echo $this->escape( $item->category ); ?>
											</div>
										</td>	
										<td class="small d-none d-md-table-cell">
											<?php echo $item->user; ?>
										</td>							
										<td class="small d-none d-md-table-cell">
											<?php echo $item->access; ?>
										</td>
										<td class="text-center btns d-none d-md-table-cell itemnumber">
											<a href="javascript:void(0);" class="btn btn-secondary small"><?php echo (int) $item->views; ?></a>
										</td>
										<td class="d-none d-md-table-cell text-center">
											<span class="tbody-icon">
												<?php if ( $item->featured ) : ?>
													<span class="icon-publish" aria-hidden="true"></span>
												<?php else : ?>
													<span class="icon-unpublish" aria-hidden="true"></span>
												<?php endif; ?>
											</span>
										</td>
										<td class="text-center">
											<span class="tbody-icon">
												<?php if ( $item->state ) : ?>
													<span class="icon-publish" aria-hidden="true"></span>
												<?php else : ?>
													<span class="icon-unpublish" aria-hidden="true"></span>
												<?php endif; ?>
											</span>
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
					<input type="hidden" name="forcedLanguage" value="<?php echo $app->input->get( 'forcedLanguage', '', 'CMD' ); ?>" />
					<?php echo HTMLHelper::_( 'form.token' ); ?>
				</div>
			</div>
		</div>
	</form>
</div>