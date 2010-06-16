<?php

/**
* User Model 
*
* Contains all the methods used to create, update, and delete users.
*
* @version 1.0
* @author Electric Function, Inc.
* @package Electric Publisher

*/

class User_model extends CI_Model
{
	var $CI;
	
	function __construct()
	{
		parent::CI_Model();
		
		$this->CI =& get_instance();
	}
	
	function new_user($email, $password, $username, $first_name, $last_name, $referrer, $groups = FALSE, $affiliate = FALSE, $is_admin = FALSE) {
		if (empty($groups)) {
			$this->CI->load->model('usergroup_model');
			
			$group = $this->CI->usergroup_model->GetDefault();
			
			$groups = array($group);
		}
		
		$insert_fields = array(
								'user_is_admin' => ($is_admin == TRUE) ? '1' : '0',
								'user_groups' => implode('|',$groups),
								'user_first_name' => $first_name,
								'user_last_name' => $last_name,
								'user_username' => $username,
								'user_email' => $email,
								'user_password' => $password,
								'user_referrer' => ($affiliate != FALSE) ? $affiliate : '0',
								'user_signup_date' => date('Y-m-d H:i:s'),
								'user_last_login' => '0000-00-00 00:00:00',
								'user_suspended' => '0',
								'user_deleted' => '0'
							);
												
		$this->db->insert('users',$insert_fields);
		
		return $this->db->insert_id();
	}
}