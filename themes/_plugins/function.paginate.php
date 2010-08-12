<?php

/*
* Paginate Function
*
* Generate a standard pagination HTML string for a set of configuration values
*
* @param string $base_url
* @param int $total_rows
* @param int $per_page
* @param string $variable The name of the $_GET variable storing the offset #
*
* @return string $setting value
*/
function smarty_function_paginate ($params, $smarty, $template) {
	if (!isset($params['base_url'])) {
		return 'The "base_url" parameter is required for {paginate}.';
	}
	if (!isset($params['total_rows'])) {
		return 'The "total_rows" parameter is required for {paginate}.';
	}
	if (!isset($params['per_page'])) {
		return 'The "per_page" parameter is required for {paginate}.';
	}
	if (!isset($params['variable'])) {
		return 'The "variable" parameter is required for {paginate}.';
	}
	
	$config['base_url'] = $params['base_url'];
	$config['total_rows'] = $params['total_rows'];
	$config['per_page'] = $params['per_page'];
	$config['num_links'] = '10';
	$config['page_query_string'] = TRUE;
	$config['query_string_segment'] = $params['variable'];
	
	$smarty->CI->load->library('pagination');
	$smarty->CI->pagination->initialize($config);
	
	$links = $smarty->CI->pagination->create_links();
	
	// we may have cases of ?& because of CodeIgniter thinking we have universally enabled query strings
	$links = str_replace('?&amp;','?', $links);
	
	return $links;
}