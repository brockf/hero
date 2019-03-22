<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/*
* Users Module
*
* Handles logins, registration, subscription management, profile management, forgotten passwords, password management
*
* @author Electric Function, Inc.
* @copyright Electric Function, Inc.
* @package Hero Framework
*
*/

class Users extends Front_Controller {
	var $public_methods; // these methods can be accessed without being loggedin

	function __construct() {
		parent::__construct();

		$this->public_methods = array('validate','password_reset','login','post_login','forgot_password','post_forgot_password','register','post_registration');

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

		$this->load->library('custom_fields/form_builder');
		$this->form_builder->build_form_from_array($custom_fields);
		$values = $this->form_builder->post_to_array();
		$values = query_value_encode(serialize($values));

		// validate standard and custom form fields
		$validated = $this->user_model->validation(TRUE, FALSE);

		if ($validated !== TRUE) {
			$this->session->set_flashdata('profile_errors',$validated);

			return redirect('users/profile?errors=true&values=' . $values);
		}

		// we validated!  let's update the account
		$custom_fields = $this->form_builder->post_to_array();

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
		$this->load->helper('security');
		$error = ($this->input->get('error')) ? xss_clean(query_value_decode($this->input->get('error'))) : '';

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
		if (empty($values) and is_array($custom_fields)) {
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
			if (module_installed('billing')) {
				// redirect to subscription packages?
				$this->load->model('billing/subscription_plan_model');
				$plans = $this->subscription_plan_model->get_plans();
			}
			else {
				$plans = FALSE;
			}

			if (setting('show_subscriptions') == '1' and !empty($plans)) {
				$return = site_url('subscriptions');
			}
			else {
				$return = setting('registration_redirect');
			}
		}

		// if we have activated our registration_spam_stopper in the settings,
		// we will check to see if the (stupid) bot completed the hidden field
		// if so, reject it
		if ($this->config->item('registration_spam_stopper') == '1') {
			if ($this->input->post('email_confirmation_hp') != '') {
				die(show_error('This registration was rejected for being spam.  If you are not a spambot, please don\'t complete the "Email Confirmation HP" field.'));
			}
		}

		// create an array of current values in case we redirect with an error
		$values = array();
		$values['username'] = $this->input->post('username');
		$values['email'] = $this->input->post('email');
		$values['first_name'] = $this->input->post('first_name');
		$values['last_name'] = $this->input->post('last_name');

		$custom_fields = $this->user_model->get_custom_fields(array('not_in_admin' => TRUE));

		// get values for this form
		if (is_array($custom_fields)) {
			$this->load->library('custom_fields/form_builder');
			$this->form_builder->build_form_from_array($custom_fields);

			$values = $this->form_builder->post_to_array();
		}

		$values = query_value_encode(serialize($values));

		// have they agreed to the Terms of Service?
		if (setting('require_tos') == '1' and !$this->input->post('agree_tos')) {
			$this->session->set_flashdata('register_errors','<p>You must accept the Terms of Service before your account is created.</p>');

			return redirect('users/register?return=' . query_value_encode($return) . '&errors=true&values=' . $values);
		}

		// validate standard and custom form fields
		$validated = $this->user_model->validation(FALSE, FALSE);

		if ($validated !== TRUE) {
			$this->session->set_flashdata('register_errors',$validated);

			return redirect('users/register?return=' . query_value_encode($return) . '&errors=true&values=' . $values);
		}

		// we validated!  let's create the account
		$this->form_builder->build_form_from_array($custom_fields);
		$custom_fields = $this->form_builder->post_to_array();

		// are we validating the emails?
		$validation = (setting('validate_emails') == '1') ? TRUE : FALSE;

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
												$validation // require validation
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
		$username = ($this->input->get('username')) ? $this->input->get('username', true) : '';

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
			elseif ($this->user_model->failed_due_to_duplicate_login == TRUE) {
				$this->session->set_flashdata('login_errors','<p>Login failed.  Someone is already logged in with this account.  If you believe this is in error, wait 1 minute and try again.  Otherwise, ensure that you are not logged in the site on another device before continuing.');
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
		// logout so that they go to the login after validating
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

	function invoices ($subscription_id = FALSE) {
		$this->load->model('billing/invoice_model');

		$filters = array('user_id' => $this->user_model->get('id'));

		if ($subscription_id !== FALSE) {
			$filters['subscription_id'] = (int)$subscription_id;
		}

		$invoices = $this->invoice_model->get_invoices($filters);

		if ($subscription_id == FALSE) {
			$this->smarty->assign('for_subscription',FALSE);
		}
		else {
			$this->smarty->assign('for_subscription',$subscription_id);
		}

		$this->smarty->assign('invoices', $invoices);
		return $this->smarty->display('account_templates/invoices');
	}

	function invoice ($invoice_id) {
		$this->load->model('billing/invoice_model');
		$invoice = $this->invoice_model->get_invoice($invoice_id);

		if (empty($invoice_id) or empty($invoice)) {
			die(show_error('Unable to find an invoice by that ID.'));
		}

		$cur_user_id = $this->user_model->active_user['id'];

		if ($cur_user_id != $invoice['user_id'])
		{
			die(show_error('That invoice does not belong to you.'));
		}

		// get invoice lines
		$lines = $this->invoice_model->invoice_lines($invoice['id']);

		// format address
		$this->load->helper('format_street_address');
		$formatted_address = format_street_address($invoice['billing_address']);
		// remove <br />'s
		$formatted_address = strip_tags($formatted_address);

		// get shipping address
		$this->load->model('store/order_model');
		$order = $this->order_model->get_order($invoice['id'], 'order_id');

		$shipping_address = FALSE;
		if (!empty($order)) {
			if (!empty($order['shipping'])) {
				$shipping_address = $order['shipping'];
			}
		}

		if (!empty($shipping_address)) {
			$formatted_shipping_address = format_street_address($shipping_address);
			// remove <br />'s
			$formatted_shipping_address = strip_tags($formatted_shipping_address);
		}
		else {
			$formatted_shipping_address = FALSE;
		}

		// get other invoice data
		$data = $this->invoice_model->get_invoice_data($invoice['id']);

		$this->smarty->assign('invoice',$invoice);
		$this->smarty->assign('lines', $lines);
		$this->smarty->assign('formatted_address', $formatted_address);
		$this->smarty->assign('shipping_address', $shipping_address);
		$this->smarty->assign('formatted_shipping_address', $formatted_shipping_address);
		$this->smarty->assign('shipping',$data['shipping']);
		$this->smarty->assign('subtotal',$data['subtotal']);
		$this->smarty->assign('tax',$data['tax']);
		$this->smarty->assign('total',$data['total']);
		$this->smarty->assign('discount',$data['discount']);

		return $this->smarty->display('account_templates/invoice');
	}

	function cancel ($subscription_id) {
		if (module_installed('billing')) {
			$this->load->model('billing/subscription_model');
			$subscription = $this->subscription_model->get_subscription($subscription_id);

			if (empty($subscription) or $subscription['user_id'] != $this->user_model->get('id')) {
				die(show_error('The subscription your attempting to cancel is invalid.'));
			}

			if ($this->input->post('confirm')) {
				// do the cancellation
				$this->subscription_model->cancel_subscription($subscription_id);

				$this->smarty->assign('cancelled',TRUE);
			}
			else {
				$this->smarty->assign('cancelled',FALSE);
			}

			$this->smarty->assign('subscription',$subscription);
			$this->smarty->display('account_templates/cancel_subscription');
		}
		else {
			die(show_error('Billing module is not installed.'));
		}
	}

	function update_cc ($subscription_id) {
		$this->load->model('billing/subscription_model');
		$subscription = $this->subscription_model->get_subscription($subscription_id);

		header('Location: ' . $subscription['renew_link']);
		die();
	}
}