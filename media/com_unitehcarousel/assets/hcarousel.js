var UniteHCarousel = new function(){
	
	var t = this;
	var containerID = "slider_container";
	var container,arrow_left,arrow_right,bullets_container;
	var caption_back,caption_text;
	var bulletsRelativeY = "";
	
	/**
	 * show slider view error, hide all the elements
	 */
	t.showSliderViewError = function(errorMessage){
		jQuery("#config-document").hide();
		UniteAdmin.showErrorMessage(errorMessage);
	}
	
	/**
	 * main init of the object
	 */
	var init = function(){
		UniteAdmin.hideSystemMessageDelay();
	}
	
	/**
	 * init visual form width
	 */
	t.initSliderView = function(){
		init();	//init the object - must call
	}
	
	/* ===================== Item View Section =================== */
	
	/**
	 * init item view
	 */
	t.initItemView = function(){
		
		//operate on slide image change
		var obj = document.getElementById("jform_params_image");
		obj.addEvent('change',function(){			
			var urlImage = g_imagePattern;
			var urlPreview = encodeURI(this.value);
			urlImage = urlImage.replace("IMAGE_PLACE",urlPreview);			
			
			jQuery("#image_preview_wrapper").show();
			jQuery("#image_preview").show().attr("src",urlImage);
		});
	}
	
	/* ===================== Item View End =================== */
	
	
}
	