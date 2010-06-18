$(document).ready(function () {
	$('#type').change(function() {
		if ($(this).val() == 'text' || $(this).val() == 'textarea') {
			$('#field_options').hide();
		}
		else {
			$('#field_options').show();
		}
	});
	
	// handle preset trigger
	if ($('#type').val() == 'text' || $('#type').val() == 'textarea') {
		$('#field_options').hide();
	}
	else {
		$('#field_options').show();
	}
});