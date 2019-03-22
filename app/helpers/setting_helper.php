<?php

/**
* Setting
*
* Loads an item by name from the app config array (which includes all site settings from `settings`)
*
* @param string $name The name of the config/setting item
* @return string The Value
*
* @copyright Electric Function, Inc.
* @package Hero Framework
* @author Electric Function, Inc.
*/
function setting ($name) {
	$CI =& get_instance();
	
	return $CI->config->item($name);
}