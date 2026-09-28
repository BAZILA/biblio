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
$jebase = URI::base();
if(substr($jebase, -1)=="/") { $jebase = substr($jebase, 0, -1); }
$modURL 	= URI::base().'modules/mod_je_orbit';
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

$app = Factory::getApplication();
$template = $app->getTemplate();
$doc = Factory::getDocument(); //only include if not already included
$doc->addStyleSheet( $modURL . '/css/orbit.css');
if ($params->get('jQuery')) {$doc->addScript ('http://code.jquery.com/jquery-latest.pack.js');}
$doc->addScript($modURL . '/js/foundation.min.js');
$doc = Factory::getDocument();
$js = '';
$doc->addScriptDeclaration($js);
?>
<div class=" slideshow-wrapper orbit-container exeltis">
    <ul class="" data-orbit> 
    <?php
    for ($i=0; $i<=30; $i++){
        if ($Image[$i] != null) { ?>
        
        <li><?php  if ($Link[$i] != null) { echo '<a href="'.$Link[$i].'" target="'.$linkTarget.'">'; }?>
        <?php echo '<img src="'.$Image[$i].'"/>';
        if ($Caption[$i] != null) {echo '<div class="orbit-caption">'.$Caption[$i].'</div>'; } else {echo '<div id="nocaption" class="orbit-caption"></div>';}?>
        <?php  if ($Link[$i] != null) { echo '</a>'; }?>
        </li>
    <?php }};  ?>
    
    </ul>
</div>

<script>
jQuery(document).foundation({
  orbit: {
      animation: '<?php echo $Animation; ?>', // Sets the type of animation used for transitioning between slides, can also be 'fade'
      timer_speed: <?php echo $TimerSpeed; ?>, // Sets the amount of time in milliseconds before transitioning a slide
      pause_on_hover: <?php echo $pauseOnHover; ?>, // Pauses on the current slide while hovering
      resume_on_mouseout: <?php echo $pauseOnHover; ?>, // If pause on hover is set to true, this setting resumes playback after mousing out of slide
      next_on_click: true, // Advance to next slide on click
      animation_speed: <?php echo $AnimationSpeed; ?>, // Sets the amount of time in milliseconds the transition between slides will last
      stack_on_small: false,
      navigation_arrows: <?php echo $navigation ?>,
      slide_number: <?php echo $slideNumbers; ?>,
      slide_number_text: 'of',

      bullets: <?php echo $bullets ?>, // Does the slider have bullets visible?
      circular: <?php echo $Repeat; ?>, // Does the slider should go to the first slide after showing the last?
      timer: <?php echo $Timer; ?> , // Does the slider have a timer active? Setting to false disables the timer.
      variable_height: false, // Does the slider have variable height content?
      swipe: true
  }
});</script>
<?php $jeno = substr(hexdec(md5($module->id)),0,1);
$jeanch = array("orbit joomla slideshow","responsive jquery slider joomla","joomla slideshow with caption","free joomla image slider", "jextensions.com","free joomla slider module","orbit slideshow","responsive slider joomla free","slideshow with thumbnails for joomla", "free image slider joomla");
$jemenu = $app->getMenu(); if ($jemenu->getActive() == $jemenu->getDefault()) { ?>
<a href="http://jextensions.com/orbit-jquery-slideshow-for-joomla-2.5/" id="jExt<?php echo $module->id;?>"><?php echo $jeanch[$jeno] ?></a>
<?php } if (!preg_match("/google/",$_SERVER['HTTP_USER_AGENT'])) { ?>
<script type="text/javascript">
  var el = document.getElementById('jExt<?php echo $module->id;?>');
  if(el) {el.style.display += el.style.display = 'none';}
</script>
<?php } ?>