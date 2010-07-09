$(document).ready(function () {
	ShowHideFieldOptions();

	$('#type').click(function() {
		ShowHideFieldOptions();
	});
});

function ShowHideFieldOptions () {
	// handle preset values
	if ($('#type').val() == 'text' || $('#type').val() == 'file' || $('#type').val() == 'date' || $(this).val() == 'wysiwyg' || $('#type').val() == 'textarea' || $('#type').val() == 'checkbox') {
		$('.field_options').hide();
	}
	else {
		$('.field_options').show();
	}
	
	if ($('#type').val() == 'text' || $('#type').val() == 'wysiwyg' || $('#type').val() == 'textarea') {
		$('.field_width').show();
		$('.field_default_checkbox').hide();
		$('.field_default_text').show();
	}
	else if ($('#type').val() == 'file' || $('#type').val() == 'date') {
		$('.field_default_checkbox').hide();
		$('.field_default_text').hide();
	}
	else if ($('#type').val() == 'checkbox') {
		$('.field_default_checkbox').show();
		$('.field_default_text').hide();
		$('.field_width').hide();
	}
	else {
		$('.field_default_text').hide();
		$('.field_default_checkbox').hide();
		$('.field_width').hide();
	}
	
	if ($('#type').val() == 'file') {
		$('.normal_validation').hide();
		$('.file_validation').show();
		$('.field_default').hide();
	}
	else if ($('#type').val() == 'date') {
			$('.normal_validation').hide();
			$('.file_validation').hide();
			$('.field_default').hide();
		}
	else {
		$('.normal_validation').show();
		$('.file_validation').hide();
		$('.field_default').show();
	}
}