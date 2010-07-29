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
		
		$this->public_methods = array('login','post_login','forgot_password','register','post_registration');
		
		if (!in_array($this->router->fetch_method(), $this->public_methods) and $this->user_model->logged_in() == FALSE) {
			redirect('users/login?return=' . query_value_encode(current_url()));
		}
	}
	
	function index () {
		
	}
	
	function register () {
		// do we have a return URL?
		$return = ($this->input->get('return')) ? query_value_decode($this->input->get('return')) : '';
		
		// do we have any errors?
		$validation_errors = ($this->input->get('errors') == 'true') ? $this->session->flashdata('register_errors') : '';
		
		// get custom fields
		$this->load->model('custom_fields_model');
		$custom_fields = $this->custom_fields_model->get_custom_fields(array('group' => '1'));
		
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
		return $this->smarty->display('account_registration.thtml');
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
				$return = site_url('billing/subscriptions');
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
		
		$this->load->model('custom_fields_model');
		
		$custom_fields = $this->custom_fields_model->get_custom_fields(array('group' => '1'));
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
			$error_string = '';
			foreach ($validated as $error) {
				$error_string = '<p>' . $error . '</p>';
			}
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
		
		// do we have a username?
		$username = ($this->input->get('username')) ? $this->input->get('username') : '';
			
		$this->smarty->assign('return',$return);
		$this->smarty->assign('username',$username);
		$this->smarty->assign('validation_errors',$validation_errors);
		return $this->smarty->display('account_login.thtml');
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
}