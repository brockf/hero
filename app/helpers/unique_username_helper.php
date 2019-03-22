<?php

/**
* Unique Username
*
* A form validation rule: Is this username unique in the system?
*
* @param string $username
* @return boolean
*
* @copyright Electric Function, Inc.
* @package Hero Framework
* @author Electric Function, Inc.
*/
function unique_username ($username) {
	$CI =& get_instance();
	
	if ($CI->user_model->unique_username($username)) {
		return TRUE;
	}
	else {
		$CI->form_validation->set_message('unique_username', 'The Username you have selected is unavailable.');
		return FALSE;
	}
}