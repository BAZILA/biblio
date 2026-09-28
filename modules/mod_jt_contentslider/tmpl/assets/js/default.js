JTCJ = jQuery.noConflict();
jQuery(document).ready(function () {
	if (typeof(jtcModuleIds) != 'undefined') {
		for (var i = 0; i < jtcModuleIds.length; i++) {
			jQuery('#jtcontentslider' + jtcModuleIds[i]).css("direction", "ltr");
			jQuery('#jtcontentslider' + jtcModuleIds[i]).fadeIn("fast");
			
			if(btcModuleOpts[i].width=='auto'){
				jQuery('#jtcontentslider' + jtcModuleIds[i] + ' .slide').width(jQuery('#jtcontentslider' + jtcModuleIds[i] + ' .slide').width());
			}
			
			JTCJ('#jtcontentslider' + jtcModuleIds[i]).slides(btcModuleOpts[i]);
			if (jQuery("html").css("direction") == "rtl") {
				jQuery('#jtcontentslider' + jtcModuleIds[i] + ' .slides_control').css("direction", "rtl");
			}
		}
	}
	jQuery('img.hovereffect').hover(function () {
		jQuery(this).stop(true).animate({
			opacity : 0.5
		}, 300)
	}, function () {
		jQuery(this).animate({
			opacity : 1
		}, 300)
	})
})
