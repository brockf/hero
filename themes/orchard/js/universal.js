$(document).ready(function () {
	// drop down menu
	$('div#navigation ul a').click(function () {
		var children = $(this).parent().children('ul.children');
		
		if (children.length > 0) {
			if (children.filter(':visible').length > 0) {
				// menu already showing, pass the user on
				return true;
			}
			else {
				// menu not showing, drop it down
				
				// close others
				$('div#navigation ul.children').slideUp('fast');
				
				children.slideDown('fast');
				return false;
			}
		}
		else {	
		 	// no sub-menu, pass them on
			return true;
		}
	});
	
	$(document).click(function () {
		$('div#navigation ul.children').slideUp('fast');
	});

	// submit button
	$('input.button').hover(function () {
		$(this).toggleClass('hover');
	});
});