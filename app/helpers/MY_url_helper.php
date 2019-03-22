<?php

/**
* Current URL
*
* Retrieve current URL *with* query string
*
* @copyright Electric Function, Inc.
* @package Hero Framework
* @author Electric Function, Inc.
*/
function current_url () {
	$CI =& get_instance();
	$base_url = $CI->config->site_url($CI->uri->uri_string()) . '?' . http_build_query($_GET);
	
	// remove trailing question mark if there is one
	$base_url = rtrim($base_url, '?');
	
	return $base_url;
}