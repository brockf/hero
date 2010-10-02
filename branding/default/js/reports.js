$(document).ready(function () {
	$('form#dataset_form input.action').click(function () {
		var id = $(this).siblings('input[name="product_id"]').val();
		var action = $(this).siblings('select[name="action"]').val();
		
		var form_url = $('#base_url').html() + $(this).attr('rel') + '/' + action + '/' + id;
		
		$('form#dataset_form').attr("ACTION",form_url);
		$('form#dataset_form').submit();
	});
});