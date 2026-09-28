<?php
/**
 * @package		Joomla.Site
 * @subpackage	mod_je_orbit
 * @copyright	Copyright (C) 2012-2015 jExtensions.com - All rights reserved.
 * @license		GNU General Public License version 2 or later
 */
//no direct access
defined('_JEXEC') or die('Direct Access to this location is not allowed.');
// Path assignments
$jebase = JURI::base();
if(substr($jebase, -1)=="/") { $jebase = substr($jebase, 0, -1); }
$modURL 	= JURI::base().'modules/mod_je_orbit';
// get parameters from the module's configuration
$jQuery = $params->get("jQuery");
$Animation = $params->get('Animation','slide');
$TimerSpeed = $params->get('TimerSpeed','3000');
$AnimationSpeed = $params->get('AnimationSpeed','300');
$Timer = $params->get('Timer','true');
$Repeat = $params->get('Repeat','true');
$pauseOnHover = $params->get('pauseOnHover','true');
$resumeNotHover = $params->get('resumeNotHover','true');
$slideNumbers = $params->get('slideNumbers','true');
$navigation = $params->get('navigation','true');
$bullets = $params->get('bullets','true');
$linkTarget = $params->get('linkTarget','_self');

$Image[]= $params->get( '!', "" );
$Caption[]= $params->get( '!', "" );
$Link[]= $params->get( '!', "" );


for ($j=1; $j<=30; $j++)
	{
	$Image[]		= $params->get( 'Image'.$j , "" );
	$Caption[]		= $params->get( 'Caption'.$j , "" );
	$Link[]	= $params->get( 'Link'.$j , "" );	
}

$app = JFactory::getApplication();
$template = $app->getTemplate();
$doc = JFactory::getDocument(); //only include if not already included
$doc->addStyleSheet( $modURL . '/css/orbit.css');
if ($params->get('jQuery')) {$doc->addScript ('http://code.jquery.com/jquery-latest.pack.js');}
$doc->addScript($modURL . '/js/foundation.min.js');
$doc = JFactory::getDocument();
$js = '';
$doc->addScriptDeclaration($js);
?>

<?php
require(JModuleHelper::getLayoutPath('mod_je_orbit'));
 ?>
 
