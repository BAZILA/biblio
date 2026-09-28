<?php
/**
 * @package     mod_jttabs
 * @copyright   Copyright (C) 2007 - 2022 http://www.joomlatema.net, Inc. All rights reserved.
 * @license		http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 * @author     	JoomlaTema.Net
 * @link 		http://www.joomlatema.net
 **/

// no direct access
defined('_JEXEC') or die('Restricted access');
error_reporting(E_ALL ^ E_NOTICE  ^ E_DEPRECATED ^ E_WARNING);
$tab_items    = $params->get('tab_items');
$TabTypeMod	  =$params->get('TabType')=='modules';
?>
<style type="text/css">
.fade {-webkit-transition: opacity .15s linear;-o-transition: opacity .15s linear;transition: opacity .15s linear;}
</style><?php if ($tab_items) : ?>
<div class="jttabs-wrapper exeltis layout-default" id="jttabs_<?php echo $module->id; ?>">

    <ul id="JtTabContent" class="nav nav-tabs" role="tablist">
    <?php
    // tab title
    foreach ($tab_items as $idx => $item) : $substridx= str_replace("tab_items", '', $idx); ?>
      <li class="nav-item" role="presentation" >
	  <button id="jttab" class="nav-link <?php echo ($substridx == 0 ? 'active' : '') ?>"    role="tab" type="button"  data-bs-toggle="tab" data-bs-target="#jttab<?php echo $substridx ?>" aria-controls="jttab<?php echo $substridx ?>"><?php echo ($item->tabtitle == '') ? ('Tab '.$substridx+1) : $item->tabtitle ?></button>
	  </li>
    <?php endforeach; ?>
    </ul>
    <div id="JtTabContent"  class="tab-content">	
	 <?php
    // tab content
    foreach  ($tab_items as $idx => $item) : $substridx= str_replace("tab_items", '', $idx);
       $active = ($substridx == 0) ? ' active show' : '';?>
      <?php if( $item->TabType=='modules'){ ?>
          <div id="jttab<?php echo $substridx ?>" class="tab-pane fade<?php echo $active ?>" role="tabpanel">
		  <?php 
jimport('joomla.application.module.helper');
if (!empty($item->id)){
foreach ($item->id as $module){
$modules = JModuleHelper::getModuleById($module);
echo JModuleHelper::renderModule($modules); 
}
};?>
</div>
      <?php }
      else if( $item->TabType=='articles'){ ?>
	  <div id="jttab<?php echo $substridx ?>" class="tab-pane fade<?php echo $active ?>" style="height:<?php echo $item->tabpanelheight; ?>" role="tabpanel">
      
		  <?php echo modJtTabsHelper::getArticle($item->article); ?>
        </div>
  
    <?php }
    endforeach;
    ?>
   </div><!--tab-content-->
</div>
<?php else: ?>
  <span class="text-danger"><?php echo 'No Tabs to Display!' ?></span>
  <?php endif; ?>
  <script>
  jQuery("#contentpanel").on("load", function() {
  let head = jQuery("#contentpanel").contents().find("head");
  let css = '<style> div.phpdebugbar,.pagenavigation{ display:none;}</style>';
  jQuery(head).append(css);
});</script>