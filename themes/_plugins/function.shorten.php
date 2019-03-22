<?php

/*
* Shorten Template Function
*
* Shortens a string to a specific length
*
* @param string $string
* @param int $length (in characters)
*
* @return string $image_src To be used like <img src="{thumbnail ...}" />
*/
function smarty_function_shorten ($params, $smarty) {
	if (!isset($params['length'])) {
		return 'You must specify a "length" parameter for the {shorten} template function.';
	}
	elseif (!isset($params['string'])) {
		return 'You must specify a "string" parameter for the {shorten} template function.';
	}
	else {
		$smarty->CI->load->helper('shorten');
		
		return shorten($params['string'], $params['length'], TRUE);
	}
}