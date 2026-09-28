//add span to first word of module title, page heading
jQuery(function($){

	// Last word in title
	$('h3.bicolor-lastword,h1.bicolor-lastword,h2.bicolor-lastword,h4.bicolor-lastword,h5.bicolor-lastword').html(function(){	
		var text = $(this).text().split(' ');
		var last = text.pop();
		return text.join(" ") + (text.length > 0 ? ' <span class="last-word-exeltis">'+last+'</span>' : last);   
	});
	
	// First word in title
	$('h3.bicolor,h4.bicolor,h1.bicolor,h2.bicolor,h6.bicolor,.logo-text .site-name-exeltis,.logo-text .site-name-exeltis a').html(function(){	
		var text = $(this).text().split(' ');
		var first = text.shift();
		return (text.length > 0 ? '<span class="first-word-exeltis">'+first+'</span> ' : first) + text.join(" ");
	});
});
