<?php

/**
* Prefilter Smarty Parse Filter
*
* This is a file that simply calls the CI_Smarty::pre_smarty_parse function
* It keeps the code clean
*/
function smarty_prefilter_pre_smarty_parse ($source, $smarty)
{
	return $smarty->pre_smarty_parse($source);
}