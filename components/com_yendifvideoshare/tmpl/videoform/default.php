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
use Joomla\CMS\Router\Route;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;
use PluginsWare\Component\YendifVideoShare\Site\Helper\YendifVideoShareHelper;

HTMLHelper::_( 'bootstrap.tooltip' );

$app = Factory::getApplication();

// Import CSS & JS
$wa = $app->getDocument()->getWebAssetManager();

if ( $this->params->get( 'load_bootstrap' ) ) {
	$wa->useStyle( 'com_yendifvideoshare.bootstrap' );
}

$wa->useStyle( 'com_yendifvideoshare.site' );

if ( $css = $this->params->get( 'custom_css' ) ) {
    $wa->addInlineStyle( $css );
}

$wa->useScript( 'keepalive' )
	->useScript( 'form.validate' )
	->useScript( 'com_yendifvideoshare.form' );

$canEdit  = YendifVideoShareHelper::canUserEdit( $this->item );
$canState = Factory::getUser()->authorise( 'core.edit.state','com_yendifvideoshare' );

$related = array();
if ( isset( $this->item->related ) && ! empty( $this->item->related ) ) {
	foreach ( (array) $this->item->related as $id ) {
		$related[] = $id;
	}		
}
$related = array_map( 'intval', $related );
$related = array_filter( $related );
$related = implode( ',', $related );
?>

