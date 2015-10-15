<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/*
* Billing Control Panel
*
* Displays all control panel forms, datasets, and other displays
*
* @author Electric Function, Inc.
* @copyright Electric Function, Inc.
* @package Hero Framework
*
*/

class Admincp extends Admincp_Controller {
	function __construct()
	{
		parent::__construct();
				
		$this->admin_navigation->parent_active('storefront');

		if ($this->uri->segment(3) == 'gateways') {
			$this->admin_navigation->module_link('Setup New Gateway',site_url('admincp/billing/new_gateway'));
		}
		elseif ($this->uri->segment(3) == 'subscriptions') {
			$this->admin_navigation->module_link('New Subscription Plan',site_url('admincp/billing/subscription_add'));
		}
	}
	
	function change_plan ($subscription_id) {
		$this->load->library('admin_form');
		$form = new Admin_form;
		
		$this->load->model('recurring_model');
		$subscription = $this->recurring_model->GetRecurring($subscription_id);
		
		if (!$subscription) {
			die(show_error('This subscription does not exist.'));
		}
		
		$form->fieldset('Change Subscription');
		$form->value_row('Subscription ID #',$subscription_id);
		$form->value_row('Current Plan',$subscription['plan']['name']);
		
		$current_plan_id = $subscription['plan']['id'];
		
		$this->load->model('subscription_plan_model');
		$subscriptions = $this->subscription_plan_model->get_plans();
		$options = array();
		foreach ($subscriptions as $subscription) {
			$options[$subscription['plan_id']] = $subscription['name'] . ' (' . setting('currency_symbol') . $subscription['amount'] . ' every ' . $subscription['interval'] . ' days)';
		}
		$form->dropdown('New Plan','plan',$options,$current_plan_id);
		
		$data = array(
					'form_title' => 'Update Subscription',
					'form_action' => site_url('admincp/billing/post_change_plan/' . $subscription_id),
					'form' => $form->display()
					);
		
		$this->load->view('change_plan', $data);
	}
	
	function post_change_plan ($id) {
		$this->load->model('recurring_model');
		if ($this->recurring_model->ChangeRecurringPlan($id,$this->input->post('plan'))) {
			$this->notices->SetNotice('Subscription #' . $id . ' updated to a new plan successfully.');
		}
		else {
			$this->notices->SetError('Subscription #' . $id . ' could not be updated.');
		}
		
		$subscription = $this->recurring_model->GetRecurring($id);
		
		redirect(site_url('admincp/users/profile/' . $subscription['customer']['internal_id']));
	}
	
	/**
	* Update Credit Card
	*/
	function update_cc ($id) {
		$this->load->model('billing/subscription_model');
		$subscription = $this->subscription_model->get_subscription($id);
		
		// load existing gateways
		$this->load->model('gateway_model');
		$gateways = $this->gateway_model->GetGateways(array());
		
		$data = array(
					'subscription' => $subscription,
					'gateways' => $gateways,
					'form_title' => 'Update Credit Card',
					'form_action' => site_url('admincp/billing/post_update_cc/' . $subscription['id'])
				);
				
		$this->load->view('update_cc', $data);
	}
	
	/**
	* Post Update Credit Card
	*/
	function post_update_cc () {
		$recurring_id = $this->input->post('subscription_id');
		
		$CI =& get_instance();
		$CI->load->model('billing/subscription_model');
		$subscription = $CI->subscription_model->get_subscription($recurring_id);
	
		$this->load->library('form_validation');
		$this->form_validation->set_rules('cc_number','Credit Card Number','required|is_natural');
		$this->form_validation->set_rules('cc_name','Credit Card Name','required');
		$this->form_validation->set_rules('subscription_id','Recurring ID # (hidden)','required|is_natural');
		
		if ($this->form_validation->run() === FALSE) {
			$this->notices->SetError(strip_tags(validation_errors()));
		}
		
		// passed validation
		$credit_card = array(
						'card_num' => $this->input->post('cc_number'),
						'card_name' => $this->input->post('card_name'),
						'exp_month' => $this->input->post('cc_expiry_month'),
						'exp_year' => $this->input->post('cc_expiry_year'),
						'cvv' => $this->input->post('cc_security')
						);
		$gateway_id = $this->input->post('gateway');
		
		$this->load->model('gateway_model');
		$response = $this->gateway_model->UpdateCreditCard($recurring_id, $credit_card, $gateway_id);
		
		if (!is_array($response) or isset($response['error'])) {
			$this->notices->SetError($this->lang->line('transaction_error') . $response['error_text'] . ' (#' . $response['error'] . ')');
		}
		elseif (isset($response['response_code']) and $response['response_code'] == '105') {
			$this->notices->SetError($this->lang->line('transaction_error') . $response['response_text'] . '. ' . $response['reason'] . ' (#' . $response['response_code'] . ')');
		}
		else {
			$this->notices->SetNotice($this->lang->line('transaction_ok'));
		}
		
		if (isset($response['recurring_id'])) {
			$redirect = site_url('admincp/users/profile/' . $subscription['user_id']);
		}
		else {
			$redirect = site_url('admincp/billing/update_cc/' . $recurring_id);
		}
		
		redirect($redirect);
		
		return TRUE;
	}
	
