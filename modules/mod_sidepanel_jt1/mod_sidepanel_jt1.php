<?php
 /**
 * @package SidePanel Module for Joomla 3.0 By Joomlatema.net
 * @version $Id: mod_SidePanel_JT1.php  2012-07-07  Joomlatema.Net $
 * @author Muratyil
 * @copyright (C) 2010- Muratyil
 * @license GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
**/
defined( '_JEXEC' ) or die( 'Restricted access' );
require_once dirname(__FILE__) . '/helper.php';
jimport('joomla.document.html.renderer.module');
$moduleId = $module->id;
//
$heading= $params->get( 'heading' );
$module_file =$params->get( 'group' )  == 'file';
$htmlcontent =$params->get( 'login' );
//
$document = JFactory::getDocument();
$document->addScript('modules/mod_sidepanel_jt1/js/sidepanel.js');
//
$mod_attrs = array('style' => 'xhtml');
//
$includeModules = modSidePanelHelper::getModulesEnable($params);
$itemCount = count($includeModules);
$lastItem = count($includeModules) - 1;
//
$document->addStylesheet(JURI::base(true) . '/modules/mod_sidepanel_jt1/css/stylecss.php?id=' .$moduleId);
//
//styling
$panel_width= $params->get( 'panel_width' );
$panel_height =$params->get( 'panel_height' );
$background = $params->get( 'background' );
$border_color = $params->get( 'border_color' );
$border_width = $params->get( 'border_width' );
$topposition = $params->get( 'topposition' );
$leftposition = $params->get( 'leftposition' );
$panel_position= $params->get( 'panel_position');
$fixedposition= $params->get( 'fixedposition' );
$css_top=$params->get('panel_position') == 'top';
$css_right=$params->get('panel_position') == 'right';
$css_left=$params->get('panel_position') == 'left';
$css_bottom=$params->get('panel_position') == 'bottom';
$speed= $params->get( 'speed' );
$onloadslideout= $params->get( 'onloadslideout' );
$panel_open=$params->get( 'panel_open' );
$panel_close=$params->get( 'panel_close' );
$TabText=$params->get( 'TabText' );
//
$document->addStylesheet(JURI::base(true) . '/modules/mod_sidepanel_jt1/css/font-awesome.min.css');


if ( $css_top) {
$document->addStylesheet(JURI::base(true) . '/modules/mod_sidepanel_jt1/css/topcss.php?id=' .$moduleId);
}
elseif ($css_right)  {
$document->addStylesheet(JURI::base(true) . '/modules/mod_sidepanel_jt1/css/rightcss.php?id=' .$moduleId);
}
elseif ($css_left)  {
$document->addStylesheet(JURI::base(true) . '/modules/mod_sidepanel_jt1/css/leftcss.php?id=' .$moduleId);
}

elseif ($css_bottom)  {
$document->addStylesheet(JURI::base(true) . '/modules/mod_sidepanel_jt1/css/bottomcss.php?id=' .$moduleId);
}
?>
<script type="text/javascript">
jQuery(document).ready(function(){
	jQuery('#sidepanel_jt<?php echo $module->id; ?>').tabSlideOut({
    tabHandle: '.handle<?php echo $module->id; ?>',                            
	otherOffset: null, // if set, panel size is also set to maintain this dist from bottom or right of view port (top or left if offsetReverse)
    handleOffset: null, // e.g. '10px'. If null, detects panel border to align handle nicely on edge
    handleOffsetReverse: false, // if true, handle is offset from right or bottom of panel instead of left or top
    tabLocation: '<?php echo $panel_position;?>',                                                                 
    action: '<?php echo $params->get( 'action');?>',   // action which will open the panel, e.g. 'hover'    
	hoverTimeout: '<?php echo $params->get( 'hoverTimeout');?>', // ms to keep tab open after no longer hovered - only if action = hover
	offsetReverse:<?php echo $params->get( 'offsetReverse');?>, // if true, panel is offset from  right or bottom of window instead of left or top 
	offset: '<?php echo $params->get( 'offset');?>', // panel dist from top or left (bottom or right if offsetReverse is true)
	onLoadSlideOut: <?php echo $onloadslideout;?>,
	clickScreenToClose: <?php echo $params->get( 'clickScreenToClose');?>, // close tab when somewhere outside the tab is clicked            
    fixedPosition: true                          
	});
});
</script>
<style type="text/css">
#sidepanel_jt<?php echo $module->id; ?> {
width:<?php echo $panel_width;?>;
}
#sidepanel_jt<?php echo $moduleId ?>.ui-slideouttab-ready .handle<?php echo $moduleId ?>.imagebg.ui-slideouttab-handle {  
background-image:url(<?php echo JURI::root().'modules/mod_sidepanel_jt1/images/'; ?><?php echo $panel_open; ?>)!important;background-repeat:no-repeat!important;background-position:0 bottom;}

#sidepanel_jt<?php echo $moduleId ?>.ui-slideouttab-open .handle<?php echo $moduleId ?>.imagebg.ui-slideouttab-handle {
background-image:url(<?php echo JURI::root().'modules/mod_sidepanel_jt1/images/'; ?><?php echo $panel_close ?>)!important;background-repeat:no-repeat!important;background-position:0 bottom;}

#sidepanel_jt<?php echo $moduleId ?>.ui-slideouttab-ready .handle<?php echo $moduleId ?>.ui-slideouttab-handle {
background-image:none!important;}
#sidepanel_jt<?php echo $moduleId ?>.ui-slideouttab-open .handle<?php echo $moduleId ?>.ui-slideouttab-handle {
background-image:none!important;
}
#sidepanel_jt<?php echo $moduleId ?> .content { height:<?php echo $panel_height ;?>!important;}
#sidepanel_jt<?php echo $moduleId ?> .ui-slideouttab-handle {background-color: <?php echo $params->get( 'TabBg' );?>;}
.sidepanel-styling<?php echo $moduleId ?> {padding: <?php echo $params->get( 'padding' );?>;}
</style>
<?php
  require(JModuleHelper::getLayoutPath('mod_sidepanel_jt1', $params->get( 'layout', '' )))
  ;?>

