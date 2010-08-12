<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/*
* Users Module
*
* Handles logins, registration, subscription management, profile management, forgotten passwords, password management
*
* @author Electric Function, Inc.
* @copyright Electric Function, Inc.
* @package Electric Publisher
*
*/

class Users extends Front_Controller {
	var $public_methods; // these methods can be accessed without being loggedin

	function __construct() {
		parent::__construct();
		
		$this->public_methods = array('password_reset','login','post_login','forgot_password','post_forgot_password','register','post_registration');
		
		if (!in_array($this->router->fetch_method(), $this->public_methods) and $this->user_model->logged_in() == FALSE) {
			redirect('users/login?return=' . query_value_encode(current_url()));
		}
	}
	
	function index () {
		$notice = $this->session->flashdata('notice');
	
		$this->smarty->assign('notice', $notice);
		return $this->smarty->display('account_templates/home.thtml');
	}
	
	function profile () {
		// get custom fields
		$custom_fields = $this->user_model->get_custom_fields(array('not_in_admin' => TRUE));
		
		$values = ($this->input->get('values')) ? unserialize(query_value_decode($this->input->get('values'))) : $this->user_model->get();
		
		$errors = ($this->input->get('errors') == 'true') ? $this->session->flashdata('profile_errors') : FALSE;
		
		$this->smarty->assign('custom_fields',$custom_fields);
		$this->smarty->assign('values',$values);
		$this->smarty->assign('validation_errors',$errors);
		return $this->smarty->display('account_templates/profile.thtml');
	}
	
	function post_profile () {
		// create an array of current values in case we redirect with an error
		$values = array();
		$values['username'] = $this->input->post('username');
		$values['email'] = $this->input->post('email');
		$values['first_name'] = $this->input->post('first_name');
		$values['last_name'] = $this->input->post('last_name');
		
		$custom_fields = $this->user_model->get_custom_fields(array('not_in_admin' => TRUE));
		foreach ($custom_fields as $field) {
			$values[$field['name']] = $_POST[$field['name']];
		}
		
		$values = query_value_encode(serialize($values));
		
		// validate standard and custom form fields
		$validated = $this->user_model->validation(TRUE);
		
		if ($validated !== TRUE) {
			$this->session->set_flashdata('profile_errors',validation_errors());
		
			return redirect('users/profile?errors=true&values=' . $values);
		}
		
		// we validated!  let's update the account
		$custom_fields = $this->custom_fields_model->post_to_array('1');
			
		$this->user_model->update_user(
										$this->user_model->get('id'),
										$this->input->post('email'),
										$this->input->post('password'),
										$this->input->post('username'),
										$this->input->post('first_name'),
										$this->input->post('last_name'),
										$this->user_model->get('usergroups'),
										($this->user_model->is_admin()) ? TRUE : FALSE, // not an administratior
										$custom_fields
									);
											
		$this->session->set_flashdata('notice','You have successfully updated your profile.');
		
		return redirect('users/');
	}
	
	function password () {
		$validation_errors = ($this->input->get('errors') == 'true') ? $this->session->flashdata('password_errors') : FALSE;
	
		$this->smarty->assign('validation_errors',$validation_errors);	
		return $this->smarty->display('account_templates/change_password.thtml');
	}
	
	function post_password () {
		$this->load->library('form_validation');
		
		// this helper will verify their current password
		$this->load->helper('verify_password');
		
		$this->form_validation->set_rules('password','Current Password','required|verify_password');
		$this->form_validation->set_rules('new_password','New Password','required|min_length[6]|matches[new_password2]');
		$this->form_validation->set_rules('new_password2','Repeat New Password','required');
		
		if ($this->form_validation->run() == FALSE) {
			$this->session->set_flashdata('password_errors',validation_errors());
		
			return redirect('users/password?errors=true');
		}
		
		$this->user_model->update_password($this->user_model->get('id'), $this->input->post('new_password'));
		
		$this->session->set_flashdata('notice','You have successfully updated your password.');
		
		redirect('users');
	}
	
