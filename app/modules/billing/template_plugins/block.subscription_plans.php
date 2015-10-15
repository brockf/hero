<?php

/**
* Get Subscription Plans
*
* Load subscriptions related to a user
*
* @param string $var The variable to return the data to.
* @param int $id The subscription ID
*
*/

function smarty_block_subscription_plans ($params, $tagdata, &$smarty, &$repeat) {
	if (!isset($params['var'])) {
		show_error('You must specify a "var" parameter for template {subscription_plans} calls.  This parameter specifies the variable name for the returned array.');
	}
	else {
		// deal with filters
		$filters = array();
		
		// param: id
		if (isset($params['id'])) {
			$filters['id'] = $params['id'];
		}
		
		// initialize block loop
		$data_name = $smarty->CI->smarty->loop_data_key($filters);
		
		if ($smarty->CI->smarty->loop_data($data_name) === FALSE) {
			// make content request
			$smarty->CI->load->model('billing/subscription_plan_model');
			$subs = $smarty->CI->subscription_plan_model->get_plans($filters);
		}
		else {
			$subs = FALSE;
		}
		
		$smarty->CI->smarty->block_loop($data_name, $subs, (string)$params['var'], $repeat);
	}
			
	echo $tagdata;
}