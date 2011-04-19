$(document).ready(function () {
	$('#plan_updater').hide();
	
	$('#plan_updater_link a').click(function () {
		$('#plan_updater').show();
		$(this).parent().hide();
		
		return FALSE;
	});
});