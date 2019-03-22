$(document).ready(function () {
	$('div.shipping_address').hide();
	$('a.hide_shipping').hide();
	
	$('a.show_shipping').click(function () {
		$(this).siblings('div.shipping_address').slideDown();
		$(this).hide();
		$(this).siblings('a.hide_shipping').show();
		
		return false;
	});
	
	$('a.hide_shipping').click(function () {
		$(this).siblings('div.shipping_address').slideUp();
		$(this).hide();
		$(this).siblings('a.show_shipping').show();
		
		return false;
	});
	
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