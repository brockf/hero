$(document).ready(function () {
	if ($('#free_trial').val() == '') {
		$('.free_trial_options').hide();
	}

	// amount: move check on textbox focus
	$('#amount').focus(function() {
		$('[name="plan_type"][value="paid"]').attr('checked',true);
	});
	
	// initial_charge: move check on textbox focus
	$('#initial_charge').focus(function() {
		$('[name="initial_charge_same"][value="0"]').attr('checked',true);
	});
	
	// occurrences: move check on textbox focus
	$('#occurrences').focus(function() {
		$('[name="occurrences_radio"][value="1"]').attr('checked',true);
	});
	
	// free_trial: move check on textbox focus
	$('#free_trial').focus(function() {
		$('[name="free_trial_radio"][value="1"]').attr('checked',true);
		$('.free_trial_options').show();
	});
});