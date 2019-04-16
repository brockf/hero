<?php

/*
* Theme URL Function
*
* Outputs an absolute URL path relative to the current theme's directory
*
* @param $path URL path (e.g, css/test.css)
*
* @return string URL
*/
function smarty_function_theme_url ($params, $smarty) {
	return site_url('themes/' . $smarty->CI->config->item('current_theme') . '/' . $params['path']);
}