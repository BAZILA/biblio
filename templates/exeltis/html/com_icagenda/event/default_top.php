<?php
/**
 *----------------------------------------------------------------------------
 * iCagenda     Events Management Extension for Joomla!
 *----------------------------------------------------------------------------
 * @version     3.7.12 2020-03-25
 *
 * @package     iCagenda.Site
 * @link        https://icagenda.joomlic.com
 *
 * @author      Cyril Rezé
 * @copyright   (c) 2012-2020 Jooml!C / Cyril Rezé. All rights reserved.
 * @license     GNU General Public License version 3 or later; see LICENSE.txt
 *
 * @since       3.6.0
 *----------------------------------------------------------------------------
*/

defined('_JEXEC') or die;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Factory;

$app        = Factory::getApplication();
$isSef      = $app->getCfg('sef');
$item       = $this->item;
$iCicons    = new iCicons();

$uri        = Uri::getInstance()->toString();
$evt_id     = $app->input->getInt('id', 0);
$event_link = Route::_('index.php?option=com_icagenda&view=event&id=' . $evt_id);

$print_url  = ($isSef == 1) ? $event_link . '?tmpl=component' : $event_link . '&tmpl=component';
$ical_url   = ($isSef == 1) ? $uri . '?vcal=1' : $uri . '&vcal=1';
$ical_url   = preg_replace('/\?date=[^\?]*/', '', $ical_url);
$ical_url   = preg_replace('/&date=[^&]*/', '', $ical_url);
?>

<?php // Top Buttons ?>
<div class="ic-top-buttons">

<?php if ($app->input->get('tmpl') != 'component') : ?>

	<?php // Back button ?>
	<?php echo icagendaEvent::backArrow($this->item); ?>

	<div class="ic-buttons ic-clearfix">

		<?php // Print icon ?>
		<?php if ($this->iconPrint_global == 2) : ?>
		<div class="ic-icon">
			<?php echo $iCicons->showIcon('printpreview', $print_url); ?>
		</div>
		<?php endif; ?>

		<?php // Add to Cal icon ?>
		<?php if ($this->iconAddToCal_global == 2) :  ?>
		<div class="ic-icon">
			<?php echo $iCicons->showIcon('vcal', $uri, $ical_url, $item->googleCalendar, $item->windowsliveCalendar, $item->yahooCalendar); ?>
		</div>
		<?php endif; ?>

		<?php // Manager Icons ?>
		<div class="ic-icon">
			<?php echo $item->managerToolBar; ?>
		</div>

	</div>

<?php else : ?>

	<?php // Print Icon ?>
	<div class="ic-printpopup-btn">
		<?php echo $iCicons->showIcon('print'); ?>
	</div>

<?php endif; ?>

</div>
<div class="ic-clearfix">&nbsp;</div>
