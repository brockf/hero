<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Admincp extends Admincp_Controller {
	function __construct()
	{
		parent::__construct();
		
		$this->navigation->parent_active('configuration');
	}
	
	/**
	* Manage emails
	*
	* Lists active emails for managing
	*/
	function index()
	{	
		$this->load->model('admincp/dataset','dataset');
		
		$columns = array(
						array(
							'name' => 'ID #',
							'sort_column' => 'id',
							'type' => 'id',
							'width' => '5%',
							'filter' => 'id'),
						array(
							'name' => 'Trigger',
							'sort_column' => 'emails.trigger',
							'type' => 'text',
							'width' => '20%',
							'filter' => 'trigger'),
						array(
							'name' => 'To:',
							'width' => '15%',
							'sort_column' => 'emails.to_address',
							'filter' => 'to_address',
							'type' => 'text'),
						array(
							'name' => 'Email Subject',
							'sort_column' => 'emails.email_subject',
							'type' => 'text',
							'width' => '25%',
							'filter' => 'email_subject'),
						array(
							'name' => 'Format',
							'width' => '5%')
					);
		
		// handle recurring plans if they exist
		$this->load->model('billing/plan_model');
		$plans = $this->plan_model->GetPlans();
		
		$options = array();
		if ($plans) {
			// build $options
			$options['-1'] = 'No plans.';
			$options['0'] = 'All plans.';
			while (list(,$plan) = each($plans)) {
				$options[$plan['id']] = $plan['name'];
			}
			
			$columns[] = array(
							'name' => 'Plan Link',
							'type' => 'select',
							'options' => $options,
							'filter' => 'plan_id',
							'width' => '14%'
							);
		}
		else {
			$columns[] = array(
				'name' => 'Plan Link',
				'width' => '14%'
				);
		}
		
		$columns[] = array(
						'name' => '',
						'width' => '6%'
				);
		
		$this->dataset->Initialize('email_model','GetEmails',$columns, site_url('admincp/emails'));

		// add actions
		$this->dataset->Action('Delete','settings/delete_emails');
		
		$this->load->view('emails.php', array('plans' => $options));
	}
	
	/**
	* Delete Emails
	*
	* Delete emails as passed from the dataset
	*
	* @param string Hex'd, base64_encoded, serialized array of email ID's
	* @param string Return URL for Dataset
	*
	* @return bool Redirects to dataset
	*/
	function delete_emails ($emails, $return_url) {
		$this->load->model('email_model');
		$this->load->library('asciihex');
		
		$emails = unserialize(base64_decode($this->asciihex->HexToAscii($emails)));
		$return_url = base64_decode($this->asciihex->HexToAscii($return_url));
		
		foreach ($emails as $email) {
			$this->email_model->DeleteEmail($this->user->Get('client_id'),$email);
		}
		
		$this->notices->SetNotice('Email deleted successfully.');
		
		redirect($return_url);
		return true;
	}
	
	/**
	* New Email
	*
	* Create a new email
	*
	* @return true Passes to view
	*/
	function new_email ()
	{
		$this->load->model('email_model');
		$this->load->model('billing/plan_model');
		
		$triggers = $this->email_model->GetTriggers();
		$plans = $this->plan_model->GetPlans();
		
		if ($plans === FALSE) { 
			$plans = array();
		}
		
		$data = array(
					'triggers' => $triggers,
					'plans' => $plans,
					'form_title' => 'Create New Email',
					'form_action' => 'admincp/emails/post_email/new'
					);
				
		$this->load->view('email_form.php',$data);
	}
	
	/**
	* Handle New/Edit Email Post
	*/
	function post_email ($action, $id = false) {		
		if ($this->input->post('email_body') == '') {
			$this->notices->SetError('Email Body is a required field.');
			$error = true;
		}
		elseif ($this->input->post('email_subject') == '') {
			$this->notices->SetError('Email Subject is a required field.');
			$error = true;
		}
		
		if (isset($error)) {
			if ($action == 'new') {
				redirect('admincp/emails/new_email');
				return false;
			}
			else {
				redirect('admincp/emails/edit_email/' . $id);
			}	
		}
		
		$params = array(
						'email_subject' => $this->input->post('email_subject',true),
						'email_body' => $this->input->post('email_body',true),
						'from_name' => '',
						'from_email' => '',
						'plan' => $this->input->post('plan',true),
						'is_html' => $this->input->post('is_html',true),
						'to_address' => ($this->input->post('to_address') == 'email') ? $this->input->post('to_address_email') : 'customer',
						'bcc_address' => ($this->input->post('bcc_address') == 'client' or $this->input->post('bcc_address') == '') ? $this->input->post('bcc_address',true) : $this->input->post('bcc_address_email')
					);
		
		$this->load->model('email_model');
		
		if ($action == 'new') {
			$email_id = $this->email_model->SaveEmail($this->input->post('trigger'), $params);
			$this->notices->SetNotice('Email added successfully.');
		}
		else {
			$this->email_model->UpdateEmail($id, $params, $this->input->post('trigger'));
			$this->notices->SetNotice('Email edited successfully.');
		}
		
		redirect('admincp/emails');
		
		return TRUE;
	}
	
	/**
	* Edit Email
	*
	* Show the email form, preloaded with variables
	*
	* @param int $id the ID of the email
	*
	* @return string The email form view
	*/
	function edit_email($id) {
		$this->load->model('email_model');
		$this->load->model('billing/plan_model');
		
		$triggers = $this->email_model->GetTriggers();
		$plans = $this->plan_model->GetPlans();
		
		if ($plans === FALSE) { 
			$plans = array();
		}
		
		// preload form variables
		$email = $this->email_model->GetEmail($id);
		
		$data = array(
					'triggers' => $triggers,
					'plans' => $plans,
					'form' => $email,
					'form_title' => 'Edit Email',
					'form_action' => 'admincp/emails/post_email/edit/' . $email['id']
					);
				
		$this->load->view('email_form.php',$data);
	}
	
	/**
	* Show Available Variables
	*
	* Show the available variables for a trigger
	*
	* @param int $trigger_id The ID of the trigger
	*
	* @return string An unordered HTML list of available variables
	*/
	function show_variables ($trigger_id) {
		$this->load->model('email_model');
		
		$variables = $this->email_model->GetEmailVariables($trigger_id);
		
		$return = '<p><b>Available Variables for this Trigger Type</b>.  Note: Not all values are available
				   for each event.  For example, "[[CUSTOMER_ADDRESS_1]]" cannot be replaced if the customer
				   does not have an address registered in the system.</p>
				   <p><i>Usage Example: [[AMOUNT]] will be replaced by a value like "34.95" in the email.</i></p><ul>';
		foreach ($variables as $variable) {
			$return .= '<li>[[' . strtoupper($variable) . ']]</li>';
		}
		
		$return .= '</ul><div style="clear:both"></div>';
		
		echo $return;
		
		return true;
	}
}