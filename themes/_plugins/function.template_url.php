<?php

/*
* Theme URL Function
*
* Outputs an absolute URL path relative to the current theme's directory
*
* @param $url_path URL path (e.g, css/test.css)
*
* @return string URL
*/
function smarty_function_setting ($params, $smarty, $template) {
	return setting($params['name']);
}