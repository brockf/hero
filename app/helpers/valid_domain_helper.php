<?php

/**
* Valid Domain
*
* A form validation rule:  Is this a valid domain like example.com?
*
* @param string $domain
* @return boolean
*
* @copyright Electric Function, Inc.
* @package Hero Framework
* @author Electric Function, Inc.
*/
function valid_domain ($domain) {
	$CI =& get_instance();
	
	if (!preg_match('/^[a-zA-Z0-9-]+\.[a-zA-Z.]{2,5}$/i', $domain)) {
		$CI->form_validation->set_message('valid_domain', '%s must be a valid domain.');
		
		return FALSE;
	}
	else {
		return TRUE;
	}
}