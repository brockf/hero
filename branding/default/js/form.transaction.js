$(document).ready(function () {
	TrialVsInitial();
	
	ShowHide();
	
	$('#gateway').change(function () {
		ShowHide();
	});
	
	$('#gateway').focus(function () {
		ShowHide();
	});

	$('#initial_charge, #free_trial, #no_free_trial').change(function () {
		TrialVsInitial();
	});
	
	$('#initial_charge, #free_trial, #no_free_trial').focus(function () {
		TrialVsInitial();
	});
	
	$('#amount').change(function () {
		ShowHide();
	});
	
	if ($('p.no_gateway').length != 0) {
		$('#form_transaction input, #form_transaction select').attr('disabled','disabled');
	}
});

function TrialVsInitial () {
	if ($('#initial_charge').val() > 0) {
		$('#free_trial').val('0');
		$('#no_free_trial').attr('checked',true);
	}
	
	if ($('#free_trial').val() > 0) {
		$('#no_free_trial').attr('checked',false);
		$('#initial_charge').val('0');
	}
	else {
		$('#no_free_trial').attr('checked',true);
	}
}

function ShowHide () {
	if ($('#amount').val() == 0 || $('#amount').val() == '0.00') {
		$('#initial_charge').val('0');
		$('#transaction_gateway').hide();
		$('#transaction_cc').hide();
		$('#transaction_customer').hide();
		$('#row_initial_charge').hide();
		$('#row_free_trial').hide();
	}
	else {
		$('#transaction_gateway').show();
		if ($('#gateway :selected').hasClass('external')) {
			$('#transaction_cc').hide();
			$('#transaction_cc input').removeClass('required');
		}
		else {
			$('#transaction_cc').show();
			$('#transaction_cc input').addClass('required');
			$('#transaction_cc input#cc_security').removeClass('required');
		}
		if ($('#gateway :selected').hasClass('billing_address')) {
			$('#transaction_customer').show();
		}
		else {
			$('#transaction_customer').hide();
		}
		$('#row_initial_charge').show();
		$('#row_free_trial').show();
	}
}