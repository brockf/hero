<?php

/**
* User Model
*
* Contains all the methods used to create, update, login, logout, and delete users.
*
* @author Electric Function, Inc.
* @copyright Electric Function, Inc.
* @package Hero Framework
*
*/

class User_model extends CI_Model
{
	public $active_user;  // the logged-in use
	public $failed_due_to_activation; // if the login failed to the account not being activated, this == TRUE
	public $failed_due_to_duplicate_login; // if the login failed because someone is already logged in, this == TRUE

	// this will change if we are in the control panel, as we want to have independent sessions foreach
	// so a user can be logged in the CP but no the frontend (helps for testing - eases confusion)
	private $session_name = 'user_id';

	// should we trigger the member_register hook or create the user silently in new_user()?
	public $trigger_register_hook = TRUE;

	// are we in the control panel?
	private $in_admin = null;

	private $cache_fields;

	function __construct()
	{
		parent::__construct();

		if ($this->in_admin()) {
			// let's use a different session token so that we can independent sessions
			$this->make_admin_session();
		}

		// check for session
        if ($this->session->userdata($this->session_name) != '') {
        	// load active user into cache for future ->Get() calls
        	$this->set_active($this->session->userdata($this->session_name));

        	// no carts in the control panel...
        	if (!$this->in_admin() and module_installed('store')) {
	        	// handle a potential cart
	        	$CI =& get_instance();
	        	$CI->load->model('store/cart_model');
	        	$CI->cart_model->save_cart_to_db();
	        }
        }
        else {
        	// we don't have remember_keys for admins...
        	if (!$this->in_admin()) {
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

	        			$this->db->update('users',array('user_remember_key' => ''),array('user_remember_key' => get_cookie('user_remember_key', TRUE)));
	        		}
	        		else {
	        			$user = $result->row_array();

	        			$this->login_by_id($user['user_id']);
	        		}
	        	}
	        }
        }
	}

	/**
	* CP Check
	*
	* Are we in the control panel?
	*
	* @return boolean
	*/
	private function in_admin () {
		if ($this->in_admin != null) {
			return $this->in_admin;
		}

		$CI =& get_instance();

		$url = $CI->uri->uri_string();
		// it may be at 0 or 1 depending on if we retained the initial slash...
		if (strpos($url, 'admincp') === 0 or strpos($url, 'admincp') === 1) {
			$this->in_admin = TRUE;
		}
		else {
			$this->in_admin = FALSE;
		}

		return $this->in_admin;
	}

	/**
	* Make Admin Session
	*
	* Forces the model into an admin session
	*
	* @return void
	*/
	function make_admin_session () {
		$this->session_name = 'admin_id';
	}

	/**
	* Make Frontend Session
	*
	* Forces the model into a user session
	*
	* @return void
	*/
	function make_frontend_session () {
		$this->session_name = 'user_id';
	}

	/**
	* Login User
	*
	* Logs a user in, sets the $_SESSION, updates the user's last login, and tracks login
	*
	* @param string $username Either the username or email of the user
	* @param string $password Their password
	* @param boolean $remember Remember the user with a cookie to re-log them in at future visits (default: FALSE)
	*
	* @return boolean FALSE upon failure, TRUE upon success
	*/
	public function login ($username, $password, $remember = FALSE) {
		$authenticated = FALSE;

		// stop SQL injection
		$username = addslashes($username);

		$this->db->where('(`user_username` = \'' . $username . '\' or `user_email` = \'' . $username . '\')');
		$this->db->where('user_suspended','0');
		$this->db->where('user_deleted','0');
		$query = $this->db->get('users');

		if ($query->num_rows() > 0) {
			$user_db = $query->row_array();
			$user = $this->get_user($user_db['user_id']);

			$hashed_password = ($user['salt'] == '') ? md5($password) : md5($password . ':' . $user['salt']);

			if ($hashed_password == $user_db['user_password']) {
				$authenticated = TRUE;
			}
		}

		if ($authenticated === TRUE) {
			if ($this->config->item('simultaneous_login_prevention') == '1') {
				// let's make sure someone isn't logged into the account right now
				$this->db->where('user_id',$user['id']);
				$this->db->where('user_activity_date >',date('Y-m-d H:i:s', time() - 60));
				$result = $this->db->get('user_activity');
				if ($result->num_rows() > 0) {
					$this->failed_due_to_duplicate_login = TRUE;

					return FALSE;
				}
			}

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
		$this->login_by_id($user['id'], $password);

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
    *
    * @return boolean
    */
    function login_by_id ($user_id, $password = FALSE) {
    	$CI =& get_instance();
    	$CI->load->model('users/login_model');
		$CI->login_model->new_login($user_id);

		$this->db->update('users',array('user_last_login' => date('Y-m-d H:i:s')),array('user_id' => $user_id));

    	$this->session->set_userdata($this->session_name,$user_id);
    	$this->session->set_userdata('login_time',now());

		$this->set_active($user_id);

		// track activity
		$this->db->insert('user_activity', array('user_id' => $user_id, 'user_activity_date' => date('Y-m-d H:i:s')));

		// cart functions
		if (module_installed('store/cart_model')) {
			$CI =& get_instance();
			$CI->load->model('store/cart_model');
			$CI->cart_model->user_login($this->active_user);
		}

		// salt password
		if (!empty($password)) {
			// do we have a salt?
			if ($this->get('salt') == '') {
				// salt it!
				$CI->load->helper('string');
				$salt = random_string('unique');

				// new password with salt
				$salted_password = md5($password . ':' . $salt);

				$this->db->update('users', array('user_salt' => $salt, 'user_password' => $salted_password), array('user_id' => $this->get('id')));
			}
		}

		// do we have a customer record for this user?
		// trigger its creation if not, else just get the active customer ID
		$this->active_user['customer_id'] = $this->get_customer_id($user_id);

		// prep hook
		$CI =& get_instance();
		// call the library here, because this may be loaded in the admin/login controller which doesn't preload
		// app_hooks like the other controllers
		$CI->load->library('app_hooks');
		$CI->app_hooks->data('member', $user_id);
		$CI->app_hooks->trigger('member_login', $user_id, $password);
		$CI->app_hooks->reset();

		return TRUE;
    }

    /**
    * User Logout
    *
    * @return boolean TRUE upon success
    */
    function logout () {
    	// delete activity
    	$this->db->delete('user_activity', array('user_id' => $this->get('id')));

    	// unset user_id session and login_time
    	$this->session->unset_userdata($this->session_name,'login_time');

    	// delete cookie
		$CI =& get_instance();
		$CI->load->helper('cookie');
		delete_cookie('user_remember_key');

		// prep hook
		// call the library here, because this may be loaded in the admin/login controller which doesn't preload
		// app_hooks like the other controllers
		$CI->load->library('app_hooks');
		$CI->app_hooks->data('member', $this->get('id'));
		$CI->app_hooks->trigger('member_logout', $this->get('id'));
		$CI->app_hooks->reset();

    	return TRUE;
    }

    /**
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

    /**
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

    /**
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

    /**
    * Is user in this group?
    *
    * Returns TRUE if the user is in the usergroup(s) or the privileges are open to all (i.e., array is empty or contains "0").
    * To check if the user is logged out, send "-1" by itself or in an array.  It's return TRUE if the user is logged out.
    *
    * @param int|array $group A group ID, or array of group ID's (they must be in one of the groups)
    * @param int $user_id (Optional) Specify the user.  (default: FALSE)
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
	    
	    // let's see if we even need to do anything...
	    if (is_array($group) and in_array('0', $group)) {
    		// this is a "privileges" array and it's public so anyone can see it
    		return TRUE;
    	}
    	elseif (is_array($group) and in_array('', $group)) {
    		return TRUE;
    	}

		// load user info
    	if ($user_id) {
    		$user_array = $this->get_user($user_id);
    	}
    	else {
    		if ($this->logged_in()) {
	    		$user_array = $this->active_user;
	    	}
	    	else {
		    	return FALSE;
	    	}
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

    /**
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
    		if ($this->logged_in()) {
	    		$user_array = $this->active_user;
	    	}
	    	else {
	    		return FALSE;
	    	}
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

    /**
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

    /**
    * Get Active Subscriptions
    *
    * Gets active subscriptions for a user
    *
    * @param int $user_id The user id
    *
    * @return array Array of active subscriptions, else FALSE if none exist
    */

    function get_active_subscriptions ($user_id) {
    	if (module_installed('billing')) {
	    	$this->load->model('billing/recurring_model');

	    	$customer_id = $this->get_customer_id($user_id);

	    	return $this->recurring_model->GetRecurrings(array('customer_id' => $customer_id));
	    }
	    else {
	    	return FALSE;
	    }
    }

    /**
    * Get Subscriptions
    *
    * Gets active and cancelled subscriptions for a user
    *
    * @param int $user_id The user id
    *
    * @param array Array of subscriptions, else FALSE if none exist
    */

    function get_subscriptions ($user_id) {
    	if (module_installed('billing')) {
	    	$this->load->model('billing/subscription_model');

	    	return $this->subscription_model->get_subscriptions(array('user_id' => $user_id), TRUE);
	    }
	    else {
	    	return FALSE;
	    }
    }

    /**
    * Get Customer ID
    *
    * Sometimes, we just need this, so let's not do a full blown query.
    *
    * @param int $user_id (default: active user)
    *
    * @return int|boolean $customer_id
    */
    function get_customer_id ($user_id = FALSE) {
    	if (!module_installed('billing')) {
    		return FALSE;
    	}

    	// auto-complete $user_id?
    	if (empty($user_id) and $this->logged_in()) {
    		$user_id = $this->active_user['id'];
    	}

    	// previously, we looked for the "customer_id" in the users table
    	// however, this didn't let us confirm that the customer record actually exists
    	// so now we look up via the customers table
    	$this->db->select('customer_id');
    	$this->db->where('internal_id',$user_id);
    	$result = $this->db->get('customers');

    	if ($result->num_rows() == 0) {
    		// no reason not to have a customer record
    		// let's create one
    		if (module_installed('billing')) {
    			// get user data
    			$user = (!empty($this->active_user) and $this->active_user['id'] == $user_id) ? $this->active_user : $this->get_user($user_id);

    			if (empty($user)) {
    				// how would this happen?  probably impossible
    				return FALSE;
    			}

				// do any custom fields map to billing fields?
				$user_custom_fields = $this->get_custom_fields();

				$customer = array();
				if (is_array($user_custom_fields)) {
					foreach ($user_custom_fields as $field) {
						if (!empty($field['billing_equiv']) and isset($user[$field['name']])) {
							$customer[$field['billing_equiv']] = $user[$field['name']];
						}
					}
				}

				$CI =& get_instance();
				$CI->load->model('billing/customer_model');

				$customer['email'] = $user['email'];
				$customer['internal_id'] = $user['id'];
				$customer['first_name'] = $user['first_name'];
				$customer['last_name'] = $user['last_name'];

				$customer_id = $CI->customer_model->NewCustomer($customer);

				$this->db->update('users',array('customer_id' => $customer_id),array('user_id' => $user_id));

				return $customer_id;
			}
    	}
    	else {
    		return $result->row()->customer_id;
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
    	$this->db->update('users', array('user_pending_charge_id' => '0'), array('user_id' => $user_id));

    	return TRUE;
    }

	/**
	* Validation
	*
	* Validates POST data to be acceptable for creating a new user
	*
	* @param boolean $editing Set to TRUE if this is an edited user (i.e., password can be blank) (default: FALSE)
	* @param boolean $error_array Return errors in an array or HTML formatted string (TRUE for array) (default: TRUE)
	*
	* @return array If errors, returns an array of individual errors, else returns TRUE
	*/
	function validation ($editing = FALSE, $error_array = TRUE) {
		$CI =& get_instance();

		$CI->load->library('form_validation');
		$CI->load->model('custom_fields_model');
		$CI->load->helpers(array('unique_username','unique_email','strip_whitespace'));

		$CI->form_validation->set_rules('first_name','First Name','trim|required|xss_clean');
		$CI->form_validation->set_rules('last_name','Last Name','trim|required|xss_clean');
		$unique_email = ($editing == FALSE) ? '|unique_email' : '';
		$CI->form_validation->set_rules('email','Email','trim' . $unique_email . '|valid_email|required');
		
		// unique email doesn't seem to be working, so let's do a manual check
		$email_error = FALSE;
		if ($editing == FALSE) {
			if ($this->unique_email($this->input->post('email')) === FALSE) {
				$email_error = TRUE;
			}
		}

		$username_rules = array('trim','strip_whitespace','min_length[3]', 'xss_clean');
		if ($this->config->item('username_allow_special_characters') == FALSE) {
			$username_rules[] = 'alpha_numeric';
		}
		if ($editing == FALSE) {
			$username_rules[] = 'unique_username';
		}
		$CI->form_validation->set_rules('username','Username',implode('|', $username_rules));

		if ($editing == FALSE) {
			$CI->form_validation->set_rules('password','Password','min_length[5]|matches[password2]');
			$CI->form_validation->set_rules('password2','Repeat Password','required');
		}

		if ($CI->form_validation->run() === FALSE) {
			if ($error_array == TRUE) {
				$errors = explode('||',str_replace(array('<p>','</p>'),array('','||'),validation_errors()));
				if ($email_error == TRUE) {
					$errors[] = 'This email address is unavailable.';
				}
			}
			else {
				$errors = validation_errors();
				if ($email_error == TRUE) {
					$errors .= '<p>This email address is unavailable.';
				}
			}
			
			return $errors;
		}

		// validate custom fields
		$custom_fields = $this->get_custom_fields(array('not_in_admin' => TRUE));

		$CI->load->library('custom_fields/form_builder');
		$CI->form_builder->build_form_from_array($custom_fields);

		if ($CI->form_builder->validate_post() === FALSE) {
			$errors = $CI->form_builder->validation_errors(($error_array == TRUE) ? TRUE : FALSE);
			return $errors;
		}

		return TRUE;
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
		if (empty($user_id))
			return false;

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
						'state' => $customer['state'],
						'phone_number' => $customer['phone']
					);

		return $address;
	}

	/**
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

	/**
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

	/**
	* Add a Usergroup
	*
	* @param int $user_id
	* @param int $group_id
	*
	* @return array New usergroup array
	*/
	function add_group ($user_id, $group_id) {
		$user = $this->get_user($user_id);

		if (is_array($user['usergroups']) and !empty($user['usergroups']) and in_array($group_id, $user['usergroups'])) {
			// already a member
			return FALSE;
		}

		if ($user['usergroups'] == FALSE) {
			$user['usergroups'] = array();
		}

		$user['usergroups'][] = $group_id;

		$usergroups = '|' . implode('|',$user['usergroups']) . '|';

		$this->db->update('users',array('user_groups' => $usergroups),array('user_id' => $user_id));

		return $usergroups;
	}

	/**
	* Remove a Usergroup
	*
	* @param int $user_id
	* @param int $group_id
	*
	* @return array New usergroup array
	*/
	function remove_group ($user_id, $group_id) {
		$user = $this->get_user($user_id);

		if (is_array($user['usergroups']) and !empty($user['usergroups'])) {
			foreach ($user['usergroups'] as $key => $val) {
				if ($val == $group_id) {
					unset($user['usergroups'][$key]);
				}
			}

			$usergroups = '|' . implode('|',$user['usergroups']) . '|';

			$this->db->update('users',array('user_groups' => $usergroups),array('user_id' => $user_id));
		}

		return $usergroups;
	}

	/**
	* Resend Validation Email
	*
	* Resends the validation email, unless there's no email to be sent
	*
	* @param int $user_id
	*
	* @return TRUE
	*/
	function resend_validation_email ($user_id) {
		$user = $this->get_user($user_id);

		if (empty($user)) {
			return FALSE;
		}

		if (!empty($user['validate_key'])) {
			$validation_link = site_url('users/validate/' . $user['validate_key']);

			$CI =& get_instance();
			$CI->app_hooks->data('member', $user['id']);

			$CI->app_hooks->data_var('validation_link', $validation_link);
			$CI->app_hooks->data_var('validation_code', $user['validate_key']);

			$CI->app_hooks->trigger('member_validate_email');

			return TRUE;
		}

		return FALSE;
	}

	/**
	* New User
	*
	* Create a new user, including custom fields
	*
	* @param string $email Email Address
	* @param string $password Password to use
	* @param string $username Username
	* @param string $first_name First name
	* @param string $last_name Last name
	* @param array $groups Array of group ID's to be entered into (default: FALSE)
	* @param int $affiliate Affiliate ID of referrer (default: FALSE)
	* @param boolean $is_admin Check to make an administrator (default: FALSE)
	* @param array $custom_fields An array of custom field data, matching in name (default: array())
	* @param boolean $require_validation Should we require email validation? (default: FALSE)
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

		// generate hashed password
		$CI =& get_instance();
		$CI->load->helper('string');
		$salt = random_string('unique');
		$hashed_password = md5($password . ':' . $salt);

		$insert_fields = array(
								'user_is_admin' => ($is_admin == TRUE) ? '1' : '0',
								'user_groups' => '|' . implode('|',$groups) . '|',
								'user_first_name' => $first_name,
								'user_last_name' => $last_name,
								'user_username' => $username,
								'user_email' => $email,
								'user_password' => $hashed_password,
								'user_salt' => $salt,
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
		if (module_installed('billing')) {
			$CI->load->model('billing/customer_model');

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

			// we don't want to set a country if we don't have a state/province, because we risk
			// internal US/Canada province validation
			if (isset($customer['country']) and (!isset($customer['state']) or (isset($customer['state']) and empty($customer['state'])))) {
				unset($customer['country']);
			}

			$customer_id = $CI->customer_model->NewCustomer($customer);

			$this->db->update('users',array('customer_id' => $customer_id),array('user_id' => $user_id));
		}

		// prep hook
		// only run this hook if the App_hooks library is loaded
		// it may not be if this is the user created during the install wizard
		if (class_exists('App_hooks') and $this->trigger_register_hook == TRUE) {
			$CI =& get_instance();
			$CI->app_hooks->data('member', $user_id);
			$CI->app_hooks->data_var('password', $password);

			// trip the validation email?
			if (!empty($validate_key)) {
				$validation_link = site_url('users/validate/' . $validate_key);

				$CI->app_hooks->data_var('validation_link', $validation_link);
				$CI->app_hooks->data_var('validation_code', $validate_key);

				$CI->app_hooks->trigger('member_validate_email');
			}

			$CI->app_hooks->trigger('member_register', $user_id, $password);
			$CI->app_hooks->reset();
		}

		return $user_id;
	}

	/**
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
	* @param array $groups Array of group ID's to be entered into (default: FALSE)
	* @param boolean $is_admin Check to make an administrator (default: FALSE)
	* @param array $custom_fields An array of custom field data, matching in name (default: array())
	*
	* @return int $user_id
	*/
	function update_user($user_id, $email, $password, $username, $first_name, $last_name, $groups = FALSE, $is_admin = FALSE, $custom_fields = array()) {
		$old_user = $this->get_user($user_id);

		if (empty($old_user)) {
			return FALSE;
		}

		// can we update the username?
		if ($old_user['username'] != $username) {
			// check if username is in use by someone else
			$users = $this->db->select('user_id')
							  ->from('users')
							  ->where('user_username',$username)
							  ->get();

			if ($users->num_rows() > 0) {
				// already in use
				// keep old username
				$username = $old_user['username'];
			}
		}

		// can we update the email?
		if ($old_user['email'] != $email) {
			// check if email is in use by someone else
			$users = $this->db->select('user_id')
							  ->from('users')
							  ->where('user_email',$email)
							  ->where('user_deleted','0')
							  ->get();

			if ($users->num_rows() > 0) {
				// already in use
				// keep old email
				$email = $old_user['email'];
			}
		}

		$update_fields = array(
								'user_is_admin' => ($is_admin == TRUE) ? '1' : '0',
								'user_groups' => @'|' . implode('|',$groups) . '|',
								'user_first_name' => $first_name,
								'user_last_name' => $last_name,
								'user_username' => $username,
								'user_email' => $email
							);

		if (!empty($password)) {
			$this->update_password($user_id, $password);
		}

		foreach ($custom_fields as $name => $value) {
			$update_fields[$name] = $value;
		}

		$this->db->update('users',$update_fields,array('user_id' => $user_id));

		if (module_installed('billing')) {
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
		}

		// hook call
		$CI =& get_instance();
		$CI->app_hooks->data('member', $user_id);
		$CI->app_hooks->trigger('member_update', $user_id, $old_user);
		$CI->app_hooks->reset();

		return TRUE;
	}

	/**
	* Update Billing Address
	*
	* @param int $user_id
	* @param array $address_fields New Address
	*
	* @return boolean
	*/
	function update_billing_address ($user_id, $address_fields) {
		if (!module_installed('billing')) {
			return FALSE;
		}

		$CI =& get_instance();
		$CI->load->model('billing/customer_model');

		$customer_id = $this->get_customer_id($user_id);

		if ($customer_id != FALSE) {
			$CI->customer_model->UpdateCustomer($customer_id, $address_fields);
		}
		else {
			// user doesn't have a customer record, let's create one
			$address_fields['internal_id'] = $user_id;
			$customer_id = $CI->customer_model->NewCustomer($address_fields);

			// link this to customer account
			$this->db->update('users', array('customer_id' => $customer_id), array('user_id' => $user_id));
		}

		return TRUE;
	}

	/**
	* Delete User
	*
	* @param int $user_id The user ID #
	*
	* @return boolean TRUE
	*/
	function delete_user ($user_id) {
		$this->db->update('users',array('user_deleted' => '1'),array('user_id' => $user_id));

		// hook call
		$CI =& get_instance();
		$CI->app_hooks->data('member', $user_id);
		$CI->app_hooks->trigger('member_delete', $user_id);
		$CI->app_hooks->reset();

		return TRUE;
	}

	/**
	* Update Password
	*
	* @param int $user_id
	* @param string $new_password
	*
	* @return boolean
	*/
	function update_password ($user_id, $new_password) {
		$CI =& get_instance();
		$CI->load->helper('string');
		$salt = random_string('unique');
		$hashed_password = md5($new_password . ':' . $salt);

		$this->db->update('users',array('user_password' => $hashed_password, 'user_salt' => $salt),array('user_id' => $user_id));

		// prep hook
		$CI =& get_instance();
		$CI->app_hooks->data('member', $user_id);
		$CI->app_hooks->data_var('new_password', $new_password);
		$CI->app_hooks->trigger('member_change_password', $user_id, $new_password);
		$CI->app_hooks->reset();

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
		$this->db->update('users',array('user_password' => md5($password), 'user_salt' => ''),array('user_id' => $user['id']));

		// hook call
		$CI =& get_instance();
		$CI->app_hooks->data('member', $user['id']);
		$CI->app_hooks->data_var('new_password', $password);

		$CI->app_hooks->trigger('member_forgot_password');
		$CI->app_hooks->reset();

		return TRUE;
	}

	/**
	* Suspend User
	*
	* @param int $user_id The user ID #
	*
	* @return boolean
	*/
	function suspend_user ($user_id) {
		$this->db->update('users',array('user_suspended' => '1'),array('user_id' => $user_id));

		// hook call
		$CI =& get_instance();
		$CI->app_hooks->data('member', $user_id);
		$CI->app_hooks->trigger('member_suspend', $user_id);
		$CI->app_hooks->reset();

		return TRUE;
	}

	/**
	* Unsuspend User
	*
	* @param int $user_id The user ID #
	*
	* @return boolean TRUE
	*/
	function unsuspend_user ($user_id) {
		$this->db->update('users',array('user_suspended' => '0'),array('user_id' => $user_id));

		// hook call
		$CI =& get_instance();
		$CI->app_hooks->data('member', $user_id);
		$CI->app_hooks->trigger('member_unsuspend', $user_id);
		$CI->app_hooks->reset();

		return TRUE;
	}

	/**
	* Get User
	*
	* @param int $user_id The user ID #
	* @param boolean $any_status Should even deleted records be retrieved?
	*
	* @return array User fields
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

	/**
	* Count Users
	*
	* @param array $filters In same format as get_users()
	*
	* @return int Number of users matching filters
	*/
	function count_users ($filters) {
		return $this->get_users($filters, FALSE, TRUE);
	}

	/**
	* Get Users
	*
	* @param int $filters['id'] The user ID to select
	* @param int $filters['group'] The group ID to filter by
	* @param int $filters['suspended'] Set to 1 to retrieve suspended users
	* @param string $filters['email'] The email address to filter by
	* @param string $filters['name'] Search by first and last name
	* @param string $filters['username'] Member username
	* @param string $filters['first_name'] Search by first name
	* @param string $filters['last_name'] Search by last name
	* @param date $filters['signup_start_date'] Select after this signup date
	* @param date $filters['signup_end_date'] Select before this signup date
	* @param string $filters['keyword'] Search by ID, Name, Email, or Username
	* @param string $filters['sort'] Field to sort by
	* @param string $filters['sort_dir'] ASC or DESC
	* @param int $filters['limit'] How many records to retrieve
	* @param int $filters['offset'] Start records retrieval at this record
	* @param boolean $any_status Set to TRUE to allow for deleted users, as well (default: FALSE)
	* @param boolean $counting Should we just count the number of users that match the filters? (default: FALSE)
	*
	* @return array Each user in an array of users
	*/
	function get_users ($filters = array(), $any_status = FALSE, $counting = FALSE) {
		$fields = $this->get_custom_fields();

		// keyword search
		if (isset($filters['keyword'])) {
			$this->db->where('user_deleted','0');
			$this->db->where('user_id', $filters['keyword']);
			$this->db->or_like('user_username', $filters['keyword']);
			$this->db->or_like('user_email', $filters['keyword']);
			$this->db->or_like('user_last_name', $filters['keyword']);
			$this->db->or_like('user_first_name', $filters['keyword']);
		}

		// other filters
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
				$this->db->where('(`user_first_name` LIKE \'%' . mysql_real_escape_string($filters['name']) . '%\' OR `user_last_name` LIKE \'%' . mysql_real_escape_string($filters['name']) . '%\')');
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
		if (is_array($fields)) {
			foreach ($fields as $field) {
				if (isset($filters[$field['name']])) {
					$this->db->like('users.' . $field['name'],$filters[$field['name']]);
				}
			}
		}

		if ($any_status == FALSE) {
			$this->db->where('user_deleted','0');
		}

		// standard ordering and limiting
		if ($counting == FALSE) {
			$order_by = (isset($filters['sort'])) ? $filters['sort'] : 'user_username';
			$order_dir = (isset($filters['sort_dir'])) ? $filters['sort_dir'] : 'ASC';
			$this->db->order_by($order_by, $order_dir);

			if (isset($filters['limit'])) {
				$offset = (isset($filters['offset'])) ? $filters['offset'] : 0;
				$this->db->limit($filters['limit'], $offset);
			}
		}

		if ($counting === TRUE) {
			$this->db->select('users.user_id');
			$result = $this->db->get('users');
			$rows = $result->num_rows();
			$result->free_result();
			return $rows;
		}
		else {
			$this->db->from('users');

			$result = $this->db->get();
		}

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
							'salt' => $user['user_salt'],
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

		$result->free_result();

		return $users;
	}

	/**
	* New User Custom Field
	*
	* Creates the user-field-specific custom field record
	*
	* @param int $custom_field_id
	* @param string $billing_equiv If this field represents a billing address field (e.g, "address_1"), specify here:  options: address_1/2, state, country, postal_code, company (default: '')
	* @param boolean $admin_only Is this an admin-only field? (default: FALSE)
	* @param boolean $registration_form Should we show this in the registration form? (default: TRUE)
	*
	* @return int $custom_field_id
	*/
	function new_custom_field ($custom_field_id, $billing_equiv = '', $admin_only = FALSE, $registration_form = TRUE) {
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

	/**
	* Update User Custom Field
	*
	* Updates the user_fields table with user custom field-specific information
	*
	* @param int $user_field_id The custom field ID to edit
	* @param string $billing_equiv If this field represents a billing address field (e.g, "address_1"), specify here:  options: address_1/2, state, country, postal_code, company (default: '')
	* @param boolean $admin_only Is this an admin-only field? (default: FALSE)
	* @param boolean $registration_form Should we show this in the registration form? (default: TRUE)
	*
	* @return boolean TRUE
	*/
	function update_custom_field ($custom_field_id, $billing_equiv = '', $admin_only = FALSE, $registration_form = TRUE) {
		$result = $this->db->select('user_field_id')
							 ->from('user_fields')
							 ->where('custom_field_id',$custom_field_id)
							 ->get();

		if ($result->num_rows() == 0) {
			// no record yet
			$field_id = $this->new_custom_field($custom_field_id, $billing_equiv, $admin_only, $registration_form);
		}

		$update_fields = array(
							'subscription_plans' => '',
							'products' => '',
							'user_field_billing_equiv' => $billing_equiv,
							'user_field_admin_only' => ($admin_only == TRUE) ? '1' : '0',
							'user_field_registration_form' => ($registration_form == TRUE) ? '1' : '0'
							);

		$this->db->update('user_fields',$update_fields,array('custom_field_id' => $custom_field_id));

		return TRUE;
	}

	/**
	* Delete User Custom Field
	*
	* ge custom field record and modify database
	*
	* @param int $id The ID of the field
	* @param string The database table to reflect the changes, else FALSE
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

	/**
	* Get User Custom Field
	*
	* @param int $custom_field_id
	*
	* @return boolean $custom_field or FALSE
	*/
	function get_custom_field ($id) {
		$return = $this->get_custom_fields(array('id' => $id));

		if (empty($return)) {
			return FALSE;
		}

		return $return[0];
	}

	/**
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

		$this->db->join('user_fields','custom_fields.custom_field_id = user_fields.custom_field_id','left');
		$this->db->order_by('custom_fields.custom_field_order','ASC');

		$this->db->select('user_fields.user_field_id');
		$this->db->select('user_fields.user_field_billing_equiv');
		$this->db->select('user_fields.user_field_admin_only');
		$this->db->select('user_fields.user_field_registration_form');
		$this->db->select('custom_fields.*');

		$this->db->where('custom_field_group','1');

		$result = $this->db->get('custom_fields');

		if ($result->num_rows() == 0) {
			return FALSE;
		}

		$billing_installed = module_installed('billing');

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
							'data' => (!empty($field['custom_field_data'])) ? unserialize($field['custom_field_data']) : array(),
							'billing_equiv' => ($billing_installed === TRUE) ? $field['user_field_billing_equiv'] : '',
							'admin_only' => ($field['user_field_admin_only'] == '1') ? TRUE : FALSE,
							'registration_form' => ($field['user_field_registration_form'] == '1') ? TRUE : FALSE
						);
		}

		$this->cache_fields[$cache_string] = $fields;

		return $fields;
	}
}