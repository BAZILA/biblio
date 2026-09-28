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
.ui-slideouttab-bottom {
	bottom: 0px;
	transform: translateY(100%);
	border-bottom: none;
}
.ui-slideouttab-bottom.ui-slideouttab-open {
		transform: translateY(0%);
}
#sidepanel_jt<?php echo $moduleId ?>.ui-slideouttab-ready .handle<?php echo $moduleId ?>.imagebg.ui-slideouttab-handle {width:158px;
height:34px; transform:none;top:-34px !important; }