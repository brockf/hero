<?php

function smarty_outputfilter_user_activity ($output, &$smarty) {
	if ($smarty->CI->config->item('simultaneous_login_prevention') == '0') {
		return $output;
	}

	$url = site_url('users/user_activity');

	$code = '
	<script type="text/javascript">
	if (typeof jQuery != \'undefined\') {
	
		var activity_register = setTimeout(\'user_activity_register()\', 1000);
		var activity_register_count = 0;
		
		function user_activity_register () {
			jQuery.post(\'' . $url . '\');
			
			activity_register_count = activity_register_count + 1;	
			
			if (activity_register_count < 30) {
				activity_register = setTimeout(\'user_activity_register()\', 15000);
			}
		}
	}
	</script>';

	$output = str_ireplace('</head>', $code . "\n" . '</head>',$output);
	
	return $output;
}