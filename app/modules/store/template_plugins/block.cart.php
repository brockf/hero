<?php

/**
* Get Cart
*
* Display each cart item from the database
*
* @param string $var Variable name in array
*
*/

function smarty_block_cart ($params, $tagdata, &$smarty, &$repeat){
	if (!isset($params['var'])) {
		show_error('You must specify a "var" parameter for template {cart} calls.  This parameter specifies the variable name for the returned array.');
	}	
	
	// initialize block loop
	$data_name = 'cart_tag';
	
	if ($smarty->CI->smarty->loop_data($data_name) === FALSE) {
		// make content request
		$smarty->CI->load->model('store/cart_model');
		$cart = $smarty->CI->cart_model->get_cart();
		
		// $cart arrays have a strange index that's not compatible with the Smarty::block_loop() method
		// we'll re-index
		
		$cart_reindexed = array();
		foreach ($cart as $item) {
			$cart_reindexed[] = $item;
		}
		
		$cart = $cart_reindexed;
	}
	else {
		$cart = FALSE;
	}
	
	$smarty->CI->smarty->block_loop($data_name, $cart, (string)$params['var'], $repeat);
	
	echo $tagdata;
}