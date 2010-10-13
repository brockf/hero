<?php

/**
* User Model 
*
* Contains all the methods used to create, update, login, logout, and delete users.
*
* @author Electric Function, Inc.
* @copyright Electric Function, Inc.
* @package Electric Publisher
*
*/

class User_model extends CI_Model
{
	var $active_user;  // the logged-in use
	var $failed_due_to_activation; // if the login failed to the account not being activated, this == TRUE
	private $cache_fields;
	
	function __construct()
	{
		parent::CI_Model();
		
		// check for session
        if ($this->session->userdata('user_id') != '') {
        	// load active user into cache for future ->Get() calls
        	$this->set_active($this->session->userdata('user_id'));
        	
        	// handle a potential card
        	$CI =& get_instance();
        	$CI->load->model('store/cart_model');
        	$CI->cart_model->save_cart_to_db();
        }
        else {
        	$this->load->helper('cookie');
        	
        	// we may have a remembered user
        	if (get_cookie('user_remember_key',TRUE)) {
        		// does this correspond with a remember key?
        		$this->db->select('user_id');
        		$this->db->where('user_remember_key', get_cookie('user_remember_key', TRUE));
        		$result = $this->db->get('users');
        		
        		if ($result->num_rows() == 0) {
        			// no correspondence, this key has expired
        			delete_cookie('user_remember_key');
        			
        			$this->db->update('users',array('user_remember_key' => ''),array('user_remember_key' => $remember_key));
        		}
        		else {
        			$user = $result->row_array();
        			
        			$this->login_by_id($user['user_id']);
        		}
        	}
        }
	}
	
	/*
	* Login User
	*
	* Logs a user in, sets the $_SESSION, updates the user's last login, and tracks login
	*
	* @param string $username Either the username or email of the user
	* @param string $password Their password
	* @param boolean $remember Remember the user with a cookie to re-log them in at future visits
	*
	* @return boolean FALSE upon failure, TRUE upon success
	*/
	function login ($username, $password, $remember = FALSE) {
		$this->db->where('(`user_username` = \'' . $username . '\' or `user_email` = \'' . $username . '\')');
		$this->db->where('user_password',md5($password));
		$this->db->where('user_suspended','0');
		$this->db->where('user_deleted','0');
		$query = $this->db->get('users');
		
		if ($query->num_rows() > 0) {
			$user = $query->row_array();
			$user = $this->get_user($user['user_id']);
			
			// let's make sure they are activated if it's been more than 1 day
			if (!empty($user['validate_key']) and ((time() - strtotime($user['signup_date'])) > (60*60*24))) {
				$this->failed_due_to_activation = TRUE;
				
				return FALSE;
			}
		}
		else {
			return FALSE;
		}
		
		// track login
		$this->login_by_id($user['id']); 
		
		// remember?
		if ($remember == TRUE) {
			$remember_key = random_string('unique');
			
			$result = $this->db->select('user_id')->where('user_remember_key',$remember_key)->get('users');
			while ($result->num_rows() > 0) {
				$remember_key = random_string('unique');
				
				$result = $this->db->select('user_id')->where('user_remember_key',$remember_key)->get('users');
			}
			
			// create the cookie with the key
			$this->load->helper('cookie');
			
			$cookie = array(
			                   'name'   => 'user_remember_key',
			                   'value'  => $remember_key,
			                   'expire' => (60*60*24*365) // 1 year
			               );
			
			set_cookie($cookie); 
			
			// put key in database
			$this->db->update('users',array('user_remember_key' => $remember_key),array('user_id' => $user['id']));
		}
		
		return TRUE;
    }
    
