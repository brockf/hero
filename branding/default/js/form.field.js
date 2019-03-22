$(document).ready(function () {
	var base_url = $('#base_url').html();
	var site_url = $('#site_url').html();

	$('#type').change(function () {
		// place loading image
		$('fieldset#field_options').html('<img src="' + site_url + 'branding/default/images/loading.gif" alt="Loading..." />');
				
		// we have a change compatible with IE6
		var this_type = $(this).val();
		var field_id = $('#field_id').val();
		
		$.post(base_url + 'custom_fields/ajax_field_form', { type : this_type, id : field_id } , function (response) {
			$('fieldset#field_options').html('<ul class="form">' + response + '</ul>');
		});
	});
	
	// trigger click
	$('#type').trigger('change');
});