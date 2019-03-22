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
function smarty_function_paginate ($params, $smarty) {
	if (!isset($params['base_url'])) {
		return 'The "base_url" parameter is required for {paginate}.  You should probably set it to {$current_url}.';
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
	
	// if they have a paginating variable in the URL, we should strip it
	$base_url_parsed = parse_url($params['base_url']);
	$base_url = $base_url_parsed['scheme'] . '://' . $base_url_parsed['host'] . $base_url_parsed['path'] . '?';
	// there may be an ending "?" here due to CodeIgniter oddness
	$base_url_parsed['query'] = rtrim($base_url_parsed['query'], '?');
	if (!empty($base_url_parsed['query'])) {
		parse_str($base_url_parsed['query'], $query_values);

		foreach ($query_values as $k => $v) {
			if ($k != $params['variable']) {
				$base_url .= $k . '=' . $v . '&';
			}
		}
		
		$base_url = rtrim($base_url, '&');
	}
	
	$config['base_url'] = $base_url;
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