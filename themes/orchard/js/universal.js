$(document).ready(function () {
	// drop down menu
	$('div#navigation ul li').hover(function () {
		var children = $(this).children('ul.children');
		
		if (children.length > 0) {
			children.stop(true,true).slideDown('fast');
		}
	}, function () { 
		var children = $(this).children('ul.children');
		
		if (children.length > 0) {
			children.delay(1).stop(true,true).slideUp('fast');
		}
	});
	
	// submit button
	$('input.button').hover(function () {
		$(this).toggleClass('hover');
	});
});