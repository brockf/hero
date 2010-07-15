$(document).ready(function () {
	if ($('#summary_field').hasClass('editing')) {
		// we don't want to do this, or we will get rid of the pre-selected item
	}
	else {
		get_fields_for_summary_field();
	}

	$('#content_type').click(function () {
		get_fields_for_summary_field();
	});
});

function get_fields_for_summary_field () {
	base_url = $('#base_url').html();

	$.getJSON(base_url + 'rss/get_fields/' + $('#content_type').val(), function(data) {
		var options = data;
			
			var options_options = '';
			$.each(options, function (intIndex, objValue) {
				if (intIndex == 'key_0') {
					intIndex = '';
				}
				options_options = options_options + '<option value="' + intIndex + '">' + objValue + '</option>';
		});
		
		$('#summary_field').html(options_options);
	});
}