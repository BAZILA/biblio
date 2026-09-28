<?php
/**
 * @package Vertical scroller JT1 Pro Module for Joomla! 2.5
 * @version $Id: 1.0 
 * @author muratyil
 * @Copyright (C) 2013- muratyil
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
**/
header('content-type: text/css');
$moduleId = $_GET['id'];

?>
.glyphicon
{
    margin-right:4px !important; /*override*/
}

.pagination .glyphicon
{
    margin-right:0px !important; /*override*/
}

.pagination a
{
    color:#555;
}

#scroller<?php echo $moduleId; ?> ul
{
    padding:0px;
    margin:0px;
    list-style:none;
	width:100%;
}
#scroller<?php echo $moduleId; ?> h4 {line-height: 1;}


img.opacity:hover{opacity : 0.5;transition: all 0.2s linear 0s;}
.vscr-image {overflow:hidden;background:#000;}

img.zoomin:hover {transition: all 0.2s linear 0s;
  opacity: 0.7;
    -webkit-transform: scale(1.2,1.2);
    -moz-transform: scale(1.2,1.2);
    -ms-transform: scale(1.2,1.2);
    -o-transform: scale(1.2,1.2);
    transform: scale(1.2,1.2);
    
	-webkit-transition: all .5s ease;
    -moz-transition: all .5s ease;
    -ms-transition: all .5s ease;
    -o-transition: all .5s ease;
    transition: all .5s ease;}
	
img.zoomin{transition: all 0.2s linear 0s; opacity: 1;
    -webkit-transform: scale(1,1);
    -moz-transform: scale(1,1);
    -ms-transform: scale(1,1);
    -o-transform: scale(1,1);
    transform: scale(1,1);}
	
@-webkit-keyframes zoomout {
    0% {
        -webkit-transform: scale(1,4);
    }
    50% {
        -webkit-transform: scale(1.2,1.2);
    }
    100% {
        -webkit-transform: scale(1.0,1.0);
    }
}

@keyframes zoomout {
    0% {
        transform: scale(1,4);
		opacity:1;
    }
    50% {
        transform: scale(1.2,1.2);
		opacity:1;
    }
    100% {
        transform: scale(1.0,1.0);
		opacity:1;
    }
}
img.zoomout:hover{ -webkit-animation: zoomout 0.5s; /* Chrome, Safari, Opera */
    animation: zoomout 0.5s;}

.vertical-scroller<?php echo $moduleId; ?> {position:relative;}	
.vertical-scroller<?php echo $moduleId; ?> .navigate{
	position: absolute;
	padding: 0px 0px;
	border-bottom-right-radius: 0px;
	border-bottom-left-radius: 0px;
	background-color: transparent;
	border-top: 0;
}
#next-animate<?php echo $moduleId; ?>,#prev-animate<?php echo $moduleId; ?>{line-height:20px;cursor:pointer;text-decoration:none;transition: all 0.2s linear 0s;}
#next-animate<?php echo $moduleId; ?> .arrow-border i,#prev-animate<?php echo $moduleId; ?> .arrow-border i{border:1px solid ;font-size:20px!important;
padding:5px 10px;
vertical-align: middle;
cursor:pointer;
transition: all 0.2s linear 0s;}
.vertical-scroller<?php echo $moduleId; ?> button#pause<?php echo $moduleId; ?>{border:0;background:url(../images/pause.png) no-repeat right;width:65px; margin:0 10px 0 0;padding-right:10px;}
.vertical-scroller<?php echo $moduleId; ?> button#pause<?php echo $moduleId; ?>.unpaused{background:url(../images/pause.png) no-repeat right;padding-right:10px;}
.vertical-scroller<?php echo $moduleId; ?> button#pause<?php echo $moduleId; ?>.paused{background:url(../images/play.png) no-repeat right;padding-right:10px;}

.vertical-scroller<?php echo $moduleId; ?> button{float:none!important;display:inline-block;text-shadow:none; box-shadow:none;}
a.vjt1-title{text-decoration:none;}

/*************MORE IN**/
.vertical-scroller<?php echo $moduleId; ?> a.more_in { 
	margin: 0px 0px 0px 0px;
	padding: 0px 5px 0px 5px;
	background: none;
	cursor: pointer;
	text-decoration: none;
	font-size:12px;
	font-weight:bold;
	border:0;
}
.vertical-scroller<?php echo $moduleId; ?> a.more_in:hover,  a.more_in:hover {
	background:none;
	margin: 0px 0px 0px 0px;
	padding: 0px 5px 0px 5px;
	cursor: pointer;
	text-decoration: none;
	font-size:12px;
	font-weight:bold;
	border:0;
}