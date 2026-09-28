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
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;

HTMLHelper::addIncludePath( JPATH_COMPONENT . '/helpers/html' );
HTMLHelper::_( 'bootstrap.tooltip' );

$wa = $this->document->getWebAssetManager();
$wa->useScript( 'keepalive' )
	->useScript( 'form.validate' )
	->useScript( 'com_yendifvideoshare.admin' );
?>

<form action="<?php echo Route::_( 'index.php?option=com_yendifvideoshare&layout=edit&id=' . (int) $this->item->id ); ?>" method="post" enctype="multipart/form-data" name="adminForm" id="advert-form" class="form-validate form-horizontal">
	<?php if ( ! $this->canDo ) : ?>
		<div class="alert alert-warning">
			<?php echo Text::_( 'COM_YENDIFVIDEOSHARE_PREMIUM_DESC_ADVERTISEMENTS' ); ?>
		</div>
	<?php else : ?>
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
								<?php echo $this->form->renderField( 'mp4' ); ?>
								<?php echo $this->form->renderField( 'link' ); ?>						
							</fieldset>
						</div>
					</div>

					<div class="col-lg-4">
						<fieldset class="form-vertical">
							<legend class="visually-hidden"><?php echo Text::_( 'COM_YENDIFVIDEOSHARE_FIELDSET_GENERAL' ); ?></legend>
							<?php echo $this->form->renderField( 'impressions' ); ?>
							<?php echo $this->form->renderField( 'clicks' ); ?>
							<?php echo $this->form->renderField( 'state' ); ?>
						</fieldset>
					</div>
				</div>
			<?php echo HTMLHelper::_( 'uitab.endTab' ); ?>

			<input type="hidden" name="jform[id]" value="<?php echo $this->item->id; ?>" />
			<input type="hidden" name="jform[state]" value="<?php echo $this->item->state; ?>" />
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
