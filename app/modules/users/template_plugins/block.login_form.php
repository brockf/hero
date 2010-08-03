<?php

/**
* Login Form Template Plugin
*
* Assists in the creation of login forms
*
* @param string $return The relative or absolute URL to return to after logging in
* @param string $username Username value
*/

function smarty_block_login_form ($params, $tagdata, $smarty, $repeat){
	if (!$repeat) {	
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
		
		// username
		$variables['username'] = isset($params['username']) ? $params['username'] : '';
						
		$return .= $smarty->parse_string($tagdata, $variables);
				
		return $return;
	}
}