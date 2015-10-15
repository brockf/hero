<?php

/*
* Cart Total Template Function
*
* Returns the total value of the cart
*
* @return float $cart_total
*/
function smarty_function_cart_total ($params, $smarty) {
	$smarty->CI->load->model('store/cart_model');
	
	return $smarty->CI->cart_model->get_total();
}