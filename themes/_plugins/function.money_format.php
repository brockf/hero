<?php

/*
* Money Format Template Function
*
* Formats a value in the locale's money format
*
* @param float $value The currency to format
*
* @return float Formatted money
*/
function smarty_function_money_format ($params, &$smarty, $template) {
	if (!isset($params['value'])) {
		$smarty->trigger_error('You must pass a "value" to the {money_format} template function.');
	}

	return money_format("%!i", $params['value']);
}