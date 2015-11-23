<?php

/*
* Cart Items Template Function
*
* Returns the total number of items in the cart
*
* @return int $cart_items
*/
function smarty_function_cart_items ($params, $smarty) {
	$smarty->CI->load->model('store/cart_model');
	
	$cart = $smarty->CI->cart_model->get_cart();
	
	if (empty($cart)) {
		return '0';
	}
	
	$items = 0;
	foreach ($cart as $item) {
		$items = $item['qty'] + $items;
	}
	
	return $items;
}