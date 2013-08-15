<?php

/*
* Shorten Template Function
*
* Shortens a string to a specific length
*
* @param string $string
* @param int $length (in characters)
*
* @return string Shortened text
*/
function smarty_function_shorten ($params, $smarty) {
	if (!isset($params['length'])) {
		return 'You must specify a "length" parameter for the {shorten} template function.';
	}
	elseif (!isset($params['string'])) {
		return 'You must specify a "string" parameter for the {shorten} template function.';
	}
	else {
		$smarty->CI->load->helper('text');
		
		$shortened = character_limiter($params['string'], $params['length']);
		
		// we may have HTML, so remove any unclosed tags
		$shortened = preg_replace("/<([^<>]*)(?=<|$)/", "$1", $shortened);
		
		return $shortened;
	}
}