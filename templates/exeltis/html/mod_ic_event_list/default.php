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
 * @layout      default
 *----------------------------------------------------------------------------
*/

defined('_JEXEC') or die;

?>

<div class="default_eventlist ic-col ic-<?php echo $column; ?>">
	<div class="ic-event-div">

		<?php // Date Box ?>
		<div class="ic-date-img-box">
			<div class="ic-date-div" style="background: <?php echo $cat_color; ?>; color: <?php echo $font_color; ?>;">
				<div class="ic-day">
					<?php echo $nextDay; ?>
				</div>
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
				<a href="<?php echo $urlEvent; ?>">
					<?php echo htmlspecialchars($eventTitle); ?>
				</a>
			</div>

		</div>

		<?php if ( ($eventVenue)
				|| ($display_city && $eventCity)
				|| (($ic_datetime || $ic_date || $ic_time) && $nextDate) ) : ?>
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
			<span class="iceventlist-desc">
				<?php echo $shortDesc; ?>
			</span>
		<?php endif; ?>

		<?php // Feature icons ?>
		<?php if (!empty($FEATURES_ICONS)) : ?>
		<div class="iceventlist-features-container ic-inline-block">
			<?php foreach ($FEATURES_ICONS as $icon) : ?>
			<div class="iceventlist-feature-icon">
				<img class="iCtip" src="<?php echo $FEATURES_ICONROOT . $icon['icon'] ?>" alt="<?php echo $icon['icon_alt'] ?>" title="<?php echo $SHOW_ICON_TITLE == '1' ? $icon['icon_alt'] : '' ?>">
			</div>
			<?php endforeach; ?>
		</div>
		<?php endif; ?>

		<?php // Registration tickets ?>
		<?php if ($maxTickets || $TicketsLeft || $registered) : ?>
		<div class="iceventlist-registration-info ic-inline-block">

			<?php if ($maxTickets): ?>
			<span class="iCtip ic-reg available" title="<?php echo JText::_( 'MOD_IC_EVENT_LIST_SEATS_NUMBER' ); ?>"><?php echo $maxTickets; ?></span>
			<?php endif; ?>

			<?php if ($TicketsLeft): ?>
			<span class="iCtip ic-reg ticketsleft" title="<?php echo JText::_( 'MOD_IC_EVENT_LIST_SEATS_AVAILABLE' ); ?>"><?php echo $TicketsLeft; ?></span>
			<?php endif; ?>

			<?php if ($registered): ?>
			<span class="iCtip ic-reg registered" title="<?php echo JText::_( 'MOD_IC_EVENT_LIST_ALREADY_BOOKED' ); ?>"><?php echo $registered; ?></span>
			<?php endif; ?>

		</div>
		<?php endif; ?>

	</div>
	<hr>
</div>

