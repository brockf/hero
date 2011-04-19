<?php

function verify_password ($password) {
	$CI =& get_instance();
	
	$CI->db->select('user_id');
	$CI->db->where('user_id',$CI->user_model->get('id'));
	$CI->db->where('user_password',md5($password));
	$result = $CI->db->get('users');
	
	if ($result->num_rows() == 1) {
		return TRUE;
	}
	else {
		$CI->form_validation->set_message('verify_password', 'Your %s is incorrect - we were unable to verify your account.');
		
		return FALSE;
	}
}