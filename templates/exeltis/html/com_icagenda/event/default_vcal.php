<?php
/**
 *------------------------------------------------------------------------------
 *  iCagenda v3 by Jooml!C - Events Management Extension for Joomla! 2.5 / 3.x
 *------------------------------------------------------------------------------
 * @package     com_icagenda
 * @copyright   Copyright (c)2012-2019 Cyril Rezé, Jooml!C - All rights reserved
 *
 * @license     GNU General Public License version 3 or later; see LICENSE.txt
 * @author      Tom-Henning (MaW) / Cyril Rezé (Lyr!C)
 * @link        http://www.joomlic.com
 *
 * @version     3.6.1 2016-08-23
 * @since       3.2.9
 *------------------------------------------------------------------------------
*/

// No direct access to this file
defined('_JEXEC') or die();
use Joomla\CMS\Uri\Uri;

require_once JPATH_LIBRARIES . '/ic_library/iCalcreator/iCalcreator.class.php';

$app			= Factory::getApplication();

// Load Joomla config options
$sitename		= $app->getCfg('sitename');
$offset			= $app->getCfg('offset');

// Shortcut for item
$item			= $this->item;

// Get date from the session (current date of the event page)
$session		= Factory::getSession();
$session_date	= $session->get('session_date');

if ($session_date)
{
	$eventDateTime	= iCDate::isDate($session_date) ? $session_date : '';
}
else
{
	$url_date		= $app->input->get('date', '');

	if ($url_date)
	{
		$sd_ex			= explode('-', $url_date);
		$eventDateTime	= $sd_ex['0'] . '-' . $sd_ex['1'] . '-' . $sd_ex['2'] . ' ' . $sd_ex['3'] . ':' . $sd_ex['4'] . ':00';
	}
}

//$new_date		= isset($eventDateTime) ? $eventDateTime : 'now';
$new_date		= isset($eventDateTime) ? $eventDateTime : $item->next;

// Set Time Offset for date of the event
$dateTimeZone	= new DateTimeZone($offset);
$dateTime		= new DateTime($new_date, $dateTimeZone);
$timeOffset		= $dateTimeZone->getOffset($dateTime);
$timezone		= ($timeOffset / 3600);

$event_date		= isset($eventDateTime)
				? date('Y-m-d-H-i', (strtotime($eventDateTime) - $timeOffset))
				: date('Y-m-d-H-i', (strtotime($item->next) - $timeOffset));

// Set vCalendar config;
$config = array( "unique_id" => $sitename
  // set a (site) unique id
               , "TZID"      => $offset );
  // opt. "calendar" timezone

// Create a new calendar instance
$v = new vCalendar($config);
$v->setConfig( 'filename', 'icagenda.ics' );
$v->prodid = 'iCagenda';

//$tz = 'UTC';
$tz = $offset;
$v->setProperty( 'method', 'PUBLISH' );
$v->setProperty( 'X-WR-CALDESC', '' );
$v->setProperty( 'X-WR-TIMEZONE', $tz);
$xprops = array( 'X-LIC-LOCATION' => $tz);

if (version_compare(PHP_VERSION, '5.3.0') >= 0)
{
	iCalUtilityFunctions::createTimezone($v, $tz, $xprops);
}

$s_dates		= $item->dates;
$single_dates	= unserialize($s_dates);

$ex			= explode('-', $event_date);
$this_date	= $ex['0'].'-'.$ex['1'].'-'.$ex['2'].' '.$ex['3'].':'.$ex['4'];

$startdate	= date('Y-m-d-H-i', (strtotime($item->startdate) - $timeOffset));
$enddate	= date('Y-m-d-H-i', (strtotime($item->enddate) - $timeOffset));

if ( ($event_date >= $startdate)
	&& ($event_date <= $enddate)
	&& ( ! in_array($this_date, $single_dates)) )
{
	$weekdays	= ($item->weekdays || $item->weekdays == '0') ? true : false;

	if ($weekdays)
	{
		$startdate	= date('Y-m-d-H-i', strtotime($this_date));
		$enddate	= date('Y-m-d', strtotime($this_date)) . '-' . date('H-i', strtotime($item->enddate)-$timeOffset);
	}

	$ex_S = explode('-', $startdate);
	$ex_E = explode('-', $enddate);

	$start_Datetime	= $ex_S['0'] . $ex_S['1'] . $ex_S['2'] . 'T' . $ex_S['3'] . $ex_S['4'] . '00Z';
	$end_Datetime	= $ex_E['0'] . $ex_E['1'] . $ex_E['2'] . 'T' . $ex_E['3'] . $ex_E['4'] . '00Z';
}
else
{
	$start_Datetime = $end_Datetime = $ex['0'] . $ex['1'] . $ex['2'] . 'T' . $ex['3'] . $ex['4'] . '00Z';
}

// Not yet used
$urllink	= Uri::getInstance()->toString();
$cleanurl	= preg_replace('/&tmpl=[^&]*/', '', $urllink);
$cleanurl	= preg_replace('/&vcal=[^&]*/', '', $cleanurl);

$vevent = &$v->newComponent('vevent');
$vevent->setProperty('categories', $item->cat_title);
$vevent->setProperty('summary', $item->title);
$vevent->setProperty('description', strip_tags($item->desc));
$vevent->setProperty('url', $cleanurl);
$vevent->setUID($item->id);

if ($item->contact_name != '')
{
	$vevent->setOrganizer($item->contact_name, $item->contact_email);
}

$vevent->setProperty('dtstart', $start_Datetime);
$vevent->setProperty('dtend', $end_Datetime);

$vevent->setProperty('location',$item->place);

$v->returnCalendar();