    /**
    * Login by ID
    *
    * @param int $user_id
    */
    function login_by_id ($user_id) {
    	$CI =& get_instance();
    	$CI->load->model('users/login_model');
		$CI->login_model->new_login($user_id);
		
		$this->db->update('users',array('user_last_login' => date('Y-m-d H:i:s')),array('user_id' => $user_id));
    	
    	$this->session->set_userdata('user_id',$user_id);
    	$this->session->set_userdata('login_time',now());
		
		$this->set_active($user_id);
		
		// cart functions
		$CI =& get_instance();
		$CI->load->model('store/cart_model');
		$CI->cart_model->user_login($this->active_user);
		
		return TRUE;
    }
    
    /*
    * User Logout
    *
    * @return boolean TRUE upon success
    */
    function logout () {
    	$this->session->unset_userdata('user_id','login_time');
    	
		$CI =& get_instance();
		$CI->load->helper('cookie');
		delete_cookie('user_remember_key');
    	
    	return TRUE;
    }
    
    /*
    * Set Active User
    *
    * Sets the active user by ID, loads user data into array
    *
    * @param int $user_id
    *
    * @return boolean TRUE upon success
    */
    function set_active ($user_id) {
    	if (!$user = $this->get_user($user_id)) {
    		return FALSE;
    	}
    	
    	$this->active_user = $user;
    	
    	return TRUE;
    }
    
    /*
    * Is User an Admin?
    *
    * @return boolean TRUE if the current user is an administrator
    */
    function is_admin () {
    	if (empty($this->active_user) or $this->active_user['is_admin'] == FALSE) {
    		return FALSE;
    	}
    	
    	return TRUE;
    }
    
    /*
    * Is user logged in?
    *
    * @return boolean TRUE if user is logged in
    */
    function logged_in () {
    	if (empty($this->active_user)) {
    		return FALSE;
    	}
    	else {
    		return TRUE;
    	}
    }
    
    /*
    * Is user in this group?
    *
    * Returns TRUE if the user is in the usergroup(s) or the privileges are open to all (i.e., array is empty or contains "0"). 
    * To check if the user is logged out, send "-1" by itself or in an array.  It's return TRUE if the user is logged out.
    *
    * @param int|array A group ID, or array of group ID's (they must be in one of the groups)
    * @param int $user_id (Optional) Specify the user.  Default: Current User
    *
    * @return boolean TRUE if in the group
    */
    function in_group ($group, $user_id = FALSE) {
    	if (empty($group)) {
    		return TRUE;
    	}
    	
    	// sometimes, we only want to show something if the user is logged out
    	if ($group == '-1' or (is_array($group) and in_array('-1', $group))) {
    		if ($this->logged_in() === FALSE) {
	    		return TRUE;
	    	}
	    	else {
	    		return FALSE;
	    	}
	    }
    
    	if ($user_id) {
    		$user_array = $this->get_user($user_id);
    	}
    	else {
    		$user_array = $this->active_user;
    	}
    	
    	if (is_array($group) and in_array('0', $group)) {	
    		// this is a "privileges" array and it's public so anyone can see it
    		return TRUE;
    	}
    	
    	if (!$this->logged_in()) {
    		// we aren't even logged in
    		
    		return FALSE;
    	}
    
    	if (is_array($group)) {
			// are they in any of these groups?
    		foreach ($group as $one_group) {
    			if (in_array($one_group, $user_array['usergroups'])) {
    				return TRUE;
    			}
    		}
    	}
    	else {
    		// are they in this group?
    		if (in_array($group, $user_array['usergroups'])) {
    			return TRUE;
    		}
    	}
    	
    	// nope
    	return FALSE;
    }
    
