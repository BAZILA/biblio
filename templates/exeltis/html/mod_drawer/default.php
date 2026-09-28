<?php

	#@license - http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL

	defined('_JEXEC') or die;
use Joomla\CMS\Factory;
	// get the button parameters and whether we're showing an article or a module
	$lebutton = '';
	$modorart = $params->get('modOrArt');
?>

<div id="theDrawer"  class="exeltis">
<div id="theDrawer-inner"  class="container">
	<?php

		// 0 is if it's a module
		if($modorart == 0){

			jimport( 'joomla.application.module.helper' );

			// we get the module renderer
			$document = Factory::getDocument();
			$renderer = $document->loadRenderer('module');

			// make a ul for them to all go in
			echo '<ul class="modulelist">';
    		foreach($mods as $moddy){
				// render out the modules

    			// just to get rid of that stupid php warning
    			$moddy->user = '';
    			$parameters = array('style'=>'xhtml');
    			echo '<li class="drawerMod">'.$renderer->render($moddy, $parameters).'</li>';
    		}
  			echo '</ul>';

		}

		// if it's an article
		if($modorart == 1){

			// get the parameters for the image and generate a url
			$itemID = $arts['id'];
			$url = 'index.php?option=com_content&view=article&id='.$itemID;
			$title = $arts['title'];
			$intro = $arts['introtext'];
			$full = $arts['fulltext'];

			// spit it all into an element
			?><div id="drawerContent"><?php
			echo '<h2>'.$title.'</h2>';
			echo $intro;
			if($full != ''){
				echo "<a href='.$url.'>Read More</a>";
			}
			?></div><?php
		}
	?>

</div></div>
<div id="theButton" class="exeltis"><a href="#"><svg aria-hidden="true" class="s-input-icon s-input-icon__search svg-icon iconSearch" width="18" height="18" viewBox="0 0 18 18"><path d="M18 16.5l-5.14-5.18h-.35a7 7 0 10-1.19 1.19v.35L16.5 18l1.5-1.5zM12 7A5 5 0 112 7a5 5 0 0110 0z"></path></svg></a></div>
<script> 
jQuery(document).ready(function(){
  jQuery("#theButton.exeltis").click(function(){
  if (jQuery('#theButton.exeltis').hasClass('clicked')) {
        jQuery(this).removeClass('clicked');
        jQuery(this).addClass('unclicked');
    } else {
        jQuery(this).removeClass('unclicked');
        jQuery(this).addClass('clicked');
    }
  
    jQuery("#theDrawer").slideToggle("slow");
	
	
  });
});




</script>
<style> 
#theDrawer {
  display: none;
  float:left;
  width:97%;
}
</style>



<?php
