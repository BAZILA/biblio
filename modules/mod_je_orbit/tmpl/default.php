<?php


// no direct access
defined('_JEXEC') or die;

?>
<div class=" slideshow-wrapper orbit-container">
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