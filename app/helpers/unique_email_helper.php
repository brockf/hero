<?php

/**
* Unique Email
*
* A form validation rule: Is this email unique in the system?
*
* @param string $email
* @return boolean
*
* @copyright Electric Function, Inc.
* @package Hero Framework
* @author Electric Function, Inc.
*/
function unique_email ($email) {
	$CI =& get_instance();

	if ($CI->user_model->unique_email($email)) {
		
		return TRUE;
	}
	else {
		$CI->form_validation->set_message('unique_email', 'The Email you have selected is unavailable.');
			
		return FALSE;
	}
}