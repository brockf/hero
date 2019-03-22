<?php

/**
* Query Value Encode
*
* Encode a potentially invalid value for passing in query string
*
* @param string $string The plaintext value of what needs to be encoded
* @return string $string The encoded value
*
* @copyright Electric Function, Inc.
* @package Hero Framework
* @author Electric Function, Inc.
*/
function query_value_encode ($string) {
	return urlencode(base64_encode($string));
}

/**
* Query Value Decode
*
* Decode a value passed in a query string
*
* @param string $string The encoded value
* @return string $string The decoded value
*
* @copyright Electric Function, Inc.
* @package Hero Framework
* @author Electric Function, Inc.
*/
function query_value_decode ($string) {
	return base64_decode(urldecode($string));
}