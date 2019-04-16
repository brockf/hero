<?php

/*
* Feature Image Template Function
*
* Displays a thumbnail from a URL path
*
* @param string $path
* @param int $height (in pixels)
* @param int $width (in pixels)
*
* @return string $image_src To be used like <img src="{thumbnail ...}" />
*/
function smarty_function_thumbnail ($params, $smarty) {
	$smarty->CI->load->helper('image_thumb');
	
	return image_thumb($params['path'], (isset($params['height'])) ? $params['height'] : FALSE, (isset($params['width'])) ? $params['width'] : FALSE);
}