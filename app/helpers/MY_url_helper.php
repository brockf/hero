<?php

/**
* Retrieve current URL *with* query string
*/
function current_url () {
	$CI =& get_instance();
	$base_url = $CI->config->site_url($CI->uri->uri_string()) . '?' . http_build_query($_GET);
	
	return $base_url;
}