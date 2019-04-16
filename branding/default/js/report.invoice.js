$(document).ready(function () {
	// shipping radios
	$('input.shipped_no').click(function() {
		var url = $('#base_url').html() + 'reports/shipped_no/' + $(this).attr('rel');
		
		$.get(url, false, function () {
			notice_ok('Product marked as not shipped.');
		});
		
		$(this).parent().parent().addClass('highlight');
	});
	
	$('input.shipped_yes').click(function() {
		var url = $('#base_url').html() + 'reports/shipped_yes/' + $(this).attr('rel');
		
		$.get(url, false, function () {
			notice_ok('Product marked as shipped.');
		});
		
		$(this).parent().parent().removeClass('highlight');
	});
});