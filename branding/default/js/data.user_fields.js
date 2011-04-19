$(document).ready(function() {
	var base_url = $('#base_url').html();
	
	$('select[name="billing_equiv"], input[name="admin_only"], input[name="registration_form"]').click(function () {
		var row = $(this).parent().parent();
		
		var this_custom_field_id = row.attr('rel');
		var this_billing_equiv = row.find('select[name="billing_equiv"]').val();
		var this_admin_only = row.find('input[name="admin_only"]:checked').val();
		var this_registration_form = row.find('input[name="registration_form"]:checked').val();
		
		$.post(base_url + 'users/data_update', { custom_field_id : this_custom_field_id, billing_equiv : this_billing_equiv, admin_only : this_admin_only, registration_form : this_registration_form }, function (response) {
			if (response == 'SUCCESS') {
				notice_ok('Field updated successfully.');
			}
			else {
				notice_error('There was an error updating this field.');
			}
		});
	});
});