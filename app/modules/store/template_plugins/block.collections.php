<?php

/**
* Get Collections
*
* Loads collections from the database
*
* @param string $var Variable name in array
* @param int $parent
* @param int $id
*
*/

function smarty_block_collections ($params, $tagdata, &$smarty, &$repeat){
	if (!isset($params['var'])) {
		show_error('You must specify a "var" parameter for template {collections} calls.  This parameter specifies the variable name for the returned array.');
	}
	else {
		// deal with filters
		$filters = array();
		
		// param: collection
		if (isset($params['parent'])) {
			$filters['parent'] = $params['parent'];
		}
		
		// param: id
		if (isset($params['id'])) {
			$filters['id'] = $params['id'];
		}
		
		// initialize block loop
		$data_name = $smarty->CI->smarty->loop_data_key($filters);
		
		if ($smarty->CI->smarty->loop_data($data_name) === FALSE) {
			// make store request
			$smarty->CI->load->model('store/collections_model');
			$collections = $smarty->CI->collections_model->get_collections($filters);
		}
		else {
			$collections = FALSE;
		}
		
		$smarty->CI->smarty->block_loop($data_name, $collections, (string)$params['var'], $repeat);
	}
				
	echo $tagdata;
}