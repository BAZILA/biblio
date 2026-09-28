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

HTMLHelper::_( 'bootstrap.tooltip' );
HTMLHelper::_( 'draggablelist.draggable' );

$wa = $this->document->getWebAssetManager();
$wa->useStyle( 'com_yendifvideoshare.admin' )
	->useScript( 'keepalive' )
	->useScript( 'form.validate' )
    ->useScript( 'com_yendifvideoshare.admin' );

$inlineScript = "
	if ( typeof( yendif ) === 'undefined' ) {
		var yendif = {};
	};

	yendif.params = " . json_encode( $this->item->related ) . ";
	yendif.i18n_category = '" . Text::_( 'COM_YENDIFVIDEOSHARE_VIDEOS_CATEGORY' ) . "';
";

$wa->addInlineScript( $inlineScript, [ 'position' => 'before' ], [], [ 'com_yendifvideoshare.admin' ] );
?>

<form action="<?php echo Route::_( 'index.php?option=com_yendifvideoshare&layout=edit&id=' . (int) $this->item->id ); ?>" method="post" enctype="multipart/form-data" name="adminForm" id="video-form" class="form-validate form-horizontal">
	<?php echo LayoutHelper::render( 'joomla.edit.title_alias', $this ); ?>

	<div>
		<?php echo HTMLHelper::_( 'uitab.startTabSet', 'myTab', array( 'active' => 'general' ) ); ?>

		<?php echo HTMLHelper::_( 'uitab.addTab', 'myTab', 'general', Text::_( 'COM_YENDIFVIDEOSHARE_TAB_GENERAL', true ) ); ?>
			<div class="row">
				<div class="col-lg-8">
					<div>
						<fieldset class="options-form">
							<legend><?php echo Text::_( 'COM_YENDIFVIDEOSHARE_FIELDSET_GENERAL' ); ?></legend>							
							<?php echo $this->form->renderField( 'type' ); ?>
							<?php echo $this->form->renderField( 'mp4' ); ?>
							<?php echo $this->form->renderField( 'mp4_hd' ); ?>
							<?php if ( ! empty( $this->item->webm ) ) echo $this->form->renderField( 'webm' ); ?>
							<?php if ( ! empty( $this->item->ogv ) ) echo $this->form->renderField( 'ogv' ); ?>
							<?php echo $this->form->renderField( 'youtube' ); ?>
							<?php echo $this->form->renderField( 'vimeo' ); ?>
							<?php echo $this->form->renderField( 'hls' ); ?>
							<?php echo $this->form->renderField( 'dash' ); ?>
							<?php echo $this->form->renderField( 'thirdparty' ); ?>
							<?php echo $this->form->renderField( 'image' ); ?>
							<?php echo $this->form->renderField( 'captions' ); ?>
							<?php echo $this->form->renderField( 'duration' ); ?>
							<?php echo $this->form->renderField( 'description' ); ?>						
						</fieldset>
					</div>
				</div>

				<div class="col-lg-4">
					<fieldset class="form-vertical">
						<legend class="visually-hidden"><?php echo Text::_( 'COM_YENDIFVIDEOSHARE_FIELDSET_GENERAL' ); ?></legend>
						<?php echo $this->form->renderField( 'catid' ); ?>						
						<?php echo $this->form->renderField( 'userid' ); ?>						
						<?php echo $this->form->renderField( 'access' ); ?>
						<?php echo $this->form->renderField( 'views' ); ?>
						<?php echo $this->form->renderField( 'featured' ); ?>
						<?php echo $this->form->renderField( 'state' ); ?>		
						<?php echo $this->form->renderField( 'created_date' ); ?>				
						<?php echo $this->form->renderField( 'published_up' ); ?>
						<?php echo $this->form->renderField( 'published_down' ); ?>
					</fieldset>
				</div>
			</div>
		<?php echo HTMLHelper::_( 'uitab.endTab' ); ?>

		<?php echo HTMLHelper::_( 'uitab.addTab', 'myTab', 'adverts', Text::_( 'COM_YENDIFVIDEOSHARE_TAB_ADVERTS', true ) ); ?>
			<div class="row-fluid">
				<div class="span10 form-horizontal">
					<fieldset class="options-form">
						<legend><?php echo Text::_( 'COM_YENDIFVIDEOSHARE_FIELDSET_ADVERTS' ); ?></legend>
						<?php echo $this->form->renderField( 'preroll' ); ?>
						<?php echo $this->form->renderField( 'postroll' ); ?>
					</fieldset>
				</div>
			</div>
		<?php echo HTMLHelper::_( 'uitab.endTab' ); ?>

		<?php echo HTMLHelper::_( 'uitab.addTab', 'myTab', 'related', Text::_( 'COM_YENDIFVIDEOSHARE_TAB_RELATED', true ) ); ?>
			<div class="row-fluid">
				<div class="span10 form-horizontal">
					<fieldset class="options-form">
						<legend><?php echo Text::_( 'COM_YENDIFVIDEOSHARE_FIELDSET_RELATED' ); ?></legend>

						<p class="alert alert-info"><?php echo Text::_( 'COM_YENDIFVIDEOSHARE_RELATED_NOTE' ); ?></p>
						
						<table class="table table-striped" id="videoList">
							<thead>
								<tr>
									<td width="1%" class="text-center d-none d-md-table-cell">
										<span class="icon-menu-2" aria-hidden="true"></span>
									</td>																	
									<td>
										<?php echo Text::_( 'JGLOBAL_TITLE' ); ?>
									</td>			
									<td class="d-none d-md-table-cell">
										<?php echo Text::_( 'COM_YENDIFVIDEOSHARE_VIDEOS_USER' ); ?>
									</td>			
									<td class="d-none d-md-table-cell">
										<?php echo Text::_( 'JGRID_HEADING_ACCESS' ); ?>
									</td>
									<td class="text-center d-none d-md-table-cell">
										<?php echo Text::_( 'COM_YENDIFVIDEOSHARE_VIDEOS_VIEWS' ); ?>
									</td>	
									<td class="text-center d-none d-md-table-cell">
										<?php echo Text::_( 'COM_YENDIFVIDEOSHARE_VIDEOS_FEATURED' ); ?>
									</td>
									<td style="width:5%" class="nowrap text-center">
										<?php echo Text::_( 'JSTATUS' ); ?>
									</td>																
									<td class="text-center d-none d-md-table-cell">
										<?php echo Text::_( 'JGLOBAL_FIELD_ID_LABEL' ); ?>
									</td>
									<td>&nbsp;
										
									</td>
								</tr>
							</thead>
							<tbody id="yendif-video-share-related-items" class="js-draggable">
								<?php foreach ( (array) $this->item->related as $i => $item ) :	?>
									<tr id="yendif-video-share-related-item-<?php echo $item->id; ?>">	
										<td class="text-center d-none d-md-table-cell">
											<span class="sortable-handler">
												<span class="icon-ellipsis-v" aria-hidden="true"></span>
											</span>
										</td>
										<td>
											<?php echo $this->escape( $item->title ); ?>
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
											<input type="hidden" name="jform[related][]" value="<?php echo (int) $item->id; ?>" />
										</td>
										<td class="text-center">
											<span class="tbody-icon">
												<a href="javascript: void(0);" class="yendif-video-share-related-item-remove">
													<span class="icon-delete" aria-hidden="true"></span>
												</a>
											</span>
										</td>
									</tr>
								<?php endforeach; ?>
							</tbody>
						</table>

						<div id="yendif-video-share-related-empty-note" class="description text-center<?php if ( ! empty( $this->item->related ) ) { echo ' hidden'; } ?>">
							<?php echo Text::_( 'COM_YENDIFVIDEOSHARE_NO_VIDEO_SELECTED' ); ?>
						</div>

						<div class="clearfix">
							<button 
								type="button" 
								class="btn btn-secondary float-end"
								data-bs-toggle="modal" 
								data-bs-target="#yendif-video-share-related-modal" 
								onclick="return false;">
									<span class="icon-save-new" aria-hidden="true"></span> 
									<?php echo Text::_( 'COM_YENDIFVIDEOSHARE_BUTTON_SELECT_VIDEOS' ); ?>
							</button>
						</div>
					</fieldset>
				</div>
			</div>
		<?php echo HTMLHelper::_( 'uitab.endTab' ); ?>

		<?php echo HTMLHelper::_( 'uitab.addTab', 'myTab', 'advanced', Text::_( 'COM_YENDIFVIDEOSHARE_TAB_ADVANCED', true ) ); ?>
			<div class="row-fluid">
				<div class="span10 form-horizontal">
					<fieldset class="options-form">
						<legend><?php echo Text::_( 'COM_YENDIFVIDEOSHARE_FIELDSET_ADVANCED' ); ?></legend>
						<?php echo $this->form->renderField( 'meta_keywords' ); ?>
						<?php echo $this->form->renderField( 'meta_description' ); ?>
					</fieldset>
				</div>
			</div>
		<?php echo HTMLHelper::_( 'uitab.endTab' ); ?>

		<input type="hidden" name="jform[id]" value="<?php echo $this->item->id; ?>" />
		<input type="hidden" name="jform[ordering]" value="<?php echo $this->item->ordering; ?>" />
		<input type="hidden" name="jform[checked_out]" value="<?php echo $this->item->checked_out; ?>" />
		<input type="hidden" name="jform[checked_out_time]" value="<?php echo $this->item->checked_out_time; ?>" />
		<?php echo $this->form->renderField( 'created_by' ); ?>
		<?php echo $this->form->renderField( 'modified_by' ); ?>
		<?php echo $this->form->renderField( 'updated_date' ); ?>
		
		<?php echo HTMLHelper::_( 'uitab.endTabSet' ); ?>
	</div>

	<input type="hidden" name="task" value="" />
	<?php echo HTMLHelper::_( 'form.token' ); ?>
</form>

<?php 
echo HTMLHelper::_(
	'bootstrap.renderModal',
	'yendif-video-share-related-modal', // selector
	array( // options
		'title'       => Text::_( 'COM_YENDIFVIDEOSHARE_BUTTON_SELECT_VIDEOS' ),
		'url'         => Route::_( 'index.php?option=com_yendifvideoshare&view=videos&layout=related&tmpl=component&' . Session::getFormToken() . '=1' ),
		'height'      => '400px',
		'width'       => '800px',
		'modalWidth'  => 80,
		'footer'      => '<button type="button" id="yendif-video-share-related-insert-btn" class="btn btn-secondary">' . Text::_( 'COM_YENDIFVIDEOSHARE_BUTTON_INSERT_VIDEOS' ) . '</button> <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">' . Text::_( 'JLIB_HTML_BEHAVIOR_CLOSE' ) . '</button>',
	)
);