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
	var $active_user;  // the logged-in use
	
	function __construct()
	{
		parent::CI_Model();
		
		// check for session
        if ($this->session->userdata('user_id') != '') {
        	$this->set_active($this->session->userdata('user_id'));
        }
	}
	
	function login ($username, $password) {
		$this->db->where('(`user_username` = \'' . $username . '\' or `user_email` = \'' . $username . '\')');
		$this->db->where('user_password',md5($password));
		$this->db->where('user_suspended','0');
		$this->db->where('user_deleted','0');
		$query = $this->db->get('users');
		
		if ($query->num_rows() > 0) {
			$user = $query->row_array();
			$user = $this->get_user($user['user_id']);
		}
		else {
			return FALSE;
		}
    	
    	$this->session->set_userdata('user_id',$user['id']);
    	$this->session->set_userdata('login_time',now());
		
		$this->set_active($user['id']); 
		
		return TRUE;
    }
    
    function logout () {
    	$this->session->unset_userdata('user_id','login_time');
    	
    	return TRUE;
    }
    
    function set_active ($user_id) {
    	if (!$user = $this->get_user($user_id)) {
    		return FALSE;
    	}
    	
    	$this->active_user = $user;
    	
    	return TRUE;
    }
    
    function is_admin () {
    	if (empty($this->active_user) or $this->active_user['is_admin'] == FALSE) {
    		return FALSE;
    	}
    	
    	return TRUE;
    }
    
    function logged_in () {
    	if (empty($this->active_user)) {
    		return FALSE;
    	}
    	else {
    		return TRUE;
    	}
    }
    
    function get ($parameter = FALSE) {
    	if ($parameter) {
    		return $this->active_user[$parameter];
    	}
    	else {
    		return $this->active_user;
    	}
    }
	
	/*
	* Validation
	*
	* Validates POST data to be acceptable for creating a new user
	*
	* @param boolean $editing Set to TRUE if this is an edited user (i.e., password can be blank)
	*
	* @return array|boolean If errors, returns an array of individual errors, else returns TRUE
	*/
	function validation ($editing = FALSE) {
		$this->load->library('form_validation');
		$this->load->model('custom_fields_model');
		$this->load->helpers(array('unique_username','unique_email'));
		
		$this->form_validation->set_rules('first_name','First Name','trim|required');
		$this->form_validation->set_rules('last_name','Last Name','trim|required');
		$unique_email = ($editing == FALSE) ? '|unique_email' : '';
		$this->form_validation->set_rules('email','Email','trim' . $unique_email . '|valid_email');
		$unique_username = ($editing == FALSE) ? 'unique_username|' : '';
		$this->form_validation->set_rules('username','Username','trim|alphanumeric' . $unique_username);
		
		if ($editing == FALSE) {
			$this->form_validation->set_rules('password','Password','min_length[5]|matches[password2]');
		}
		
		// get custom field rules for field group #1 (user fields)
		$rules = $this->custom_fields_model->get_validation_rules('1');
		$this->form_validation->set_rules($rules);
		
		if ($this->form_validation->run() == FALSE) {
			$errors = rtrim(validation_errors('','||'),'|');
			$errors = explode('||',str_replace('<p>','',$errors));
			return $errors;
		}
		
		// file validation
		if ($this->custom_fields_model->validate_files('1') == FALSE) {
			return array('File upload has an invalid extension.');
		}
		else {
			return TRUE;
		}
	}
	
	/*
	* Is email address unique?
	*
	* @param string $email The email address being tested
	*
	* @return boolean TRUE upon being OK, FALSE if not
	*/
	function unique_email ($email) {
		$this->db->select('user_id');
		$this->db->where('user_email',$email);
		$this->db->where('user_deleted','0');
		$result = $this->db->get('users');
		
		if ($result->num_rows() > 0) {
			return FALSE;
		}
		else {
			return TRUE;
		}
	}
	
	/*
	* Is username unique?
	*
	* @param string $username The username being tested
	*
	* @return boolean TRUE upon being OK, FALSE if not
	*/
	function unique_username ($username) {
		// protected usernames
		$protected = array('admin','administrator','root','','1','2','3','4','5','6','7','8','9');
		if (in_array($username, $protected)) {
			return FALSE;
		}
	
		$this->db->select('user_id');
		$this->db->where('user_username',$username);
		$this->db->where('user_deleted','0');
		$result = $this->db->get('users');
		
		if ($result->num_rows() > 0) {
			return FALSE;
		}
		else {
			return TRUE;
		}
	}
	
	/*
	* Remove a Usergroup
	*
	* @param int $user_id
	* @param int $group_id
	*
	* @return array New usergroup array
	*/
	
	function remove_group ($user_id, $group_id) {
		$user = $this->get_user($user_id);
		
		foreach ($user['usergroups'] as $key => $val) {
			if ($val == $group_id) {
				unset($user['usergroups'][$key]);
			}
		}
		
		$usergroups = '|' . implode('|',$user['usergroups']) . '|';
		
		$this->db->update('users',array('user_groups' => $usergroups),array('user_id' => $user_id));
		
		return $usergroups;
	}
	
	/*
	* New User
	*
	* Create a new user, including custom fields
	*
	* @param string $email Email Address
	* @param string $password Password to use
	* @param string $username Username
	* @param string $first_name First name
	* @param string $last_name Last name
	* @param array $groups Array of group ID's to be entered into
	* @param int $affiliate Affiliate ID of referrer
	* @param boolean $is_admin Check to make an administrator
	* @param array $custom_fields An array of custom field data, matching in name
	*
	* @return int $user_id
	*/
	function new_user($email, $password, $username, $first_name, $last_name, $groups = FALSE, $affiliate = FALSE, $is_admin = FALSE, $custom_fields = array()) {
		if (empty($groups)) {
			$this->load->model('users/usergroup_model');
			
			$group = $this->usergroup_model->get_default();
			
			$groups = array($group);
		}
		
		$insert_fields = array(
								'user_is_admin' => ($is_admin == TRUE) ? '1' : '0',
								'user_groups' => '|' . implode('|',$groups) . '|',
								'user_first_name' => $first_name,
								'user_last_name' => $last_name,
								'user_username' => $username,
								'user_email' => $email,
								'user_password' => md5($password),
								'user_referrer' => ($affiliate != FALSE) ? $affiliate : '0',
								'user_signup_date' => date('Y-m-d H:i:s'),
								'user_last_login' => '0000-00-00 00:00:00',
								'user_suspended' => '0',
								'user_deleted' => '0'
							);
							
		foreach ($custom_fields as $name => $value) {
			$insert_fields[$name] = $value;
		}
												
		$this->db->insert('users',$insert_fields);
		$user_id = $this->db->insert_id();
		
		// create customer record
		$this->load->model('billing/customer_model');
		
		$customer = array();
		$customer['email'] = $email;
		$customer['internal_id'] = $user_id;
		$customer['first_name'] = $first_name;
		$customer['last_name'] = $last_name;
		
		// do any custom fields map to billing fields?
		$user_custom_fields = $this->get_custom_fields();
		
		if (is_array($user_custom_fields)) {
			foreach ($user_custom_fields as $field) {
				if (!empty($field['billing_equiv']) and isset($custom_fields[$field['name']])) {
					$customer[$field['billing_equiv']] = $custom_fields[$field['name']];		
				}
			}
		}
		
		$customer_id = $this->customer_model->NewCustomer($customer);
		
		$this->db->update('users',array('customer_id' => $customer_id),array('user_id' => $user_id));
		
		// trip the email
		TriggerTrip('new_member',FALSE,FALSE,$customer_id,FALSE,array('password' => $password));
		
		return $user_id;
	}
	
	/*
	* Update User
	*
	* Updates a user, including custom fields
	*
	* @param int $user_id The current user ID #
	* @param string $email Email Address
	* @param string $password Password to use
	* @param string $username Username
	* @param string $first_name First name
	* @param string $last_name Last name
	* @param array $groups Array of group ID's to be entered into
	* @param boolean $is_admin Check to make an administrator
	* @param array $custom_fields An array of custom field data, matching in name
	*
	* @return int $user_id
	*/
	function update_user($user_id, $email, $password, $username, $first_name, $last_name, $groups = FALSE, $is_admin = FALSE, $custom_fields = array()) {
		$update_fields = array(
								'user_is_admin' => ($is_admin == TRUE) ? '1' : '0',
								'user_groups' => '|' . implode('|',$groups) . '|',
								'user_first_name' => $first_name,
								'user_last_name' => $last_name,
								'user_username' => $username,
								'user_email' => $email
							);
							
		if (!empty($password)) {
			$update_fields['user_password'] = md5($password);
		}
							
		foreach ($custom_fields as $name => $value) {
			$update_fields[$name] = $value;
		}
												
		$this->db->update('users',$update_fields,array('user_id' => $user_id));
		
		return TRUE;
	}
	
	/*
	* Delete User
	*
	* @param int $user_id The user ID #
	*
	* @param boolean TRUE
	*/
	function delete_user ($user_id) {
		$this->db->update('users',array('user_deleted' => '1'),array('user_id' => $user_id));
		return TRUE;
	}
	
	/*
	* Suspend User
	*
	* @param int $user_id The user ID #
	*
	* @param boolean TRUE
	*/
	function suspend_user ($user_id) {
		$this->db->update('users',array('user_suspended' => '1'),array('user_id' => $user_id));
		return TRUE;
	}
	
	/*
	* Unsuspend User
	*
	* @param int $user_id The user ID #
	*
	* @param boolean TRUE
	*/
	function unsuspend_user ($user_id) {
		$this->db->update('users',array('user_suspended' => '0'),array('user_id' => $user_id));
		return TRUE;
	}
	
	/*
	* Get User
	*
	* @param int $user_id The user ID #
	*
	* @param array User fields
	*/
	function get_user ($user_id) {
		$filters = array('id' => $user_id);
		
		$user = $this->get_users($filters);
		
		if (empty($user)) {
			return FALSE;
		}
		else {
			return $user[0];
		}
	}
	
	/*
	* Get Users
	*
	* @param int $filters['id'] The user ID to select
	* @param int $filters['group'] The group ID to filter by
	* @param int $filters['suspended'] Set to 1 to retrieve suspended users
	* @param string $filters['email'] The email address to filter by
	* @param string $filters['name'] Search by first and last name
	*
	* @return array Each user in an array of users
	*/
	function get_users ($filters) {
		if (isset($filters['id'])) {
			$this->db->where('user_id',$filters['id']);
		}
		if (isset($filters['group'])) {
			$this->db->where('(user_groups LIKE \'%|' . $filters['group'] . '|%\' or user_groups = \'|' . $filters['group'] . '|\')');
		}
		if (isset($filters['suspended'])) {
			$this->db->where('user_suspended',$filters['suspended']);
		}
		if (isset($filters['email'])) {
			$this->db->like('user_email',$filters['email']);
		}
		if (isset($filters['name'])) {
			$this->db->like('user_first_name',$filters['name']);
			$this->db->or_like('user_last_name',$filters['name']);
		}
		
		$this->db->where('user_deleted','0');
		
		// standard ordering and limiting
		$order_by = (isset($filters['sort'])) ? $filters['sort'] : 'user_username';
		$order_dir = (isset($filters['sort_dir'])) ? $filters['sort_dir'] : 'ASC';
		$this->db->order_by($order_by, $order_dir);
		
		if (isset($filters['limit'])) {
			$offset = (isset($filters['offset'])) ? $filters['offset'] : 0;
			$this->db->limit($filters['limit'], $offset);
		}
		
		$result = $this->db->get('users');
		
		if ($result->num_rows() == 0) {
			return FALSE;
		}
		
		// get custom fields
		$CI =& get_instance();
		$CI->load->model('custom_fields_model');
		$custom_fields = $CI->custom_fields_model->get_custom_fields('1');
		
		$users = array();
		foreach ($result->result_array() as $user) {
			$groups = explode('|',$user['user_groups']);
			$user_groups = array();
			foreach ($groups as $group) {
				if (!empty($group)) {
					$user_groups[] = $group;
				}
			}
		
			$this_user = array(
							'id' => $user['user_id'],
							'is_admin' => ($user['user_is_admin'] == '1') ? TRUE : FALSE,
							'usergroups' => $user_groups,
							'first_name' => $user['user_first_name'],
							'last_name' => $user['user_last_name'],
							'username' => $user['user_username'],
							'email' => $user['user_email'],
							'referrer' => $user['user_referrer'],
							'signup_date' => $user['user_signup_date'],
							'last_login' => ($user['user_last_login'] == '0000-00-00 00:00:00') ? FALSE : $user['user_last_login'],
							'suspended' => $user['user_suspended']
							);
							
			foreach ($custom_fields as $field) {
				$this_user[$field['name']] = $user[$field['name']];
			}
			reset($custom_fields);
							
			$users[] = $this_user;
							
		}
		
		return $users;
	}
	
	function new_custom_field ($name, $type, $options, $default, $width, $help, $billing_equiv = '', $required = FALSE, $validators = array()) {
		$this->load->model('custom_fields_model');
		
		// create custom field to user group
		$custom_field_id = $this->custom_fields_model->new_custom_field('1', $name, $type, $options, $default, $width, $help, $required, $validators, 'users');
		
		$insert_fields = array(
							'custom_field_id' => $custom_field_id,
							'subscription_plans' => '',
							'products' => '',
							'user_field_billing_equiv' => $billing_equiv
							);
							
		$this->db->insert('user_fields',$insert_fields);
		
		return $this->db->insert_id();
	}
	
	function update_custom_field ($user_field_id, $name, $type, $options, $default, $width, $help, $billing_equiv = '', $required = FALSE, $validators = array()) {	
		$this->load->model('custom_fields_model');
		
		// get custom_field_id
		$field = $this->get_custom_field($user_field_id);
		
		// create custom field to user group
		$custom_field_id = $this->custom_fields_model->update_custom_field($field['custom_field_id'], '1', $name, $type, $options, $default, $width, $help, $required, $validators, 'users');
		
		$update_fields = array(
							'subscription_plans' => '',
							'products' => '',
							'user_field_billing_equiv' => $billing_equiv
							);
							
		$this->db->update('user_fields',$update_fields,array('user_field_id' => $user_field_id));
		
		return TRUE;
	}
	
	function delete_custom_field ($user_field_id) {
		// get custom_field_id
		$field = $this->get_custom_field($user_field_id);
		
		$this->load->model('custom_fields_model');
		$this->custom_fields_model->delete_custom_field($field['custom_field_id'], 'users');
		
		$this->db->delete('user_fields',array('user_field_id' => $user_field_id));
		
		return TRUE;
	}
	
	function get_custom_field ($id) {
		$return = $this->get_custom_fields(array('id' => $id));
		
		if (empty($return)) {
			return FALSE;
		}
		
		return $return[0];
	}
	
	function get_custom_fields ($filters = array()) {
		$this->load->model('custom_fields_model');
	
		if (isset($filters['id'])) {
			$this->db->where('user_field_id',$filters['id']);
		}
	
		$this->db->join('custom_fields','custom_fields.custom_field_id = user_fields.custom_field_id','inner');
		$this->db->order_by('custom_fields.custom_field_order','ASC');
		
		$result = $this->db->get('user_fields');
		
		if ($result->num_rows() == 0) {
			return FALSE;
		}
		
		$fields = array();
		foreach ($result->result_array() as $field) {
			$fields[] = array(
							'id' => $field['user_field_id'],
							'custom_field_id' => $field['custom_field_id'],
							'friendly_name' => $field['custom_field_friendly_name'],
							'name' => $field['custom_field_name'],
							'type' => $field['custom_field_type'],
							'options' => (!empty($field['custom_field_options'])) ? unserialize($field['custom_field_options']) : array(),
							'help' => $field['custom_field_help_text'],
							'order' => $field['custom_field_order'],
							'width' => $field['custom_field_width'],
							'default' => $field['custom_field_default'],
							'required' => ($field['custom_field_required'] == 1) ? TRUE : FALSE,
							'validators' => (!empty($field['custom_field_validators'])) ? unserialize($field['custom_field_validators']) : array(),
							'billing_equiv' => $field['user_field_billing_equiv']
						);
		}
		
		return $fields;
	}
}