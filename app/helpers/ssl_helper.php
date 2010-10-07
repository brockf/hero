<?php

$CI =& get_instance();

function secure($url) {
	return str_replace('http://','https://',$url);
}

function unsecure($url) {
	return str_replace('https://','http://',$url);
}

if ($CI->config->item('ssl_certificate') == '1') {
	if ((in_array($CI->uri->segment(1),$CI->config->item('secure_modules'))) and ($_SERVER["SERVER_PORT"] != "443" or (isset($_SERVER['HTTPS']) and $_SERVER['HTTPS'] != 'on'))) {
		header("Location: " . secure(current_url()));
		die();
	}
	elseif (($_SERVER["SERVER_PORT"] == "443" or (isset($_SERVER['https']) and $_SERVER['HTTPS'] == 'on')) and (!in_array($CI->uri->segment(1),$CI->config->item('secure_modules')))) {
		header('Location: ' . unsecure(current_url()));
		die();
	}		
}