	function change_price ($subscription_id) {
		$this->load->library('admin_form');
		$form = new Admin_form;
		
		$this->load->model('recurring_model');
		$subscription = $this->recurring_model->GetRecurring($subscription_id);
		
		if (!$subscription) {
			die(show_error('This subscription does not exist.'));
		}
		
		$form->fieldset('Change Subscription');
		$form->value_row('Subscription ID #',$subscription_id);
		$form->value_row('Current Price',setting('currency_symbol') . $subscription['amount'] . ' every ' . $subscription['interval'] . ' days');
		
		$form->text('New Price (' . setting('currency_symbol') . ')','new_price',$subscription['amount'],FALSE,TRUE,'0.00',FALSE,'60px');
		
		$data = array(
					'form_title' => 'Update Subscription',
					'form_action' => site_url('admincp/billing/post_change_price/' . $subscription_id),
					'form' => $form->display()
					);
		
		$this->load->view('change_plan', $data);
	}
	
	function post_change_price ($id) {
		$this->load->model('recurring_model');
		
		if ($this->recurring_model->UpdateRecurring(array('recurring_id' => $id, 'amount' => $this->input->post('new_price')))) {
			$this->notices->SetNotice('Subscription #' . $id . ' updated successfully.');
		}
		else {
			$this->notices->SetError('Subscription #' . $id . ' could not be updated.');
		}
		
		$subscription = $this->recurring_model->GetRecurring($id);
		
		redirect(site_url('admincp/users/profile/' . $subscription['customer']['internal_id']));
	}
	
	function new_subscription ($user_id = 0, $subscription_id = 0) {
		$this->load->library('admin_form');
		
		$form = new Admin_form;
		$form->fieldset('Subscription Details');
		
		// we can't show a dropdown of all users if we have 5000+ members
		$result = $this->db->select('COUNT(user_id) AS `user_count`',FALSE)
						   ->from('users')
						   ->get()
						   ->row_array();
						   
		if ($result['user_count'] > 5000) {						   
			$members = $this->user_model->get_users(array('id' => $user_id));
		}
		else {
			$members = $this->user_model->get_users();
		}
		
		$options = array();
		foreach ($members as $member) {
			$options[$member['id']] = $member['last_name'] . ', ' . $member['first_name'] . ' (' . $member['username'] . ')';
		}		
		$form->dropdown('Member','member',$options, $user_id);
		
		$this->load->model('subscription_plan_model');
		$subscriptions = $this->subscription_plan_model->get_plans();
		if (is_array($subscriptions)) {
			$options = array();
			foreach ($subscriptions as $subscription) {
				$options[$subscription['id']] = $subscription['name'] . ' (' . setting('currency_symbol') . $subscription['amount'] . ' every ' . $subscription['interval'] . ' days)';
			}
			$form->dropdown('Subscription','subscription',$options,$subscription_id);
		}
		else {
			$form->value_row('Subscription','You don\'t have any subscription plans created.  <a href="' . site_url('admincp/billing/subscription_add') . '">Add a subscription plan</a>');
		}
				
		$data = array(
					'form' => $form->display(),
					'form_title' => 'Create New Subscription (Step 1 of 2)',
					'form_action' => site_url('admincp/billing/new_subscription_details')
					);
					
		$this->load->view('new_subscription.php', $data);
	}
	
