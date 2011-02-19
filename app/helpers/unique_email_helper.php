<?php

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