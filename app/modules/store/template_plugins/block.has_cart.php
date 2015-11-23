<?php

/**
* Has Cart
*
* Only display contents if the user has items in their cart
*
*/

function smarty_block_has_cart ($params, $tagdata, &$smarty, &$repeat){
	// make content request
	$smarty->CI->load->model('store/cart_model');
	$cart = $smarty->CI->cart_model->get_cart();
	
	if (empty($cart)) {
		return '';
	}
	else {
		return $tagdata;
	}
}