<?php 
/**
 * @package Mansethaber JT2 Module for Joomla 2.5 By Joomlatema.net
 * @version $Id: mod_mansethaber_JT2.php  2013-07-30  Joomlatema.Net $
 * @author Muratyil
 * @copyright (C) 2013- Muratyil
 * @license GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
**/
// no direct access
defined('_JEXEC') or die('Restricted access');
?>
<div id="sidepanel_jt<?php echo $module->id; ?>" style="line-height: 1; height: auto; z-index:9999;  background:<?php echo $background;?>;" >
<div class="sidepanel-styling<?php echo $module->id; ?>" style="border:<?php echo $border_width;?>px solid <?php echo $border_color;?>;">
<?php if ($params->get( 'TabStyle' )=='TextandIcon'){?>
	 <a  style="display: block;  outline: medium none; position: absolute; " class="handle<?php echo $module->id; ?>" href="javascript:void(0)"><?php echo $TabText;?><i class="<?php echo $params->get( 'TabIcon');?>" aria-hidden="true"></i>
</a>
<?php } else { ?>
<a  style="display: block;  outline: medium none; position: absolute; " class="handle<?php echo $module->id; ?> imagebg" href="javascript:void(0)"></a>
<?php } ?>
<?php if ($heading!=null):?><h2 class="title"><?php echo $heading;?></h2><?php endif; ?>
	  <div class="content"><?php if ($module_file) { ?>
<div id="modpos" class="module-nostyle">
<?php
						foreach ($includeModules as $k => $moduleObj) :
							if ($k == 0) {
								$extClass = 'first_block';
							} elseif ($k == $lastItem) {
								$extClass = 'last_block';
							} else {
								$extClass = '';
							}
							?>
							<div style="clear:both; margin:10px 0;" class="sidepanel_block <?php echo $extClass; ?>" >
								<?php
								$mod = JModuleHelper::getModule($moduleObj->module, $moduleObj->title);
								echo JModuleHelper::renderModule($mod, $mod_attrs);
								?>
							</div>
							<?php endforeach; ?></div>
<?php } else { ?>
   <div id="htmlcontent"><?php echo $htmlcontent; ?></div>
<?php } ?>
</div>
</div>
</div>