	function new_subscription_details () {
		define('INCLUDE_DATEPICKER','TRUE');
		
		// get subscription
		$this->load->model('subscription_plan_model');
		$subscription = $this->subscription_plan_model->get_plan($this->input->post('subscription'));
		
		if (!$subscription) {
			die(show_error('Invalid subscription ID.'));
		}
		
		$this->load->model('states_model');
		$countries = $this->states_model->GetCountries();
		$states = $this->states_model->GetStates();
		
		// load gateways
		$this->load->model('gateway_model');
		$gateways = $this->gateway_model->GetGateways(array());
		if (is_array($gateways)) {
			foreach ($gateways as $key => $gateway) {
				// get settings
				$this->load->library('payment/' . $gateway['name']);
				$settings = $this->$gateway['name']->Settings();
				$gateways[$key]['external'] = $settings['external'];
				$gateways[$key]['no_credit_card'] = $settings['no_credit_card'];
				$gateways[$key]['billing_address'] = ($settings['requires_customer_information'] == FALSE) ? FALSE: TRUE;
			}
		}
		
		// get billing address
		$billing = $this->user_model->get_billing_address($this->input->post('member'));
		
		$data = array(
					'form_title' => 'Create New Subscription (Step 2 of 2)',
					'form_action' => site_url('admincp/billing/post_new_subscription'),
					'states' => $states,
					'countries' => $countries,
					'gateways' => $gateways,
					'subscription' => $subscription,
					'billing' => $billing,
					'member' => $this->input->post('member')
					);
					
		$this->load->view('new_subscription_2.php', $data);
	}
	
	function post_new_subscription () {
		$this->load->model('gateway_model');
		
		$gateway_id = $this->input->post('gateway');
		
		// get gateway info
		$this->load->model('gateway_model');
		$gateway = $this->gateway_model->GetGatewayDetails($gateway_id);
		
		// load the gateway
		$this->load->library('payment/'.$gateway['name']);
		$settings = $this->$gateway['name']->Settings();
		
		// we can't only have an initial charge
		if ($this->input->post('initial_charge') > 0 and ($this->input->post('amount') == 0 or $this->input->post('amount') == '0.00')) {
			die(show_error('You can\'t have a free subscription with an initial charge.  This should just be a normal one-time charge product.'));
		}
		
		// force initial charge to be recurring charge if it's zero and there's no free trial
		if (($this->input->post('initial_charge') == 0 or $this->input->post('initial_charge') == '0.00') and ($this->input->post('free_trial') == 0)) {
			$initial_charge = $this->input->post('amount');
		}
		elseif ($this->input->post('free_trial') != 0) {
			$initial_charge = $this->input->post('amount');
		}
		else {
			$initial_charge = $this->input->post('initial_charge');
		}
		
		$amount = $initial_charge;
		
		if ($settings['external'] == FALSE and $settings['no_credit_card'] == FALSE and (int)$this->input->post('amount') != 0) {
			$credit_card = array(
								'card_num' => $this->input->post('cc_number'),
								'exp_month' => $this->input->post('cc_expiry_month'),
								'exp_year' => $this->input->post('cc_expiry_year'),
								'cvv' => $this->input->post('cc_security'),
								'name' => $this->input->post('cc_name')
							);
		}
		else {
			$user = $this->user_model->get_user($this->input->post('member'));
			
			$credit_card = FALSE;
		}
						
		$customer_id = $this->user_model->get_customer_id($this->input->post('member'));
						
		// shall we update the customer's billing address?		
		if ($settings['requires_customer_information'] != FALSE and !empty($amount) and $amount != '0.00') {
			// they passed a billing address, let's run the update
			$params = array();
			$params['first_name'] = $this->input->post('first_name');
			$params['last_name'] = $this->input->post('last_name');
			$params['company'] = $this->input->post('company');
			$params['address_1'] = $this->input->post('address_1');
			$params['address_2'] = $this->input->post('address_2');
			$params['city'] = $this->input->post('city');
			$params['state'] = $this->input->post('state');
			$params['postal_code'] = $this->input->post('postal_code');
			$params['country'] = $this->input->post('country');
			
			$this->load->model('customer_model');
			$this->customer_model->UpdateCustomer($customer_id, $params);
		}
		
		$customer_ip = $this->input->ip_address();
		
		// get true plan id
		$this->load->model('subscription_plan_model');
		$plan = $this->subscription_plan_model->get_plan($this->input->post('plan'));
		
		$recur = array(
						'plan_id' => $plan['plan_id'],
						'amount' => $this->input->post('amount')
					);
		
		// handle free trial
		if ($this->input->post('free_trial') > 0) {
			$recur['free_trial'] = $this->input->post('free_trial');
		}	
		
		// handle end date
		if ($this->input->post('no_enddate') != '1' and $this->input->post('end_date') != '') {
			$recur['end_date'] = $this->input->post('end_date');
		}		
					
		$return_url = site_url('admincp/users/profile/' . $this->input->post('member'));
		$cancel_url = site_url('admincp/billing/new_subscription/' . $this->input->post('member'));
		
		$response = $this->gateway_model->Recur($gateway_id, $amount, $credit_card, $customer_id, NULL, $customer_ip, $recur, $return_url, $cancel_url);
		
		if (isset($response['redirect'])) {
			header('Location: ' . $response['redirect']);
			return;
		}
		
		if (!is_array($response) or isset($response['error'])) {
			$this->notices->SetError('There was a system error processing this transaction: ' . $response['error_text'] . ' (#' . $response['error'] . ')');
			$error = TRUE;
		}
		elseif (isset($response['response_code']) and $response['response_code'] == '2') {
			$this->notices->SetError('There was a gateway error processing this transaction: ' . $response['response_text'] . '. ' . $response['reason'] . ' (#' . $response['response_code'] . ')');
			$error = TRUE;
		}
		else {
			$this->notices->SetNotice('Transaction successful.');
			$error = FALSE;
		}
		
		if (empty($error)) {
			redirect('admincp/users/profile/' . $this->input->post('member'));
		}
		else {
			redirect('admincp/billing/new_subscription/' . $this->input->post('member'));
		}
		
		return TRUE;
	}
	
