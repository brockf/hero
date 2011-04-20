<?php

/**
* Retrieve current URL *with* query string
*/
function current_url () {
	$CI =& get_instance();
	$base_url = $CI->config->site_url($CI->uri->uri_string()) . '?' . http_build_query($_GET);
	
	// remove trailing question mark if there is one
	$base_url = rtrim($base_url, '?');
	
	return $base_url;
}