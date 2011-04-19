$(document).ready(function () {
	// drop down menu
	$('div#navigation ul li').hover(function () {
		var children = $(this).children('ul.children');
		
		if (children.length > 0) {
			children.stop(true,true).fadeIn('fast');
			$(this).children('a').addClass('hover');
		}
	}, function () { 
		var children = $(this).children('ul.children');
		
		if (children.length > 0) {
			children.delay(1).stop(true,true).fadeOut('fast');
			$(this).children('a').removeClass('hover');
		}
	});
	
	// submit button
	$('input.button').hover(function () {
		$(this).toggleClass('hover');
	});
});