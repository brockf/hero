<?php

/**
* Paywall
*
* Show the Paywall using the user's current setting
*
* @param array $content Specify variables to pass to the Paywall's $content array
* @param string $type The type of content being redirected
* @param string $url If you want to specify a different url than the current URL, specify it here.  It will be used for login/registration redirects.
*
* @return boolean TRUE if paywall is auto, FALSE if not
*/
function paywall ($content = array(), $type = FALSE, $url = FALSE) {
	if (setting('paywall_auto') == '0') {
		return FALSE;
	}
	
	$CI =& get_instance();
	
	if (!empty($content)) {
		$CI->smarty->assign('content', $content);
	}
	
	if (!empty($type)) {
		$CI->smarty->assign('type', $type);
	}
	
	$CI->smarty->display(setting('paywall_template'));
}