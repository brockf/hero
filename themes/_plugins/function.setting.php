<?php

/*
* Setting Template Function
*
* Loads a config/setting from the app and displays it
*
* @param $name Name of the setting
*
* @return string $setting value
*/
function smarty_function_setting ($params, $smarty) {
	return setting($params['name']);
}