	function subscriptions () {
		$this->load->library('dataset');
		
		$columns = array(
						array(
							'name' => 'ID #',
							'type' => 'id',
							'width' => '5%',
							'filter' => 'id'),
						array(
							'name' => 'Name',
							'type' => 'text',
							'filter' => 'name',
							'width' => '20%'),
						array(
							'name' => 'Price',
							'width' => '10%',
							),
						array(
							'name' => 'Bills Every',
							'type' => 'text',
							'filter' => 'interval',
							'width' => '10%'
							),
						array(
							'name' => 'Free Trial',
							'width' => '10%'
							),
						array(
							'name' => 'Subscribers',
							'width' => '10%'
							),
						array(
							'name' => 'Promotion',
							'width' => '10%'),
						array(
							'name' => 'Demotion',
							'width' => '10%'),
						array(
							'name' => '',
							'width' => '15%'
							)
					);
		
		$this->dataset->columns($columns);
		$this->dataset->datasource('subscription_plan_model','get_plans');
		$this->dataset->base_url(site_url('admincp/billing/subscriptions'));

		// total rows
		$total_rows = $this->db->where('deleted','0')->get('plans')->num_rows(); 
		$this->dataset->total_rows($total_rows);

		$this->dataset->initialize();

		// add actions
		$this->dataset->action('Delete','admincp/billing/delete_subscriptions');
		
		$this->load->model('users/usergroup_model');			
	    $usergroups = $this->usergroup_model->get_usergroups();
	    
	    $options = array();
	    foreach ($usergroups as $group) {
	    	$options[$group['id']] = $group['name'];
	    }
	    $usergroups = $options;
	    
	    $data = array(
	    			'usergroups' => $usergroups
	    		);
		
		$this->load->view('subscriptions.php', $data);
	}
	
	/**
	* Delete Subscription Plans
	*
	* Delete plans as passed from the dataset
	*
	* @param string Hex'd, base64_encoded, serialized array of plan ID's
	* @param string Return URL for Dataset
	*
	* @return bool Redirects to dataset
	*/
	function delete_subscriptions ($plans, $return_url) {
		$this->load->library('asciihex');
		
		$plans = unserialize(base64_decode($this->asciihex->HexToAscii($plans)));
		$return_url = base64_decode($this->asciihex->HexToAscii($return_url));
		
		$this->load->model('subscription_plan_model');
		
		foreach ($plans as $plan) {
			$this->subscription_plan_model->delete_plan($plan);
		}
		
		$this->notices->SetNotice('Plan(s) deleted successfully.');
		
		redirect($return_url);
		
		return TRUE;
	}
	