    /*
    * Is user NOT in this group?
    *
    * @param int|array A group ID, or array of group ID's (they must NOT be in any of the groups)
    * @param int $user_id (Optional) Specify the user.  Default: Current User
    *
    * @return boolean TRUE if not in the group
    */
    function not_in_group ($group, $user_id = FALSE) {
    	if (empty($group)) {
    		return FALSE;
    	}
    
    	if ($user_id) {
    		$user_array = $this->get_user($user_id);
    	}
    	else {
    		$user_array = $this->active_user;
    	}
    	
    	if (!$this->logged_in()) {
    		// we aren't even logged in
    		
    		return TRUE;
    	}
    	
    	if (is_array($group)) {
			// are they in any of these groups?
    		foreach ($group as $one_group) {
    			if (in_array($one_group, $user_array['usergroups'])) {
    				return FALSE;
    			}
    		}
    	}
    	else {
    		// are they in this group?
    		if (in_array($group, $user_array['usergroups'])) {
    			return FALSE;
    		}
    	}
    	
    	// nope, they aren't in any of them
    	return TRUE;
    }
    
    /*
    * Get user data
    *
    * @param string $parameter The name of the piece of user data (e.g., email)
    *
    * @return string User data
    */
    function get ($parameter = FALSE) {
    	if ($parameter) {
    		return $this->active_user[$parameter];
    	}
    	else {
    		return $this->active_user;
    	}
    }
    
    /*
    * Get Active Subscriptions
    *
    * Gets active subscriptions for a user
    *
    * @param int $user_id The user id
    *
    * @param boolean|array Array of active subscriptions, else FALSE if none exist
    */
    
    function get_active_subscriptions ($user_id) {
    	$this->load->model('billing/recurring_model');
    	
    	$customer_id = $this->get_customer_id($user_id);
    	
    	return $this->recurring_model->GetRecurrings(array('customer_id' => $customer_id));
    }
    
    /*
    * Get Subscriptions
    *
    * Gets active and cancelled subscriptions for a user
    *
    * @param int $user_id The user id
    *
    * @param boolean|array Array of subscriptions, else FALSE if none exist
    */
    
    function get_subscriptions ($user_id) {
    	$this->load->model('billing/subscription_model');
    	
    	return $this->subscription_model->get_subscriptions(array('user_id' => $user_id), TRUE);
    }
    
    /*
    * Get Customer ID
    * 
    * Sometimes, we just need this, so let's not do a full blown query.
    *
    * @param int $user_id
    *
    * @return int $customer_id
    */
    function get_customer_id ($user_id) {
    	$this->db->select('customer_id');
    	$this->db->where('user_id',$user_id);
    	$result = $this->db->get('users');
    	
    	if ($result->num_rows() == 0) {
    		return FALSE;
    	}
    	else {
    		$user = $result->row_array();
    		
    		return isset($user['customer_id']) ? $user['customer_id'] : FALSE;
    	}
    }
    
    /**
    * Set Charge ID
    *
    * After a successful order, we put this charge ID in the user database so that when the
    * charge trigger is tripped, we'll process this user's cart
    *
    * @param int $user_id
    * @param int $charge_id
    *
    * @return boolean TRUE
    */
    function set_charge_id ($user_id, $charge_id) {
    	$this->db->update('users', array('user_pending_charge_id' => $charge_id), array('user_id' => $user_id));
    	
    	return TRUE;
    }
    
