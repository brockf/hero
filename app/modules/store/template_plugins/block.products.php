<?php

/**
* Get Products
*
* Loads products from the database
*
* @param string $var Variable name in array
* @param int $id
* @param int $collection
* @param string $keyword
* @param string $sort The column to sort by
* @param string $sort_dir The direction, ASC or DESC, to sort
* @param int $limit The number of items to pull
* @param int $offset Database offset
*
*/

function smarty_block_products ($params, $tagdata, &$smarty, &$repeat){
	if (!isset($params['var'])) {
		show_error('You must specify a "var" parameter for template {products} calls.  This parameter specifies the variable name for the returned array.');
	}
	else {
		// deal with filters
		$filters = array();
		
		// param: collection
		if (isset($params['collection'])) {
			$filters['collection'] = $params['collection'];
		}
		
		// param: id
		if (isset($params['id'])) {
			$filters['id'] = $params['id'];
		}
		
		// param: keyword
		if (isset($params['keyword'])) {
			$filters['keyword'] = $params['keyword'];
		}
		
		// param: sort
		if (isset($params['sort'])) {
			$filters['sort'] = $params['sort'];
		}
		
		// param: sort_dir
		if (isset($params['sort_dir'])) {
			$filters['sort_dir'] = $params['sort_dir'];
		}
		
		// param: limit
		if (isset($params['limit'])) {
			$filters['limit'] = $params['limit'];
		}
		
		// param: offset
		if (isset($params['offset'])) {
			$filters['offset'] = $params['offset'];
		}
		
		// initialize block loop
		$data_name = $smarty->CI->smarty->loop_data_key($filters);
		
		if ($smarty->CI->smarty->loop_data($data_name) === FALSE) {
			// make store request
			$smarty->CI->load->model('store/products_model');
			$products = $smarty->CI->products_model->get_products($filters);
		}
		else {
			$products = FALSE;
		}
		
		$smarty->CI->smarty->block_loop($data_name, $products, (string)$params['var'], $repeat);
	}
				
	echo $tagdata;
}