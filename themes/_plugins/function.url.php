<?php

/*
* URL Function
*
* Outputs an absolute URL path relative to the site's base URL
*
* @param $path URL path (e.g, js/jquery-1.4.2.min.js)
*
* @return string URL
*/
function smarty_function_url ($params, $smarty) {
	if (!isset($params['path'])) {
		return base_url();
	}
	
	return site_url($params['path']);
}