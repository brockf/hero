<?php

function smarty_function_recaptcha ($params, &$smarty) {
	if ($smarty->CI->config->item('recaptcha_site_key') != '') {
		$output = "<script src='https://www.google.com/recaptcha/api.js' async defer></script>
		<script type='text/javascript'>$('form .form').append(\"<div class='g-recaptcha' data-sitekey='" 
		. $smarty->CI->config->item('recaptcha_site_key') . "'</div>\");</script>";
		//data-sitekey="6Lce0Q4TAAAAAOJXop4460V9fVL3_zg534I8FgGQ"

		return $output;
	}
	else {
		return $ourput;
	}
}