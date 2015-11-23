<?php

/**
* Get Product
*
* Loads a product from the database
*
* @param string $var Variable name in array
* @param int $id The product ID
*
*/

function smarty_block_product ($params, $tagdata, &$smarty, &$repeat){
	if (!isset($params['id'])) {
		show_error('You must specify an "id" parameter for template {product} calls.');
	}
	elseif (!isset($params['var'])) {
		show_error('You must specify a "var" parameter for template {product} calls.  This parameter specifies the variable name for the returned array.');
	}
	else {
		$smarty->CI->load->model('store/products_model');
		$product = $smarty->CI->products_model->get_product($params['id']);
		
		if (empty($product)) {
			show_error('Error returning {product} tag: No product by that ID.');
		}
		
		$smarty->assign($params['var'], $product);
	}
				
	echo $tagdata;
}