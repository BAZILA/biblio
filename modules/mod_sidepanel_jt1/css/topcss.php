<?php
 /**
 * @package SidePanel Module for Joomla 3.0 By Joomlatema.net
 * @version $Id: mod_SidePanel_JT1.php  2012-07-07  Joomlatema.Net $
 * @author Muratyil
 * @copyright (C) 2010- Muratyil
 * @license GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
**/
header('content-type: text/css');
$moduleId = $_GET['id'];
?>
.ui-slideouttab-top {
	top: 0px;
	transform: translateY(-100%);
	border-top: none;
}
.ui-slideouttab-top.ui-slideouttab-open {
	transform: translateY(0%);
}
#sidepanel_jt<?php echo $moduleId ?>.ui-slideouttab-ready .handle<?php echo $moduleId ?>.imagebg.ui-slideouttab-handle {width:158px;
height:34px; transform:none;bottom:-34px !important;right:0px!important;left:auto!important; }