	function forgot_password () {
		$error = ($this->input->get('error')) ? query_value_decode($this->input->get('error')) : '';
	
		$this->smarty->assign('error',$error);
		return $this->smarty->display('account_templates/forgot_password.thtml');
	}
	
	function post_forgot_password () {
		// validate
		$this->load->library('form_validation');
		
		$this->form_validation->set_rules('email','Email','trim|valid_email');
		
		if ($this->form_validation->run() == FALSE) {
			return redirect('users/forgot_password?error=' . query_value_encode('You have entered an invalid email address.'));
		}
	
		$users = $this->user_model->get_users(array('email' => $this->input->post('email')));
		
		if (count($users) > 1) {
			return redirect('users/forgot_password?error=' . query_value_encode('There was an error retrieving your account.'));
		}
		elseif (empty($users)) {
			return redirect('users/forgot_password?error=' . query_value_encode('Your account record could not be retrieved.'));
		}
		elseif (count($users) == 1) {
			// success
			$user = $users[0];
			
			$this->user_model->reset_password($user['id']);
			
			return redirect('users/password_reset');
		}
		
		return FALSE;
	}
	
	function password_reset () {
		return $this->smarty->display('account_templates/forgot_password_complete.thtml');
	}
	
	function register () {
		// do we have a return URL?
		$return = ($this->input->get('return')) ? query_value_decode($this->input->get('return')) : '';
		
		// do we have any errors?
		$validation_errors = ($this->input->get('errors') == 'true') ? $this->session->flashdata('register_errors') : '';
		
		// get custom fields
		$custom_fields = $this->user_model->get_custom_fields(array('registration_form' => TRUE, 'not_in_admin' => TRUE));
		
		// do we have values being passed?
		$values = ($this->input->get('values')) ? unserialize(query_value_decode($this->input->get('values'))) : array();
		
		// to stop PHP notices, we'll populate empty values for custom fields if we didn't get any values
		if (empty($values)) {
			foreach ($custom_fields as $field) {
				$values[$field['name']] = '';
			}
		}
		
		$this->smarty->assign('custom_fields',$custom_fields);
		$this->smarty->assign('return', $return);
		$this->smarty->assign('validation_errors', $validation_errors);
		$this->smarty->assign('values',$values);
		return $this->smarty->display('account_templates/registration.thtml');
	}
	
	function post_registration () {
		// get $return if available
		if ($this->input->post('return') != '') {
			$return = query_value_decode($this->input->post('return'));
		}
		else {
			// redirect to subscription packages?
			$this->load->model('billing/subscription_plan_model');
			$plans = $this->subscription_plan_model->get_plans();
			
			if (setting('show_subscriptions') == '1' and !empty($plans)) {
				$return = site_url('subscriptions');
			}
			else {
				$return = setting('registration_redirect');
			}
		}
		
		// create an array of current values in case we redirect with an error
		$values = array();
		$values['username'] = $this->input->post('username');
		$values['email'] = $this->input->post('email');
		$values['first_name'] = $this->input->post('first_name');
		$values['last_name'] = $this->input->post('last_name');
		
		$custom_fields = $this->user_model->get_custom_fields(array('not_in_admin' => TRUE));
		foreach ($custom_fields as $field) {
			$values[$field['name']] = $_POST[$field['name']];
		}
		
		$values = query_value_encode(serialize($values));
		
		// have they agreed to the Terms of Service?
		if (setting('require_tos') == '1' and !$this->input->post('agree_tos')) {
			$this->session->set_flashdata('register_errors','<p>You must accept the Terms of Service before your account is created.</p>');
		
			return redirect('users/register?return=' . query_value_encode($return) . '&errors=true&values=' . $values);
		}
		
		// validate standard and custom form fields
		$validated = $this->user_model->validation(FALSE);
		
		if ($validated !== TRUE) {
			$this->session->set_flashdata('register_errors',validation_errors());
		
			return redirect('users/register?return=' . query_value_encode($return) . '&errors=true&values=' . $values);
		}
		
		// we validated!  let's create the account
		$custom_fields = $this->custom_fields_model->post_to_array('1');
			
		$user_id = $this->user_model->new_user(
												$this->input->post('email'),
												$this->input->post('password'),
												$this->input->post('username'),
												$this->input->post('first_name'),
												$this->input->post('last_name'),
												FALSE, // default usergroup
												FALSE, // no affiliate
												FALSE, // not an administratior
												$custom_fields,
												TRUE // require validation
											);
											
		// log them in
		$this->user_model->login_by_id($user_id);
											
		// do we have a relative URL?
		if (strpos($return,'http') === FALSE) {
			$return = site_url($return);
		}
			
		return header('Location: ' . $return);
	}
	
