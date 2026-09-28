<?php
/**
 *----------------------------------------------------------------------------
 * iCagenda     Events Management Extension for Joomla!
 *----------------------------------------------------------------------------
 * @version     3.14.8 2020-03-25
 *
 * @package     iCagenda.Site
 * @subpackage  mod_ic_event_list
 * @link        https://icagenda.joomlic.com
 *
 * @author      Cyril Rezé
 * @copyright   (c) 2012-2020 Jooml!C / Cyril Rezé. All rights reserved.
 * @license     GNU General Public License version 3 or later; see LICENSE.txt
 *
 * @since       1.0
 *----------------------------------------------------------------------------
 * @layout      icrounded
 *----------------------------------------------------------------------------
*/

defined('_JEXEC') or die;

$background = $EVENT_IMAGE
	? 'background: url(' . $thumb_img . ') no-repeat center center; color: white;'
	: 'background: ' . $cat_color . '; color: ' . $font_color . ';';
?>

<div class="icrounded_eventlist exeltis ic-col ic-<?php echo $column; ?>">
	<a href="<?php echo $urlEvent; ?>">
	<div class="ic-event-div">
		<!--[if lte IE 9]>
			<a href="<?php echo $urlEvent; ?>"><span>
		<![endif]-->

		<?php // Date Box ?>
		<div class="ic-date-img-box" style="<?php echo $background; ?> width: <?php echo $thumb_xsmall_width; ?>; height: <?php echo $thumb_xsmall_height; ?>;">
			<div class="ic-date-div" style="width: <?php echo $thumb_xsmall_width; ?>;">

				<?php // Day ?>
				<div class="ic-day">
					<?php echo $nextDay; ?>
				</div>

				<?php // Month ?>
				<div class="ic-month">
					<?php echo $nextMonth; ?>
				</div>

			</div>
		</div>

		<div class="ic-block">

			<?php // If Event is cancelled ?>
			<?php if ($eventIsCancelled) : ?>
			<div class="iceventlist-title ic-block">
				<?php echo $eventIsCancelled; ?>
			</div>
			<?php endif; ?>

			<?php // Title ?>
			<div class="iceventlist-title ic-block">
				<!--[if lte IE 9]>
					<a href="<?php echo $urlEvent; ?>">
				<![endif]-->
				<?php echo htmlspecialchars($eventTitle); ?>
				<!--[if lte IE 9]>
					</a>
				<![endif]-->
			</div>

			<?php // Registration tickets ?>
			<?php if ($maxTickets || $TicketsLeft || $registered) : ?>
			<div class="iceventlist-registration-info ic-inline-block">

				<?php if ($maxTickets): ?>
				<span class="iCtip ic-reg available" title="<?php echo JText::_( 'MOD_IC_EVENT_LIST_SEATS_NUMBER' ); ?>">
					<?php echo $maxTickets; ?>
				</span>
				<?php endif; ?>

				<?php if ($TicketsLeft): ?>
				<span class="iCtip ic-reg ticketsleft" title="<?php echo JText::_( 'MOD_IC_EVENT_LIST_SEATS_AVAILABLE' ); ?>">
					<?php echo $TicketsLeft; ?>
				</span>
				<?php endif; ?>

				<?php if ($registered): ?>
				<span class="iCtip ic-reg registered" title="<?php echo JText::_( 'MOD_IC_EVENT_LIST_ALREADY_BOOKED' ); ?>">
					<?php echo $registered; ?>
				</span>
				<?php endif; ?>

			</div>
			<?php endif; ?>

			<?php // Feature icons ?>
			<?php if (!empty($FEATURES_ICONS)) : ?>
			<div class="iceventlist-features-container">
				<?php foreach ($FEATURES_ICONS as $icon) : ?>
				<div class="iceventlist-feature-icon">
					<img class="iCtip" src="<?php echo $FEATURES_ICONROOT . $icon['icon'] ?>" alt="<?php echo $icon['icon_alt'] ?>" title="<?php echo $SHOW_ICON_TITLE == '1' ? $icon['icon_alt'] : '' ?>">
				</div>
				<?php endforeach; ?>
			</div>
			<?php endif; ?>

		</div>

		<?php if ( ($eventVenue)
				|| ($display_city && $eventCity)
				|| (($ic_datetime || $ic_date || $ic_time) && $EVENT_DATETIME) ) : ?>
		<div class="ic-block">

			<?php // Venue ?>
			<?php if ($eventVenue): ?>
			<div class="iceventlist-venue ic-inline-block">
				<?php echo htmlspecialchars($eventVenue); ?>
			</div>
			<?php endif; ?>

			<?php // City ?>
			<?php if ($display_city && $eventCity): ?>
			<?php if ( $eventVenue ): ?>-<?php endif; ?>
			<div class="iceventlist-city ic-inline-block">
				<?php echo htmlspecialchars($eventCity); ?>
			</div>
			<?php endif; ?>

			<?php // Date (& time) ?>
			<?php if (($ic_datetime || $ic_date || $ic_time) && $nextDate): ?>
			<?php $block_class = ($eventVenue || ($display_city && $eventCity)) ? ' ic-inline-block' : ' ic-block'; ?>
			<div class="ic-datetime-div<?php echo $block_class; ?>">
				<?php if ($ic_datetime): ?>
					<?php echo '<span class="ic-datetime-label">' . JText::_( 'MOD_IC_EVENT_LIST_DATE' ). '</span>'; ?>
					<?php echo $eventDate; ?><?php if ($displayTime) : ?>, <?php echo $nextTime; ?><?php endif; ?>
				<?php elseif ($ic_date): ?>
					<?php //echo JText::_( 'MOD_IC_EVENT_LIST_AT_TIME' ); ?>
					<?php echo '<span class="ic-datetime-label">' . JText::_( 'MOD_IC_EVENT_LIST_DATE' ). '</span>'; ?>
					<?php echo $eventDate; ?>
				<?php elseif ($ic_time): ?>
					<?php //echo JText::_( 'MOD_IC_EVENT_LIST_AT_TIME' ); ?>
					<?php echo '<span class="ic-datetime-label">' . JText::_( 'MOD_IC_EVENT_LIST_TIME' ). '</span>'; ?>
					<?php echo $nextTime; ?>
				<?php endif; ?>
			</div>
			<?php endif; ?>
		</div>
		<?php endif; ?>

		<?php // Short Description ?>
		<?php if ($display_shortDesc && $shortDesc): ?>
		<div class="ic-block">
			<div class="iceventlist-desc ic-inline-block">
				<?php echo $shortDesc; ?>
			</div>
		</div>
		<?php endif; ?>
		<!--[if lte IE 9]>
			</span></a>
		<![endif]-->
	</div>
	</a>
</div>
