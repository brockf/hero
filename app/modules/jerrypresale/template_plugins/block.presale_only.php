<?php

/**
* Presale Only
*
* Don't display this content if the user is restricted to the presale site
*
*/

function smarty_block_presale_only ($params, $tagdata, $smarty, $repeat){
	if (!$repeat) {		
		if (!defined('PRESALE_ONLY')) {
			return $tagdata;
		}
	}
}