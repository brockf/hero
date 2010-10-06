<?php

$CI =& get_instance();

function secure($url) {
	return str_replace('http://','https://',$url);
}

function unsecure($url) {
	return str_replace('https://','http://',$url);
}

if ($CI->config->item('ssl_certificate') == '1') {
	if (($CI->uri->segment(1) == 'users' or $CI->uri->segment(1) == 'checkout') and ($_SERVER["SERVER_PORT"] != "443" or (isset($_SERVER['HTTPS']) and $_SERVER['HTTPS'] != 'on'))) {
		header("Location: " . secure(current_url()));
		die();
	}
	elseif (($_SERVER["SERVER_PORT"] == "443" or (isset($_SERVER['https']) and $_SERVER['HTTPS'] == 'on')) and ($CI->uri->segment(1) != 'users' and $CI->uri->segment(1) != 'checkout')) {
		header('Location: ' . unsecure(current_url()));
		die();
	}		
}