	function subscription_add ()
	{
		$this->load->library('admin_form');
		$form = new Admin_form;
		
		$this->load->model('users/usergroup_model');			
	    $usergroups = $this->usergroup_model->get_usergroups();
	    
	    $options = array();
	    $options[0] = 'No member group move';
	    foreach ($usergroups as $group) {
	    	$options[$group['id']] = $group['name'];
	    }
	    $usergroups = $options;
		
		$form->fieldset('Membership Options');
		$form->dropdown('Promotion','promotion',$usergroups, 0, FALSE, FALSE, 'Upon subscription, the member will be added to this usergroup.  They will be removed from this group upon expiration or cancellation.');
		$form->dropdown('Demotion','demotion',$usergroups, 0, FALSE, FALSE, 'Upon expiration or cancellation, the member will be added to this usergroup.');
		$form->fieldset('Description');
		$form->textarea('Description', 'description', '', 'This text may be displayed to the user in a list of subscription packages.', FALSE, 'basic', TRUE, '100%', '100px');
		
		$data = array(
					'member_form' => $form->display(),
					'form_title' => 'Create New Subscription Plan',
					'form_action' => site_url('admincp/billing/post_subscription/new'),
					'action' => 'new'
					);
		
		$this->load->view('subscription_form.php',$data);
	}
	
	function subscription_edit ($id)
	{
		$this->load->library('admin_form');
		$form = new Admin_form;
		
		$this->load->model('subscription_plan_model');
		$plan = $this->subscription_plan_model->get_plan($id);
		
		if (!$plan) {
			die(show_error('No subscription plan with that ID.'));
		}
		
		$this->load->model('users/usergroup_model');			
	    $usergroups = $this->usergroup_model->get_usergroups();
	    
	    $options = array();
	    $options[0] = 'No member group move';
	    foreach ($usergroups as $group) {
	    	$options[$group['id']] = $group['name'];
	    }
	    $usergroups = $options;
		
		$form->fieldset('Membership Options');
		$form->dropdown('Promotion','promotion',$usergroups, $plan['promotion'], FALSE, FALSE, 'Upon subscription, the member will be moved into this usergroup.  They will be removed from this group upon expiration or cancellation.');
		$form->dropdown('Demotion','demotion',$usergroups, $plan['demotion'], FALSE, FALSE, 'Upon expiration or cancellation, the member will be moved into this usergroup.');
		$form->fieldset('Description');
		$form->textarea('Description', 'description', $plan['description'], 'This text may be displayed to the user in a list of subscription packages.', FALSE, 'basic', TRUE, '100%', '100px');
		
		$data = array(
					'member_form' => $form->display(),
					'form_title' => 'Edit Subscription Plan',
					'form_action' => site_url('admincp/billing/post_subscription/edit/' . $plan['id']),
					'action' => 'edit',
					'form' => $plan
					);
		
		$this->load->view('subscription_form.php',$data);
	}
	
