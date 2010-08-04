<?php

$CI =& get_instance();

if ($CI->config->item('ssl_active') == TRUE) {
	if (($CI->uri->segment(1) == 'users' or $CI->uri->segment(1) == 'checkout') and $_SERVER["SERVER_PORT"] != "443") {
		header("Location: " . secure(current_url()));
		die();
	}
	elseif ($_SERVER["SERVER_PORT"] == "443" and ($CI->uri->segment(1) != 'users' and $CI->uri->segment(1) != 'checkout')) {
		header('Location: ' . unsecure(current_url()));
		die();
	}		
	
	function secure($url) {
		return str_replace('http://','https://',$url);
	}
	
	function unsecure($url) {
		return str_replace('https://','http://',$url);
	}
}