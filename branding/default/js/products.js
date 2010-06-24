$(document).ready(function () {
	$('input.action').click(function () {
		var product_id = $(this).siblings('input[name="product_id"]').val();
		var action = $(this).siblings('select[name="action"]').val();
		
		var form_url = $('#base_url').html() + 'store/product_actions/' + product_id + '/' + action;
		$('form#dataset_form').attr("ACTION",form_url);
		$('form#dataset_form').submit();
	});
});