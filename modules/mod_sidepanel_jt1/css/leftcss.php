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
.ui-slideouttab-left {
	left: 0px;
	transform: translateX(-100%);
	border-left: none;
}
.ui-slideouttab-left.ui-slideouttab-open {
	transform: translateX(0%);
}
.ui-slideouttab-left .ui-slideouttab-handle {
    transform-origin: 100% 0%;
    transform: rotate(-90deg);   
}
.ui-slideouttab-left .ui-slideouttab-handle-reverse {
    transform-origin: 100% 100%;
    transform: rotate(-90deg) translate(100%,100%);   
}
#sidepanel_jt<?php echo $moduleId ?>.ui-slideouttab-ready .handle<?php echo $moduleId ?>.imagebg.ui-slideouttab-handle {width:34px;height:158px; transform:none;right:-32px !important; }