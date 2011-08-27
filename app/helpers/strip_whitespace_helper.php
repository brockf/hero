<?php

/**
* Strip Whitespace
*
* Remove all whitespace from a string
*
* @copyright Electric Function, Inc.
* @package Electric Framework
* @author Electric Function, Inc.
*/
function strip_whitespace ($param) {
	$param = preg_replace('/\s/s','',$param);
	
	return $param;
}