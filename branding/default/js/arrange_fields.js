$(document).ready(function() {
	$('div.help').each(function() {
		$(this).remove();
	});
	
	$('input.required').removeClass('required');
});