<?php

function unique_username ($username) {
	$CI =& get_instance();
	
	if ($CI->user_model->unique_username($username)) {
		return TRUE;
	}
	else {
		$CI->form_validation->set_message('unique_username', 'The username you have selected is unavailable.');
		return FALSE;
	}
}