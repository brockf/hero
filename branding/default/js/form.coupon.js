$(document).ready(function(){
	
	// Hook into our coupon type selector so we can 
	// show or hide options based on coupon type selected.
	$("#row_coupon_type select").change(function(){
		var coupon_type = $("#row_coupon_type select :selected").val();
				
		switch (coupon_type)
		{
			case '1':		// Price Reduction
				$("ul.coupon_trial, ul.coupon_shipping").parent().css("display", "none");
				$("ul.coupon_reduction").parent().fadeIn();
				break;
			case '2':		// Free Trial
				$("ul.coupon_reduction, ul.coupon_shipping").parent().css("display", "none");
				$("ul.coupon_trial").parent().fadeIn();
				break;
			case '3': 	// Free Shipping
				$("ul.coupon_trial, ul.coupon_reduction").parent().css("display", "none");
				$("ul.coupon_shipping").parent().fadeIn();
				break; 
		}
	});
	
	// Trigger our coupon type change event so everything looks correct
	$("#row_coupon_type select").trigger("change");
});