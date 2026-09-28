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
use Joomla\CMS\Uri\Uri;
use PluginsWare\Component\YendifVideoShare\Site\Helper\YendifVideoShareHelper;

HTMLHelper::addIncludePath( JPATH_COMPONENT . '/helpers/html' );
HTMLHelper::_( 'bootstrap.tooltip' );

$wa = $this->document->getWebAssetManager();
$wa->useStyle( 'com_yendifvideoshare.admin' )
	->useScript( 'keepalive' )
	->useScript( 'form.validate' )
    ->useScript( 'com_yendifvideoshare.admin' );

if ( ! empty( $this->item->history ) ) {
	$this->item->history = json_decode( $this->item->history );
}
?>

<form action="<?php echo Route::_( 'index.php?option=com_yendifvideoshare&layout=edit&id=' . (int) $this->item->id ); ?>" method="post" enctype="multipart/form-data" name="adminForm" id="import-form" class="form-validate form-horizontal">
	<?php if ( ! $this->canDo ) : ?>
		<div class="alert alert-warning">
			<?php echo Text::_( 'COM_YENDIFVIDEOSHARE_PREMIUM_DESC_IMPORTS' ); ?>
		</div>
	<?php else : ?>
		<?php if ( ! empty( $this->item->history->last_error ) ) : ?>
			<div class="alert alert-error">
				<?php echo $this->item->history->last_error; ?>
			</div>
		<?php endif; ?>

		<div>
			<?php echo HTMLHelper::_( 'uitab.startTabSet', 'myTab', array( 'active' => 'general' ) ); ?>

			<?php echo HTMLHelper::_( 'uitab.addTab', 'myTab', 'general', Text::_( 'COM_YENDIFVIDEOSHARE_TAB_GENERAL', true ) ); ?>
				<div class="row">
					<div class="col-lg-8">
						<div>
							<fieldset class="options-form">
								<legend><?php echo Text::_( 'COM_YENDIFVIDEOSHARE_FIELDSET_GENERAL' ); ?></legend>
								<?php echo $this->form->renderField( 'title' ); ?>
								<?php echo $this->form->renderField( 'type' ); ?>
								<?php echo $this->form->renderField( 'playlist' ); ?>
								<?php echo $this->form->renderField( 'channel' ); ?>
								<?php echo $this->form->renderField( 'username' ); ?>
								<?php echo $this->form->renderField( 'search' ); ?>
								<?php echo $this->form->renderField( 'videos' ); ?>
								<?php echo $this->form->renderField( 'exclude' ); ?>
								<?php echo $this->form->renderField( 'order_by' ); ?>
								<?php echo $this->form->renderField( 'limit' ); ?>
								<?php echo $this->form->renderField( 'schedule' ); ?>
								<?php echo $this->form->renderField( 'reschedule' ); ?>
							</fieldset>
						</div>
					</div>

					<div class="col-lg-4">
						<fieldset class="form-vertical">
							<legend class="visually-hidden"><?php echo Text::_( 'COM_YENDIFVIDEOSHARE_FIELDSET_GENERAL' ); ?></legend>
							<?php if ( ! empty( $this->item->import_state ) ) :
								if ( $this->item->schedule > 0 ) {
									if ( $this->item->state == 1 || $this->item->import_state == 'completed' ) {
										// Do nothing
									} else {
										$this->item->import_state = 'paused';
									}
								} else {
									$this->item->import_state = 'imported';
								}

								$this->item->imported = 0;
								$this->item->excluded = 0;
								$this->item->ignored  = 0;

								foreach ( $this->item->history->data as $data ) {
									$this->item->imported += (int) $data->imported;
									$this->item->excluded += (int) $data->excluded; 
									$this->item->ignored  += (int) $data->duplicates; 
								} 
								?>
								<ul class="list-group mt-2 mb-4">
									<li class="list-group-item active" aria-current="true">
										<span class="fw-bold"><?php echo Text::_( 'COM_YENDIFVIDEOSHARE_IMPORT_STATE' ); ?></span>: 
										<?php echo Text::_( 'COM_YENDIFVIDEOSHARE_IMPORT_STATE_' . strtoupper( $this->item->import_state ) ); ?>
									</li>
									<li class="list-group-item">
										<span class="fw-bold text-success"><?php echo Text::_( 'COM_YENDIFVIDEOSHARE_IMPORT_VIDEOS_IMPORTED' ); ?></span>: 
										<a href="<?php echo Route::_( 'index.php?option=com_yendifvideoshare&view=videos&filter_import_id=' . (int) $this->item->id . '&filter_search=&limitstart=1' ); ?>" target="_blank">
											<?php Text::printf( 'COM_YENDIFVIDEOSHARE_IMPORT_N_VIDEOS_IMPORTED', $this->item->imported ); ?>
										</a>
									</li>
									<li class="list-group-item">
										<span class="fw-bold text-success"><?php echo Text::_( 'COM_YENDIFVIDEOSHARE_IMPORT_VIDEOS_EXCLUDED' ); ?></span>: 
										<?php Text::printf(	'COM_YENDIFVIDEOSHARE_IMPORT_N_VIDEOS_EXCLUDED', $this->item->excluded ); ?>
									</li>
									<li class="list-group-item">
										<span class="fw-bold text-success"><?php echo Text::_( 'COM_YENDIFVIDEOSHARE_IMPORT_VIDEOS_IGNORED' ); ?></span>: 
										<?php Text::printf(	'COM_YENDIFVIDEOSHARE_IMPORT_N_VIDEOS_IGNORED', $this->item->ignored ); ?>
									</li>
									<li class="list-group-item">
										<span class="fw-bold text-success"><?php echo Text::_( 'COM_YENDIFVIDEOSHARE_IMPORT_NEXT_DATE' ); ?></span>: 
										<?php echo ! empty( $this->item->next_import_date ) ? $this->item->next_import_date : 'N/A'; ?>
									</li>
								</ul>
							<?php endif; ?>
							
							<?php echo $this->form->renderField( 'state' ); ?>
							<?php echo $this->form->renderField( 'video_catid' ); ?>
							<?php echo $this->form->renderField( 'video_description' ); ?>
							<?php echo $this->form->renderField( 'video_date' ); ?>						
							<?php echo $this->form->renderField( 'video_userid' ); ?>
							<?php echo $this->form->renderField( 'video_state' ); ?>
						</fieldset>
					</div>
				</div>
			<?php echo HTMLHelper::_( 'uitab.endTab' ); ?>

			<?php echo HTMLHelper::_( 'uitab.addTab', 'myTab', 'import-history', Text::_( 'COM_YENDIFVIDEOSHARE_TAB_IMPORT_HISTORY', true ) ); ?>
				<div class="row-fluid">
					<div class="span10 form-horizontal">
						<fieldset class="options-form">
							<legend><?php echo Text::_( 'COM_YENDIFVIDEOSHARE_FIELDSET_IMPORT_HISTORY' ); ?></legend>

							<?php if ( ! empty( $this->item->import_state ) ) : ?>
								<?php 
								$this->item->history->data = array_reverse( $this->item->history->data );

								foreach ( $this->item->history->data as $data ) : ?>
									<p>
										<a href="<?php echo Route::_( 'index.php?option=com_yendifvideoshare&view=videos&filter_import_id=' . (int) $this->item->id . '&filter_search=import:' . $data->key . '&limitstart=1' ); ?>" target="_blank">
											<?php
											Text::printf( 
												'COM_YENDIFVIDEOSHARE_IMPORT_N_VIDEOS_IMPORTED_ON_DATE',
												$data->imported,
												$data->date
											);
											?>
										</a>
									</p>
								<?php endforeach; ?>
							<?php else: ?>
								<div class="alert alert-info">
									<?php echo Text::_( 'COM_YENDIFVIDEOSHARE_IMPORT_EMPTY' ); ?>
								</div>
							<?php endif; ?>
						</fieldset>
					</div>
				</div>
			<?php echo HTMLHelper::_( 'uitab.endTab' ); ?>

			<input type="hidden" id="jform_id" name="jform[id]" value="<?php echo $this->item->id; ?>" />
			<input type="hidden" name="jform[checked_out]" value="<?php echo $this->item->checked_out; ?>" />
			<input type="hidden" name="jform[checked_out_time]" value="<?php echo $this->item->checked_out_time; ?>" />
			<?php echo $this->form->renderField( 'created_by' ); ?>
			<?php echo $this->form->renderField( 'modified_by' ); ?>
			
			<?php echo HTMLHelper::_( 'uitab.endTabSet' ); ?>
		</div>
	<?php endif; ?>

	<input type="hidden" name="task" value="" />
	<?php echo HTMLHelper::_( 'form.token' ); ?>
</form>
