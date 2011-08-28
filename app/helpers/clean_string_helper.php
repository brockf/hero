<?php

/**
* Clean String
*
* Remove all non-URL friendly characters from a string and return it.
*
* @param string $text
* @return string
*
* @copyright Electric Function, Inc.
* @package Hero Framework
* @author Electric Function, Inc.
*/
function clean_string ($text) {
	$clean = $text;
	$clean = preg_replace("/[^a-zA-Z0-9\_\s]/", '', $clean);
	$clean = str_replace(' ','_',$clean);
	$clean = strtolower($clean);
	$clean = preg_replace('/_+/i','_',$clean);
	
	return $clean;
}