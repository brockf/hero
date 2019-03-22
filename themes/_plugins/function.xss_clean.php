<?php

/**
* XSS Clean
*
* Cleanse a variable before displaying in Smarty
*
* @param string $string
*
* @return string cleansed $string
*/
function smarty_function_xss_clean ($params, $smarty) {
	return $smarty->CI->security->xss_clean($params['string']);
}