<?php

/**
* Get Subscriptions
*
* Load subscriptions related to a user
*
* @param string $var The variable to return the data to.
* @param boolean $active Is the subscription still active on the account (i.e., end_date < now)?
* @param boolean $recurring Is the subscription still actively recurring?
* @param int $id The subscription ID
* @param int $plan_id The subscription plan ID
*
*/

function smarty_block_subscriptions ($params, $tagdata, &$smarty, &$repeat) {
	if (!isset($params['var'])) {
		show_error('You must specify a "var" parameter for template {subscriptions} calls.  This parameter specifies the variable name for the returned array.');
	}
	else {
		// deal with filters
		$filters = array();
		
		// param: active
		if (isset($params['active']) and !empty($params['active'])) {
			$filters['active'] = TRUE;
		}
		
		// param: recurring
		if (isset($params['recurring']) and !empty($params['recurring'])) {
			$filters['recurring'] = TRUE;
		}
		
		// param: id
		if (isset($params['id'])) {
			$filters['id'] = $params['id'];
		}
		
		// param: plan_id
		if (isset($params['plan_id'])) {
			$filters['plan_id'] = $params['plan_id'];
		}
		
		// initialize block loop
		$data_name = $smarty->CI->smarty->loop_data_key($filters);
		
		if ($smarty->CI->smarty->loop_data($data_name) === FALSE) {
			// make content request
			$smarty->CI->load->model('billing/subscription_model');
			$subs = $smarty->CI->subscription_model->get_subscriptions_friendly($filters);
		}
		else {
			$subs = FALSE;
		}
		
		$smarty->CI->smarty->block_loop($data_name, $subs, (string)$params['var'], $repeat);
	}
			
	echo $tagdata;
}