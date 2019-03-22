<?php

/**
* Not In Group Template Plugin
*
* Are they not in any of these groups?
*
* @param string $privileges A standard privileges array containing usergroup ID's
*/

function smarty_block_not_in_group ($params, $tagdata, &$smarty, &$repeat){
	if (!$repeat) {	
		if ($smarty->CI->user_model->not_in_group($params['privileges'])) {
			return $tagdata;
		}
		else {
			return '';
		}
	}
}