$(document).ready(function () {
	$('#type').change(function() {
		if ($(this).val() == 'text' || $(this).val() == 'file' || $(this).val() == 'wysiwyg' || $(this).val() == 'textarea' || $('#type').val() == 'checkbox') {
			$('.field_options').hide();
		}
		else {
			$('.field_options').show();
		}
		
		if ($(this).val() == 'text' || $(this).val() == 'textarea' || $(this).val() == 'wysiwyg') {
			$('.field_width').show();
			$('.field_default_checkbox').hide();
			$('.field_default_text').show();
		}
		else if ($(this).val() == 'file') {
			$('.field_default_checkbox').hide();
			$('.field_default_text').hide();
		}
		else if ($(this).val() == 'checkbox') {
			$('.field_default_checkbox').show();
			$('.field_default_text').hide();
			$('.field_width').hide();
		}
		else {
			$('.field_default_text').hide();
			$('.field_default_checkbox').hide();
			$('.field_width').hide();
		}
		
		if ($(this).val() == 'file') {
			$('.normal_validation').hide();
			$('.file_validation').show();
			$('.field_default').hide();
		}
		else {
			$('.normal_validation').show();
			$('.file_validation').hide();
			$('.field_default').show();
		}
	});
	
	// handle preset values
	if ($('#type').val() == 'text' || $('#type').val() == 'file' || $(this).val() == 'wysiwyg' || $('#type').val() == 'textarea' || $('#type').val() == 'checkbox') {
		$('.field_options').hide();
	}
	else {
		$('.field_options').show();
	}
	
	if ($('#type').val() == 'text' || $(this).val() == 'wysiwyg' || $('#type').val() == 'textarea') {
		$('.field_width').show();
		$('.field_default_checkbox').hide();
		$('.field_default_text').show();
	}
	else if ($('#type').val() == 'file') {
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
	else {
		$('.normal_validation').show();
		$('.file_validation').hide();
		$('.field_default').show();
	}
});