	function login () {
		// are they already logged in?
		if ($this->user_model->logged_in()) {
			// send to main account page
			return redirect('users');
		}
	
		// do we have a return URL?
		$return = ($this->input->get('return')) ? query_value_decode($this->input->get('return')) : '';
		
		// do we have any errors?
		$validation_errors = ($this->input->get('errors') == 'true') ? $this->session->flashdata('login_errors') : '';
		
		// do we have any notices?
		$notices = ($this->session->flashdata('notices')) ? $this->session->flashdata('notices') : FALSE;
		
		// do we have a username?
		$username = ($this->input->get('username')) ? $this->input->get('username') : '';
			
		$this->smarty->assign('return',$return);
		$this->smarty->assign('username',$username);
		$this->smarty->assign('validation_errors',$validation_errors);
		$this->smarty->assign('notices', $notices);
		return $this->smarty->display('account_templates/login.thtml');
	}
	
	function post_login () {
		// get $return if available
		if ($this->input->post('return') != '') {
			$return = query_value_decode($this->input->post('return'));
		}
		else {
			$return = site_url('users');
		}
		
		// validate fields
		$this->load->library('form_validation');
		$this->form_validation->set_rules('username','Username/Email','trim|required');
		$this->form_validation->set_rules('password','Password','trim|required');
		
		if ($this->form_validation->run() == FALSE) {
			$this->session->set_flashdata('login_errors',validation_errors());
		
			return redirect('users/login?return=' . query_value_encode($return) . '&errors=true&username=' . $this->input->post('username'));
		}
		
		// are we remembering this user?
		$remember = ($this->input->post('remember') and $this->input->post('remember') != '') ? TRUE : FALSE;
		
		// attempt login
		if ($this->user_model->login($this->input->post('username'), $this->input->post('password'), $remember)) {
			// success!
			
			// do we have a relative URL?
			if (strpos($return,'http') === FALSE) {
				$return = site_url($return);
			}
			
			return header('Location: ' . $return);
		}
		else {
			if ($this->user_model->failed_due_to_activation == TRUE) {
				$this->session->set_flashdata('login_errors','<p>Login failed.  Your account email has not been activated yet.  Please click the link in your activation email to activate your account.  If you cannot find the email in your inbox or junk folders, contact website support for assistance.');
			}
			else {
				$this->session->set_flashdata('login_errors','<p>Login failed.  Please verify your username/email and password.');
			}
		
			return redirect('users/login?return=' . query_value_encode($return) . '&errors=true');
		}
	}
	
	function logout () {
		$this->user_model->logout();
		
		return redirect('users');
	}
	
	function validate ($key) {
		// logout so that they go the login after validating
		$this->user_model->logout();
	
		$this->db->select('user_id');
		$this->db->where('user_validate_key', $key);
		$result = $this->db->get('users');
		
		if ($result->num_rows() == 0) {
			die(show_error('There was an error validating your account email.  Your email may have already been validated, or you need to copy and paste then entire URL from the email into your browser and try again.'));
		}
		else {
			$this->db->update('users',array('user_validate_key' => ''), array('user_validate_key' => $key));
		}
		
		$this->session->set_flashdata('notices','<p>Your account email has been validated successfully.</p>');
		
		return redirect('users/login');
	}
}