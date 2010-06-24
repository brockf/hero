$(document).ready(function() {
	$('div.help').each(function() {
		$(this).parent().remove();
	});
	
	$('input.required').removeClass('required');
});