    /**
    * Remove Charge ID
    * 
    * @param int $user_id
    *
    * @return boolean TRUE
    */
    function remove_charge_id ($user_id) {
    	$this->db->update('users', array('user_pending_charge_id' => ''), array('user_id' => $user_id));
    	
    	return TRUE;
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
	
	/**
	* Validate Billing Address
	*
	* @param int $user_id
	*
	* @return boolean TRUE if they have a valid billing address on file
	*/
	function validate_billing_address ($user_id) {
		$address = $this->get_billing_address($user_id);
		
		$required = array(
							'first_name',
							'last_name',
							'address_1',
							'city',
							'country',
							'postal_code'
						);
						
		foreach ($required as $item) {
			if (empty($address[$item])) {
				return FALSE;
			}
		}
		
		return TRUE;
	}
	
	/**
	* Get Billing Address
	*
	* @param int $user_id
	*
	* @return array Billing address
	*/
	function get_billing_address ($user_id) {
		$customer_id = $this->get_customer_id($user_id);
		
		if (empty($customer_id)) {
			return FALSE;
		}
		
		$CI =& get_instance();
		$CI->load->model('billing/customer_model');
		
		$customer = $this->customer_model->GetCustomer($customer_id);
		
		$address = array(
						'first_name' => $customer['first_name'],
						'last_name' => $customer['last_name'],
						'company' => $customer['company'],
						'address_1' => $customer['address_1'],
						'address_2' => $customer['address_2'],
						'city' => $customer['city'],
						'country' => $customer['country'],
						'postal_code' => $customer['postal_code'],
						'state' => $customer['state']
					);
		
		return $address;
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
	* Add a Usergroup
	*
	* @param int $user_id
	* @param int $group_id
	*
	* @return array New usergroup array
	*/
	
	function add_group ($user_id, $group_id) {
		$user = $this->get_user($user_id);
		
		if (in_array($group_id, $user['usergroups'])) {
			// already a member
			return FALSE;
		}
		
		$user['usergroups'][] = $group_id;
		
		$usergroups = '|' . implode('|',$user['usergroups']) . '|';
		
		$this->db->update('users',array('user_groups' => $usergroups),array('user_id' => $user_id));
		
		return $usergroups;
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
	* @param boolean $require_validation Should we require email validation?
	*
	* @return int $user_id
	*/
	function new_user($email, $password, $username, $first_name, $last_name, $groups = FALSE, $affiliate = FALSE, $is_admin = FALSE, $custom_fields = array(), $require_validation = FALSE) {
		if (empty($groups)) {
			$this->load->model('users/usergroup_model');
			
			$group = $this->usergroup_model->get_default();
			
			$groups = array($group);
		}
		
		if ($require_validation == TRUE) {
			$validate_key = random_string('unique');
			
			$result = $this->db->select('user_id')->where('user_validate_key',$validate_key)->get('users');
			while ($result->num_rows() > 0) {
				$validate_key = random_string('unique');
				
				$result = $this->db->select('user_id')->where('user_validate_key',$validate_key)->get('users');
			}
		}
		else {
			$validate_key = '';
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
								'user_deleted' => '0',
								'user_remember_key' => '',
								'user_validate_key' => $validate_key
							);
		
		if (is_array($custom_fields)) {					
			foreach ($custom_fields as $name => $value) {
				$insert_fields[$name] = $value;
			}
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
		
		// prep hook
		$this->app_hooks->data('member', $user_id);
		$this->app_hooks->data_var('password', $password);
		
		// trip the validation email?
		if (!empty($validate_key)) {
			$validation_link = site_url('users/validate/' . $validate_key);
			
			$this->app_hooks->data_var('validation_link', $validation_link);
			$this->app_hooks->data_var('validation_code', $validate_key);
			
			$this->app_hooks->trigger('member_validate_email');
		}
		
		$this->app_hooks->trigger('member_register', $user_id);
		$this->app_hooks->reset();
		
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
		
		// update email in customers table
		$this->db->update('customers',array('email' => $email),array('internal_id' => $user_id));
		
		// do any custom fields map to billing fields?
		$user_custom_fields = $this->get_custom_fields();
		
		$customer = array();
		if (is_array($user_custom_fields)) {
			foreach ($user_custom_fields as $field) {
				if (!empty($field['billing_equiv']) and isset($custom_fields[$field['name']])) {
					$customer[$field['billing_equiv']] = $custom_fields[$field['name']];		
				}
			}
		}
		
		$customer_id = $this->get_customer_id($user_id);
		
		if (!empty($customer)) {
			$this->db->update('customers', $customer, array('internal_id' => $user_id));
		}
		
		return TRUE;
	}
	
	/**
	* Update Billing Address
	*
	* @param $address_fields New Address
	*
	* @return TRUE
	*/
	function update_billing_address ($user_id, $address_fields) {
		$customer_id = $this->get_customer_id($user_id);
		
		$CI =& get_instance();
		$CI->load->model('billing/customer_model');
		$CI->customer_model->UpdateCustomer($customer_id, $address_fields);
		
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
		
		// hook call
		$this->app_hooks->data('member', $user_id);
		$this->app_hooks->trigger('member_delete');
		$this->app_hooks->reset();
		
		return TRUE;
	}
	
	/**
	* Update Password
	*
	* @param int $user_id
	* @param string $new_password
	*
	* @return TRUE
	*/
	function update_password ($user_id, $new_password) {
		$this->db->update('users',array('user_password' => md5($new_password)),array('user_id' => $user_id));
		
		return TRUE;
	}
	
	/**
	* Reset Password
	*
	* @param int $user_id
	*
	* @return boolean TRUE
	*/
	function reset_password ($user_id) {
		$user = $this->get_user($user_id);
		
		if (empty($user)) {
			return FALSE;
		}
		
		// reset the password
		$this->load->helper('string');
		
		$password = random_string('alnum',9);
		$this->db->update('users',array('user_password' => md5($password)),array('user_id' => $user['id']));
	
		// hook call
		$this->app_hooks->data('member', $user['id']);
		$this->app_hooks->data_var('new_password', $password);
		
		$this->app_hooks->trigger('member_forgot_password');
		$this->app_hooks->reset();
		
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
		
		// hook call
		$this->app_hooks->data('member', $user_id);
		$this->app_hooks->trigger('member_suspend');
		$this->app_hooks->reset();
		
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
		
		// hook call
		$this->app_hooks->data('member', $user_id);
		$this->app_hooks->trigger('member_suspend');
		$this->app_hooks->reset();
		
		return TRUE;
	}
	
	/*
	* Get User
	*
	* @param int $user_id The user ID #
	*
	* @param array User fields
	*/
	function get_user ($user_id, $any_status = FALSE) {
		$filters = array('id' => $user_id);
		
		$user = $this->get_users($filters, $any_status);
		
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
	* @param string $filters['sort'] Field to sort by
	* @param string $filters['sort_dir'] ASC or DESC
	* @param int $filters['limit'] How many records to retrieve
	* @param int $filters['offset'] Start records retrieval at this record
	*
	* @return array Each user in an array of users
	*/
	function get_users ($filters = array(), $any_status = FALSE) {
		$fields = $this->get_custom_fields();
		
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
		
		if (isset($filters['username'])) {
			$this->db->like('user_username',$filters['username']);
		}
		
		if (isset($filters['name'])) {
			if (is_numeric($filters['name'])) {
				// we are passed a member id
				$this->db->where('users.user_id',$filters['name']);
			} else {
				$this->db->like('user_first_name',$filters['name']);
				$this->db->or_like('user_last_name',$filters['name']);
			}
		}
		
		if (isset($filters['first_name'])) {
			$this->db->like('user_first_name',$filters['first_name']);
		}
		
		if (isset($filters['last_name'])) {
			$this->db->like('user_last_name',$filters['last_name']);
		}
		
		if (isset($filters['is_admin'])) {
			$this->db->where('users.user_is_admin',$filters['is_admin']);
		}
		
		if (isset($filters['signup_date_start'])) {
			$date = date('Y-m-d H:i:s', strtotime($filters['signup_date_start']));
			$this->db->where('users.user_signup_date >=', $date);
		}
		
		if (isset($filters['signup_date_end'])) {
			$date = date('Y-m-d H:i:s', strtotime($filters['signup_date_end']));
			$this->db->where('users.user_signup_date <=', $date);
		}
		
		// custom field params
		foreach ($fields as $field) {
			if (isset($filters[$field['name']])) {
				$this->db->like('users.' . $field['name'],'%' . $filters[$field['name']] . '%');
			}
		}
		
		if ($any_status == FALSE) {
			$this->db->where('user_deleted','0');
		}
		
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
		$custom_fields = $CI->custom_fields_model->get_custom_fields(array('group' => '1'));
		
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
							'customer_id' => $user['customer_id'],
							'usergroups' => $user_groups,
							'first_name' => $user['user_first_name'],
							'last_name' => $user['user_last_name'],
							'username' => $user['user_username'],
							'email' => $user['user_email'],
							'referrer' => $user['user_referrer'],
							'signup_date' => local_time($user['user_signup_date']),
							'last_login' => local_time($user['user_last_login']),
							'suspended' => ($user['user_suspended'] == 1) ? TRUE : FALSE,
							'admin_link' => site_url('admincp/users/profile/' . $user['user_id']),
							'remember_key' => $user['user_remember_key'],
							'validate_key' => $user['user_validate_key'],
							'cart' => (empty($user['user_cart'])) ? FALSE : unserialize($user['user_cart']),
							'pending_charge_id' => (!empty($user['user_pending_charge_id'])) ? $user['user_pending_charge_id'] : FALSE
							);
							
			foreach ($custom_fields as $field) {
				$this_user[$field['name']] = $user[$field['name']];
			}
			reset($custom_fields);
							
			$users[] = $this_user;
							
		}
		
		return $users;
	}
	
	/*
	* New User Custom Field
	*
	* Create new user custom field record and modifies the database
	*
	* @param string $name The label used for the field (will be converted automatically to a system-friendly name)
	* @param string $type Either "text", "textarea", "password", "wysiwyg", "select", "multiselect", "radio", "checkbox", or "file"
	* @param array|string $options A string of newline-separated values like (test=value\nvalue2, etc.) or an array like array(array(name => 'test', value => 'test2')) etc.
	* @param string $default Default selected value
	* @param string $width The complete style:width element definition (e.g., "250px" or "50%")
	* @param string $help A string of help text
	* @param string $billing_equiv If this field represents a billing address field (e.g, "address_1"), specify here:  options: address_1/2, state, country, postal_code, company
	* @param boolean $admin_only Is this an admin-only field?
	* @param boolean $registration_form Should we show this in the registration form?
	* @param boolean $required TRUE to require the field for submission
	* @param array $validators One or more validators values in an array: whitespace, email, alphanumeric, numeric, domain
	* @param string|boolean $db_table The database table to add the field to, else FALSE
	*
	* @return int $custom_field_id
	*/
	function new_custom_field ($name, $type, $options, $default, $width, $help, $billing_equiv = '', $admin_only = FALSE, $registration_form = TRUE, $required = FALSE, $validators = array()) {
		$this->load->model('custom_fields_model');
		
		// create custom field to user group
		$custom_field_id = $this->custom_fields_model->new_custom_field('1', $name, $type, $options, $default, $width, $help, $required, $validators, 'users');
		
		$insert_fields = array(
							'custom_field_id' => $custom_field_id,
							'subscription_plans' => '',
							'products' => '',
							'user_field_billing_equiv' => $billing_equiv,
							'user_field_admin_only' => ($admin_only == TRUE) ? '1' : '0',
							'user_field_registration_form' => ($registration_form == TRUE) ? '1' : '0'
							);
							
		$this->db->insert('user_fields',$insert_fields);
		
		return $this->db->insert_id();
	}
	
	/*
	* Update User Custom Field
	*
	* Updates custom field records as well as modifies the appropriate database
	*
	* @param int $user_field_id The custom field ID to edit
	* @param string $name The label used for the field (will be converted automatically to a system-friendly name)
	* @param string $type Either "text", "textarea", "password", "wysiwyg", "select", "multiselect", "radio", "checkbox", or "file"
	* @param array|string $options A string of newline-separated values like (test=value\nvalue2, etc.) or an array like array(array(name => 'test', value => 'test2')) etc.
	* @param string $default Default selected value
	* @param string $width The complete style:width element definition (e.g., "250px" or "50%")
	* @param string $help A string of help text
	* @param string $billing_equiv If this field represents a billing address field (e.g, "address_1"), specify here:  options: address_1/2, state, country, postal_code, company
	* @param boolean $admin_only Is this an admin-only field?
	* @param boolean $registration_form Should we show this in the registration form?
	* @param boolean $required TRUE to require the field for submission
	* @param array $validators One or more validators values in an array: whitespace, email, alphanumeric, numeric, domain
	* @param string|boolean $db_table The database table to add the field to, else FALSE
	*
	* @return boolean TRUE
	*/
	function update_custom_field ($user_field_id, $name, $type, $options, $default, $width, $help, $billing_equiv = '', $admin_only = FALSE, $registration_form = TRUE, $required = FALSE, $validators = array()) {	
		$this->load->model('custom_fields_model');
		
		// get custom_field_id
		$field = $this->get_custom_field($user_field_id);
		
		// create custom field to user group
		$custom_field_id = $this->custom_fields_model->update_custom_field($field['custom_field_id'], '1', $name, $type, $options, $default, $width, $help, $required, $validators, 'users');
		
		$update_fields = array(
							'subscription_plans' => '',
							'products' => '',
							'user_field_billing_equiv' => $billing_equiv,
							'user_field_admin_only' => ($admin_only == TRUE) ? '1' : '0',
							'user_field_registration_form' => ($registration_form == TRUE) ? '1' : '0'
							);
							
		$this->db->update('user_fields',$update_fields,array('user_field_id' => $user_field_id));
		
		return TRUE;
	}
	
	/*
	* Delete User Custom Field
	*
	* ge custom field record and modify database
	*
	* @param int $id The ID of the field
	* @param string|boolean The database table to reflect the changes, else FALSE
	*
	* @return boolean TRUE
	*/
	function delete_custom_field ($user_field_id) {
		// get custom_field_id
		$field = $this->get_custom_field($user_field_id);
		
		$this->load->model('custom_fields_model');
		$this->custom_fields_model->delete_custom_field($field['custom_field_id'], 'users');
		
		$this->db->delete('user_fields',array('user_field_id' => $user_field_id));
		
		return TRUE;
	}
	
	/*
	* Get User Custom Field
	*
	* @param int $custom_field_id
	*
	* @return boolean|array $custom_field or FALSE
	*/
	function get_custom_field ($id) {
		$return = $this->get_custom_fields(array('id' => $id));
		
		if (empty($return)) {
			return FALSE;
		}
		
		return $return[0];
	}
	
	/*
	* Get User Custom Fields
	*
	* Retrieves custom fields ordered by custom_field_order
	* 
	* @param int $filters['id'] A custom field ID
	* @param boolean $filters['registration_form'] Set to TRUE to retrieve registration form fields
	* @param boolean $filters['not_in_admin'] Set to TRUE to not retrieve admin-only fields
	*
	* @return array $fields The custom fields
	*/
	function get_custom_fields ($filters = array()) {
		$cache_string = md5(implode(',',$filters));
		
		if (isset($this->cache_fields[$cache_string])) {
			return $this->cache_fields[$cache_string];
		}
	
		$this->load->model('custom_fields_model');
	
		if (isset($filters['id'])) {
			$this->db->where('user_field_id',$filters['id']);
		}
		
		if (isset($filters['registration_form']) and $filters['registration_form'] == TRUE) {
			$this->db->where('user_field_registration_form','1');
		}
		
		if (isset($filters['not_in_admin']) and $filters['not_in_admin'] == TRUE) {
			$this->db->where('user_field_admin_only','0');
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
							'billing_equiv' => $field['user_field_billing_equiv'],
							'admin_only' => ($field['user_field_admin_only'] == '1') ? TRUE : FALSE,
							'registration_form' => ($field['user_field_registration_form'] == '1') ? TRUE : FALSE
						);
		}
		
		$this->cache_fields[$cache_string] = $fields;
		
		return $fields;
	}
}