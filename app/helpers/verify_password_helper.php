<?php

/**
* Verify Password
*
* A form validation routine:  Validate the user's password.
*
* @param string $password
* @return boolean
*
* @copyright Electric Function, Inc.
* @package Hero Framework
* @author Electric Function, Inc.
*/
function verify_password ($password) {
	$CI =& get_instance();
	
	$verified = FALSE;
	
	$CI->db->where('user_id',$CI->user_model->get('id'));
	$result = $CI->db->get('users');
	
	if ($result->num_rows() == 1) {
		$user = $result->row_array();
	
		$hashed_password = ($user['user_salt'] == '') ? md5($password) : md5($password . ':' . $user['user_salt']);
			
		if ($hashed_password == $user['user_password']) {
			$verified = TRUE;
		}
	}
	
	if ($verified === FALSE) {
		$CI->form_validation->set_message('verify_password', 'Your %s is incorrect - we were unable to verify your account.');
		
		return FALSE;
	}
}