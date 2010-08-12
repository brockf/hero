<?php

function current_url () {
	$CI =& get_instance();
	$base_url = $CI->config->site_url($CI->uri->uri_string());
		
	// add a query string if we have one
	$my_query_string = unserialize(MY_QUERY_STRING);
	if (is_array($my_query_string)) {
		$base_url .= '?';
		
		foreach ($my_query_string as $k => $v) {
			$base_url .= $k . '=' . $v . '&';
		}

		$base_url = rtrim($base_url, '&');
		
		return $base_url;
	}
	else {
		return $base_url;
	}
}