<div id="yendif-video-share-video-form" class="yendif-video-share video-form mb-4">
	<?php if ( ! $canEdit ) : ?>
		<?php $app->enqueueMessage( Text::_( 'COM_YENDIFVIDEOSHARE_NO_PERMISSION_EDIT' ), 'error' ); ?>
	<?php else : ?>
		<div class="page-header">
			<?php if ( ! empty( $this->item->id ) ) : ?>
				<h1><?php echo Text::_( 'COM_YENDIFVIDEOSHARE_EDIT_VIDEO_TITLE' ); ?></h1>
			<?php else: ?>
				<h1><?php echo Text::_( 'COM_YENDIFVIDEOSHARE_ADD_VIDEO_TITLE' ); ?></h1>
			<?php endif; ?>
		</div>

		<form id="form-video" action="<?php echo Route::_( 'index.php?option=com_yendifvideoshare&task=videoform.save' ); ?>" method="post" class="form-validate form-horizontal" enctype="multipart/form-data">
			<input type="hidden" name="jform[id]" value="<?php echo isset( $this->item->id ) ? (int) $this->item->id : ''; ?>" />
			<input type="hidden" name="jform[access]" value="<?php echo isset( $this->item->access ) ? (int) $this->item->access : 1; ?>" />
			<input type="hidden" name="jform[views]" value="<?php echo isset( $this->item->views ) ? (int) $this->item->views : 0; ?>" />
			<input type="hidden" name="jform[featured]" value="<?php echo isset( $this->item->featured ) ? (int) $this->item->featured : 0; ?>" />
			<input type="hidden" name="jform[rating]" value="<?php echo isset( $this->item->rating ) ? (float) $this->item->rating : 0; ?>" />
			<input type="hidden" name="jform[preroll]" value="<?php echo isset( $this->item->preroll ) ? (int) $this->item->preroll : -1; ?>" />
			<input type="hidden" name="jform[postroll]" value="<?php echo isset( $this->item->postroll ) ? (int) $this->item->postroll : -1; ?>" />
			<input type="hidden" name="jform[related]" value="<?php echo $related; ?>" />
			<input type="hidden" name="jform[ordering]" value="<?php echo isset( $this->item->ordering ) ? (int) $this->item->ordering : ''; ?>" />
			<input type="hidden" name="jform[checked_out]" value="<?php echo isset( $this->item->checked_out ) ? $this->item->checked_out : ''; ?>" />
			<input type="hidden" name="jform[checked_out_time]" value="<?php echo isset( $this->item->checked_out_time ) ? $this->item->checked_out_time : ''; ?>" />
			<input type="hidden" name="jform[created_date]" value="<?php echo isset( $this->item->created_date ) ? $this->item->created_date : ''; ?>" />
			
			<?php echo HTMLHelper::_( 'uitab.startTabSet', 'myTab', array( 'active' => 'video', 'breakpoint' => '0' ) ); ?>
				
			<?php echo HTMLHelper::_( 'uitab.addTab', 'myTab', 'video', Text::_( 'COM_YENDIFVIDEOSHARE_TAB_VIDEO', true ) ); ?>
				<div class="yendif-control-group yendif-control-group-title">
					<?php echo $this->form->renderField( 'title' ); ?>
				</div>

				<div class="yendif-control-group yendif-control-group-catid">
					<?php echo $this->form->renderField( 'catid' ); ?>
				</div>
				
				<div class="yendif-control-group yendif-control-group-type">
					<?php
					$options = array(
						'video' => Text::_( 'COM_YENDIFVIDEOSHARE_FORM_OPTION_VIDEO_TYPE_VIDEO' )
					);

					if ( $this->params->get( 'allow_youtube' ) ) {
						$options['youtube'] = Text::_( 'COM_YENDIFVIDEOSHARE_FORM_OPTION_VIDEO_TYPE_YOUTUBE' );
					}

					if ( $this->params->get( 'allow_vimeo' ) ) {
						$options['vimeo'] = Text::_( 'COM_YENDIFVIDEOSHARE_FORM_OPTION_VIDEO_TYPE_VIMEO' );
					}

					if ( $this->params->get( 'allow_hls_dash' ) ) {
						$options['rtmp'] = Text::_( 'COM_YENDIFVIDEOSHARE_FORM_OPTION_VIDEO_TYPE_RTMP' );
					}
					?>
					<div class="control-group"<?php if ( count( $options ) == 1 ) { echo ' style="display: none;"'; } ?>>
						<div class="control-label">
							<label id="jform_type-lbl" for="jform_type"><?php echo Text::_( 'COM_YENDIFVIDEOSHARE_FORM_LBL_VIDEO_TYPE' ); ?></label>
						</div>
						<div class="controls">
							<select id="jform_type" name="jform[type]" class="form-select">
								<?php
								foreach ( $options as $value => $text ) {								
									$selected = isset( $this->item->type ) ? $this->item->type : 'video';

									printf(
										'<option value="%s"%s>%s</option>',
										$value,
										( $value == $selected ? ' selected="selected"' : '' ),
										$text
									);
								}
								?>
							</select>
						</div>
					</div>
				</div>

				<div class="yendif-control-group yendif-control-group-mp4">
					<?php echo $this->form->renderField( 'mp4' ); ?>
				</div>

				<div class="yendif-control-group yendif-control-group-mp4_hd" style="display: none;">
					<?php echo $this->form->renderField( 'mp4_hd' );	?>
				</div>

				<div class="yendif-control-group yendif-control-group-webm"<?php if ( empty( $this->item->webm ) ) { echo ' style="display: none;"'; } ?>>
					<?php echo $this->form->renderField( 'webm' ); ?>
				</div>

				<div class="yendif-control-group yendif-control-group-ogv"<?php if ( empty( $this->item->ogv ) ) { echo ' style="display: none;"'; } ?>>
					<?php echo $this->form->renderField( 'ogv' ); ?>
				</div>

				<div class="yendif-control-group yendif-control-group-youtube"<?php if ( ! $this->params->get( 'allow_youtube' ) ) { echo ' style="display: none;"'; } ?>>
					<?php echo $this->form->renderField( 'youtube' ); ?>
				</div>

				<div class="yendif-control-group yendif-control-group-vimeo"<?php if ( ! $this->params->get( 'allow_vimeo' ) ) { echo ' style="display: none;"'; } ?>>
					<?php echo $this->form->renderField( 'vimeo' ); ?>
				</div>

				<div class="yendif-control-group yendif-control-group-hls"<?php if ( ! $this->params->get( 'allow_hls_dash' ) ) { echo ' style="display: none;"'; } ?>>
					<?php echo $this->form->renderField( 'hls' ); ?>
				</div>

				<div class="yendif-control-group yendif-control-group-dash"<?php if ( ! $this->params->get( 'allow_hls_dash' ) ) { echo ' style="display: none;"'; } ?>>
					<?php echo $this->form->renderField( 'dash' ); ?>
				</div>

				<div class="yendif-control-group yendif-control-group-image">
					<?php echo $this->form->renderField( 'image' ); ?>
				</div>

				<div class="yendif-control-group yendif-control-group-captions"<?php if ( ! $this->params->get( 'allow_subtitle' ) ) { echo ' style="display: none;"'; } ?>>
					<?php echo $this->form->renderField( 'captions' ); ?>
				</div>

				<div class="yendif-control-group yendif-control-group-duration">
					<?php echo $this->form->renderField( 'duration' ); ?>
				</div>

				<div class="yendif-control-group yendif-control-group-description">
					<?php echo $this->form->renderField( 'description' ); ?>
				</div>

				<div class="yendif-control-group yendif-control-group-state">
					<div class="control-group">
						<?php if ( ! $canState ) : ?>
							<div class="control-label"><?php echo $this->form->getLabel( 'state' ); ?></div>
							<div class="controls">
								<?php
								$state = isset( $this->item->state ) ? $this->item->state : 0;

								switch ( $state ) {
									case 1:
										echo Text::_( 'JPUBLISHED' );
										break;
									case 0:
										echo Text::_( 'JUNPUBLISHED' );
										break;
								}
								?>
							</div>
							<input type="hidden" name="jform[state]" value="<?php echo $state; ?>" />
						<?php else : ?>
							<div class="control-label"><?php echo $this->form->getLabel( 'state' ); ?></div>
							<div class="controls"><?php echo $this->form->getInput( 'state' ); ?></div>
						<?php endif; ?>
					</div>
				</div>

				<?php if ( $canState && $this->params->get( 'schedule_video_publishing' ) ) : ?>
					<div class="yendif-control-group yendif-control-group-published_up">
						<?php echo $this->form->renderField( 'published_up' ); ?>
					</div>

					<div class="yendif-control-group yendif-control-group-published_down">
						<?php echo $this->form->renderField( 'published_down' ); ?>
					</div>
				<?php else : ?>
					<input type="hidden" name="jform[published_up]" value="<?php echo isset( $this->item->published_up ) ? $this->item->published_up : ''; ?>" />
					<input type="hidden" name="jform[published_down]" value="<?php echo isset( $this->item->published_down ) ? $this->item->published_down : ''; ?>" />
				<?php endif; ?>
			<?php echo HTMLHelper::_( 'uitab.endTab' ); ?>

			<?php echo HTMLHelper::_( 'uitab.addTab', 'myTab', 'Advanced', Text::_( 'COM_YENDIFVIDEOSHARE_TAB_ADVANCED', true ) ); ?>
				<div class="yendif-control-group yendif-control-group-meta_keywords">
					<?php echo $this->form->renderField( 'meta_keywords' ); ?>
				</div>

				<div class="yendif-control-group yendif-control-group-meta_description">
					<?php echo $this->form->renderField( 'meta_description' ); ?>
				</div>
			<?php echo HTMLHelper::_( 'uitab.endTab' ); ?>

			<div class="control-group">
				<div class="controls">
					<?php if ( $this->canSave ) : ?>
						<button type="submit" class="validate btn btn-primary">
							<span class="fas fa-check" aria-hidden="true"></span>
							<?php echo Text::_( 'JSUBMIT' ); ?>
						</button>
					<?php endif; ?>
					<a class="btn btn-danger" href="<?php echo Route::_( 'index.php?option=com_yendifvideoshare&task=videoform.cancel' ); ?>" title="<?php echo Text::_( 'JCANCEL' ); ?>">
					   <span class="fas fa-times" aria-hidden="true"></span>
						<?php echo Text::_( 'JCANCEL' ); ?>
					</a>
				</div>
			</div>

			<input type="hidden" name="option" value="com_yendifvideoshare" />
			<input type="hidden" name="task" value="videoform.save" />
			<?php echo HTMLHelper::_( 'form.token' ); ?>
		</form>
	<?php endif; ?>
</div>
