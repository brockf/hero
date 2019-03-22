<?php

/**
* Login Form Template Plugin
*
* Assists in the creation of login forms
*
* @param string $return The relative or absolute URL to return to after logging in
* @param string $username Username value
*/

function smarty_block_login_form ($params, $tagdata, &$smarty, &$repeat){
	if (!isset($params['var'])) {
		show_error('You must specify a "var" parameter for template {login_form} calls.  This parameter specifies the variable name for the returned array.');
	}
	else {
		$variables = array();
		
		// get return URL
		if (isset($params['return']) and !empty($params['return'])) {
			$variables['return'] = query_value_encode($params['return']);
		}
		else {
			$variables['return'] = '';
		}
		
		// form action
		$variables['form_action'] = site_url('users/post_login');
		
		if (setting('ssl_certificate') == '1') {
			$variables['form_action'] = secure($variables['form_action']);
		}
		
		// username
		$variables['username'] = isset($params['username']) ? $params['username'] : '';
						
		$smarty->assign($params['var'], $variables);
				
		echo $tagdata;
	}
}