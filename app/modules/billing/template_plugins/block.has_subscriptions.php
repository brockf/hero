<?php

/**
* Has Subscriptions
*
* Show contents if the user has any subscriptions
*
*/

function smarty_block_has_subscriptions ($params, $tagdata, &$smarty, &$repeat) {
	$smarty->CI->load->model('billing/subscription_model');
	if ($smarty->CI->subscription_model->has_subscriptions()) {
		return $tagdata;
	}
	else {
		return '';
	}
}