	function post_subscription ($action = 'new', $id = false) {		
		$this->load->library('field_validation');
		
		// manual validation
		if ($this->input->post('name') == '') {
			$this->notices->SetError('Plan Name is a required field.');
			$error = TRUE;
		}
		elseif ($this->input->post('interval') < 1 or !is_numeric($this->input->post('interval'))) {
			$this->notices->SetError('Charge Interval must be a number greater than 1.');
			$error = TRUE;
		}
		elseif ($this->input->post('plan_type') != 'free' and !$this->field_validation->ValidateAmount($this->input->post('amount'))) {
			$this->notices->SetError('Charge Amount is in an improper format.');
			$error = TRUE;
		}
		elseif ($this->input->post('occurrences_radio') != '0' and !is_numeric($this->input->post('occurrences'))) {
			$this->notices->SetError('Occurrences is in an improper format.');
			$error = TRUE;
		}
		elseif ($this->input->post('free_trial_radio') != '0' and !is_numeric($this->input->post('free_trial'))) {
			$this->notices->SetError('Free Trial is in an improper format.');
			$error = TRUE;
		}		
		
		if (isset($error)) {
			if ($action == 'new') {
				redirect('admincp/billing/subscription_add');
				return FALSE;
			}
			else {
				redirect('admincp/billing/subscription_edit/' . $id);
				return FALSE;
			}	
		}
		
		$this->load->model('subscription_plan_model');
		
		$name = $this->input->post('name');
		$amount = ($this->input->post('plan_type') == 'free') ? '0' : $this->input->post('amount');
		$initial_charge = ($this->input->post('initial_charge_same') == '1') ? $amount : $this->input->post('initial_charge');
		$interval = $this->input->post('interval');
		$free_trial = ($this->input->post('free_trial_radio') == '0') ? '0' : $this->input->post('free_trial');
		$require_billing_for_trial = ($this->input->post('require_billing_for_trial') == '0') ? FALSE : TRUE;
		$occurrences = ($this->input->post('occurrences_radio') == '0') ? '0' : $this->input->post('occurrences');
		$promotion = $this->input->post('promotion');
		$demotion = $this->input->post('demotion');
		$description = $this->input->post('description');
		$is_taxable = ($this->input->post('taxable') == '1') ? TRUE : FALSE;
		
		if ($action == 'new') {
			$plan_id = $this->subscription_plan_model->new_plan($name, $amount, $initial_charge, $is_taxable, $interval, $free_trial, $require_billing_for_trial, $occurrences, $promotion, $demotion, $description);
			$this->notices->SetNotice('Plan added successfully.');
		}
		else {
			$this->subscription_plan_model->update_plan($id, $name, $amount, $initial_charge, $is_taxable, $interval, $free_trial, $require_billing_for_trial, $occurrences, $promotion, $demotion, $description);
			$this->notices->SetNotice('Plan updated successfully.');
		}
		
		redirect('admincp/billing/subscriptions');
		
		return TRUE;
	}
	
	/**
	* Manage gateways
	*
	* Lists active gateways for managing
	*/
	function gateways()
	{	
		$this->admin_navigation->parent_active('configuration');

		$this->load->library('dataset');
		
		$columns = array(
						array(
							'name' => 'ID #',
							'type' => 'id',
							'width' => '5%',
							'filter' => 'id'),
						array(
							'name' => 'Gateway',
							'type' => 'text',
							'width' => '40%'),
						array(
							'name' => 'Date Created',
							'width' => '25%',
							'type' => 'date'),
						array(
							'name' => '',
							'width' => '25%'
							)
					);
		
		$this->dataset->columns($columns);
		$this->dataset->datasource('gateway_model','GetGateways');
		$this->dataset->base_url(site_url('admincp/gateways'));
		$this->dataset->rows_per_page(1000);

		$this->dataset->initialize();

		// add actions
		$this->dataset->action('Delete','admincp/billing/delete_gateways');
		
		$this->load->view('gateways.php');
	}
	
	/**
	* Delete Gateways
	*
	* Delete gateways as passed from the dataset
	*
	* @param string Hex'd, base64_encoded, serialized array of gateway ID's
	* @param string Return URL for Dataset
	*
	* @return bool Redirects to dataset
	*/
	function delete_gateways ($gateways, $return_url) {
		$this->load->model('gateway_model');
		$this->load->library('asciihex');
		
		$gateways = unserialize(base64_decode($this->asciihex->HexToAscii($gateways)));
		$return_url = base64_decode($this->asciihex->HexToAscii($return_url));
		
		foreach ($gateways as $gateway) {
			$this->gateway_model->DeleteGateway($gateway);
		}
		
		$this->notices->SetNotice('Gateway deleted successfully.');
		
		redirect($return_url);
		return TRUE;
	}
	
	/**
	* New Gateway
	*
	* Create a new gateway
	*
	* @return true Passes to view
	*/
	function new_gateway ()
	{
		$this->admin_navigation->parent_active('configuration');

		$this->load->model('gateway_model');
		$gateways = $this->gateway_model->GetExternalAPIs();
		
		$data = array(
					'gateways' => $gateways
					);
		
		$this->load->view('new_gateway_type',$data);
	}
	
