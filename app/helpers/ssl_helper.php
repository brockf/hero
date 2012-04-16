<?php

/**
* SSL Helper
*
* Redirects to SSL if we are in a SSL-necessary place (defined as "secure_modules" in the config file)
*
* @copyright Electric Function, Inc.
* @package Hero Framework
* @author Electric Function, Inc.
*/
$CI =& get_instance();

function secure($url) {
	return str_replace('http://','https://',$url);
}

function unsecure($url) {
	return str_replace('https://','http://',$url);
}

function is_ssl () {
	if ($_SERVER['SERVER_PORT'] == 443) {
		return TRUE;
	}
	
	if (isset($_SERVER['HTTPS']) and $_SERVER['HTTPS'] == 'on') {
		return TRUE;
	}
	
	if (isset($_SERVER['HTTP_X_FORWARDED_PORT']) and $_SERVER['HTTP_X_FORWARDED_PORT'] == 443) {
		return TRUE;
	}
	
	if (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) and $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https') {
		return TRUE;
	}
	
	return FALSE;
}

// never redirect the user_activity controller!
if ($CI->uri->segment(2) != 'user_activity') {

	if ($CI->config->item('ssl_certificate') == '1') {
		if ((in_array($CI->uri->segment(1),$CI->config->item('secure_modules'))) and !is_ssl()) {
			header("Location: " . secure(current_url()));
			die();
		}
		elseif (is_ssl() and (!in_array($CI->uri->segment(1),$CI->config->item('secure_modules')))) {
			header('Location: ' . unsecure(current_url()));
			die();
		}		
	}
	
}