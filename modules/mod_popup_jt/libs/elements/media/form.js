 /**
 * @package SidePanel Module for Joomla 2.5 By Joomlatema.net
 * @version $Id: mod_SidePanel_JT1.php  2012-07-07  Joomlatema.Net $
 * @author Muratyil
 * @copyright (C) 2010- Muratyil
 * @license GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
**/
// JavaScript Document\
 
 function removeFile( obj ){
	var parent = jQuery(obj).parent("div").parent("div");
	parent.find(".jtn-enable").val(0);
	parent.parent("div").fadeOut();
	parent.parent("div").attr('style',"display:none");
 }
function addFile( ){
	var listFiles = jQuery("#myTabContent .control-group .jtn-enable");
	for(var i= 0; i< listFiles.length;i++){
        enableField = jQuery(listFiles[ i ]);
		var item = enableField.parent("div").parent("div").parent("div");
		var enableField = item.find(".jtn-enable");
		if(enableField){
			if(enableField.val() == 0){
				item.fadeIn();
				item.attr('style',"display:block");
				enableField.val(1);
				break;
			}
		}
	}
 }

function check_init(){
    var type = jQuery("#jform_params_group");
    if(type.val() == 'file') {
        hideHtml();
        displayEnabledModules();
    }
    else{
        displayHtml()
        hideAllModule();
    }
}

function hideAllModule(){
    var listFiles = jQuery("#myTabContent .control-group .jtn-enable");

    for(var i= 0; i< listFiles.length;i++){
        var enableField = jQuery(listFiles[ i ]);
        var item = enableField.parent("div").parent("div").parent("div");
        if(enableField){
            item.attr('style',"display:none");
        }
    }

    var file = jQuery("#jform_params_filegroup-lbl");
    var file_l = file.parent("div").parent("div");
    file_l.attr('style',"display:none");
}

function displayEnabledModules(){
    var listFiles = jQuery("#myTabContent .control-group .jtn-enable");
    var check = false;
    for(var i= 0; i< listFiles.length;i++){
        var enableField = jQuery(listFiles[ i ]);
        if(enableField){
            var item = enableField.parent("div").parent("div").parent("div");

            if(enableField.val() == 0 || enableField.val() == ""){
                item.attr('style',"display:none");
            }
            else if(enableField.val() == 1){
                item.attr('style',"display:block");
                check = true;
            }
        }

    }

    if(check == false) {
        var enableField = jQuery(listFiles[ 0 ]);
        var item = enableField.parent("div").parent("div").parent("div");
        enableField.val(1);
        item.attr('style',"display:block");
    }

    var file = jQuery("#jform_params_filegroup-lbl");
    var file_l = file.parent("div").parent("div");
    file_l.attr('style',"display:block");
}

function hideHtml(){
    var login = jQuery("#jform_params_login");
    var item_login = login.parent("div").parent("div");
    item_login.attr('style',"display:none");

    var login_t = jQuery("#jform_params_jtn_added_time");
    var item_login_t = login_t.parent("div").parent("div");
    item_login_t.attr('style',"display:none");
}

function displayHtml(){
    var login = jQuery("#jform_params_login");
    var item_login = login.parent("div").parent("div");
    item_login.attr('style',"display:block");

    var login_t = jQuery("#jform_params_jtn_added_time");
    var item_login_t = login_t.parent("div").parent("div");
    item_login_t.attr('style',"display:block");
}


jQuery(document).ready(function(){
    check_init();

    jQuery('#jform_params_group').change(function(){
        check_init();
    });
});