	/**
	* New Gateway Step 2
	*
	* Create a new gateway
	*
	* @return true Passes to view
	*/
	function new_gateway_details ()
	{
		$this->admin_navigation->parent_active('configuration');

		if ($this->input->post('external_api') == '') {
			redirect('admincp/billings/new_gateway');
			return FALSE;
		}
		else {
			$this->load->library('payment/' . $this->input->post('external_api'), $this->input->post('external_api'));
			$class = $this->input->post('external_api');
			$settings = $this->$class->Settings();
		}
		
		$data = array(
					'form_title' => $settings['name'] . ': Details',
					'form_action' => site_url('admincp/billing/post_gateway/new'),
					'external_api' => $this->input->post('external_api'),
					'name' => $settings['name'],
					'fields' => $settings['field_details']
					);
		
		$this->load->view('gateway_details.php',$data);
	}
	
	/**
	* Handle New/Edit Gateway Post
	*/
	function post_gateway ($action, $id = false) {		
		if ($this->input->post('external_api') == '') {
			$this->notices->SetError('No external API ID in form posting.');
			$error = TRUE;
		}
		else {
			$this->load->library('payment/' . $this->input->post('external_api'), $this->input->post('external_api'));
			$class = $this->input->post('external_api');
			$settings = $this->$class->Settings();
		}
		
		$gateway = array();
		
		foreach ($settings['field_details'] as $name => $details) {
			$gateway[$name] = $this->input->post($name);
			
			if ($this->input->post($name) == '') {
				$this->notices->SetError('Required field missing: ' . $details['text']);
				$error = TRUE;
			}
		}
		reset($settings['field_details']);
		
		if (isset($error)) {
			if ($action == 'new') {
				redirect('admincp/billing/new_gateway');
				return FALSE;
			}
			else {
				redirect('admincp/billing/edit_gateway/' . $id);
			}	
		}
		
		$params = array(
						'gateway_type' => $this->input->post('external_api'),
						'alias' => $this->input->post('alias')
					);
					
		foreach ($settings['field_details'] as $name => $details) {
			$params[$name] = $this->input->post($name);
		}
		
		$this->load->model('gateway_model');
		
		if ($action == 'new') {
			$gateway_id = $this->gateway_model->NewGateway($params);
			
			$gateway = $this->gateway_model->GetGatewayDetails($gateway_id);
			
			// test gateway
			$test = $this->$class->TestConnection($gateway);
			
			if (!$test) {
				$this->gateway_model->DeleteGateway($gateway_id,TRUE);
				
				$this->notices->SetError('Unable to establish a test connection.  Your details may be incorrect.');
				
				if ($action == 'new') {
					redirect('admincp/billing/new_gateway');
					return FALSE;
				}
				else {
					redirect('admincp/billing/edit_gateway/' . $id);
				}	
			}
			
			$this->notices->SetNotice('Gateway added succesfully.');
		}
		else {
			$params['gateway_id'] = $id;
			
			$this->gateway_model->UpdateGateway($params);
			$this->notices->SetNotice('Gateway updated successfully.');
		}
		
		redirect('admincp/billing/gateways');
		
		return TRUE;
	}
	
	/**
	* Edit Gateway
	*
	* Show the gateway form, preloaded with variables
	*
	* @param int $id the ID of the gateway
	*
	* @return string The email form view
	*/
	function edit_gateway($id) {
		$this->admin_navigation->parent_active('configuration');

		$this->load->model('gateway_model');
		$gateway = $this->gateway_model->GetGatewayDetails($id);
	
		$this->load->library('payment/' . $gateway['name'], $gateway['name']);
		$settings = $this->$gateway['name']->Settings();

		$data = array(
					'form_title' => $settings['name'] . ': Details',
					'form_action' => site_url('admincp/billing/post_gateway/edit/' . $id),
					'external_api' => $gateway['name'],
					'name' => $gateway['alias'],
					'fields' => $settings['field_details'],
					'values' => $gateway
					);
		
		$this->load->view('gateway_details.php',$data);
	}
	
	/**
	* Make Default Gateway
	*/
	function make_default_gateway ($id) {
		$this->load->model('gateway_model');
		$this->gateway_model->MakeDefaultGateway($id);
		
		$this->notices->SetNotice('Default gateway updated successfully.');
		
		redirect(site_url('admincp/billing/gateways'));
	}
}