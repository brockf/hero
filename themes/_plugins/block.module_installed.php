<?php

/**
* Module Installed
*
* Only display the content between the tags if the named module is installed
*
* @param string $name
*
* @return tagdata or FALSE
*/

function smarty_block_module_installed ($params, $tagdata, &$smarty, &$repeat){
	if (module_installed($params['name'])) {
		return $tagdata;
	}
	else {
		return '';
	}
}