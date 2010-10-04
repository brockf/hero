<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/*
* Emails Control Panel
*
* Displays all control panel forms, datasets, and other displays
*
* @author Electric Function, Inc.
* @copyright Electric Function, Inc.
* @package Electric Publisher
*
*/

class Admincp extends Admincp_Controller {
	function __construct()
	{
		parent::__construct();
		
		$this->navigation->parent_active('configuration');
		
		$this->navigation->module_link('New Email',site_url('admincp/emails/new_email'));
	}
	
	/**
	* Manage emails
	*
	* Lists active emails for managing
	*/
	function index()
	{
		$this->load->library('dataset');
		
		$columns = array(
						array(
							'name' => 'ID #',
							'sort_column' => 'id',
							'type' => 'id',
							'width' => '5%',
							'filter' => 'id'),
						array(
							'name' => 'Hook',
							'sort_column' => 'emails.hook',
							'type' => 'text',
							'width' => '20%',
							'filter' => 'trigger'),
						array(
							'name' => 'Parameters',
							'width' => '15%'
							),
						array(
							'name' => 'To',
							'width' => '15%'
							),
						array(
							'name' => 'Email Subject',
							'width' => '25%'
							),
						array(
							'name' => 'Format',
							'width' => '10%'),
						array(
							'name' => '',
							'width' => '10%'
						)
					);
				
		$this->dataset->columns($columns);
		$this->dataset->datasource('email_model','get_emails');
		$this->dataset->base_url(site_url('admincp/emails'));
		$this->dataset->rows_per_page(1000);
		
		// total rows
		$this->db->where('email_deleted', '0');
		$total_rows = $this->db->get('emails')->num_rows(); 
		$this->dataset->total_rows($total_rows);
		
		// initialize the dataset
		$this->dataset->initialize();

		// add actions
		$this->dataset->action('Delete','admincp/emails/delete_emails');
		
		$this->load->view('emails');
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
			$this->email_model->delete_email($email);
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
	function new_email () {
		$data = array(
					'hooks' => $this->app_hooks->get_hooks(),
					'form_action' => site_url('admincp/emails/new_email_2')
					);
				
		$this->load->view('select_hook',$data);
	}
	
	/**
	* New Email Step 2
	*
	* Create a new email
	*
	* @return true Passes to view
	*/
	function new_email_2 () {
		$this->load->model('billing/subscription_plan_model');
		$this->load->model('store/products_model');
		
		$plans = $this->subscription_plan_model->get_plans();
		$products = $this->products_model->get_products();
		
		$data = array(
					'hook' => $this->app_hooks->get_hook($this->input->get('hook')),
					'products' => $products,
					'plans' => $plans,
					'form_title' => 'Create New Email',
					'form_action' => site_url('admincp/emails/post_email/new')
					);
				
		$this->load->view('email_form',$data);
	}
	
	/**
	* Handle New/Edit Email Post
	*/
	function post_email ($action, $id = false) {		
		// build values
		$hook = $this->input->post('hook');
		
		// to:
		$to = array();
		if ($this->input->post('to_member') == '1') {
			$to[] = 'member';
		}
		if ($this->input->post('to_admin') == '1') {
			$to[] = 'admin';
		}
		if ($this->input->post('to_others')) {
			$others = explode(',', $this->input->post('to_others'));
			foreach ($others as $email) {
				$email = trim($email);
				if (!empty($email)) {
					$to[] = $email;
				}
			}
		}
		
		// bcc:
		$bcc = array();
		if ($this->input->post('bcc_member') == '1') {
			$bcc[] = 'member';
		}
		if ($this->input->post('bcc_admin') == '1') {
			$bcc[] = 'admin';
		}
		if ($this->input->post('bcc_others')) {
			$others = explode(',', $this->input->post('bcc_others'));
			foreach ($others as $email) {
				$email = trim($email);
				if (!empty($email)) {
					$bcc[] = $email;
				}
			}
		}
		
		// parameters
		$parameters = array();
		$params = isset($_POST['param']) ? $_POST['param'] : FALSE;
		if (!empty($params)) {
			foreach ($params as $key => $param) {
				if (!empty($_POST['param_value'][$key])) {
					$parameters[$param . ' ' . $_POST['operator'][$key]] = $_POST['param_value'][$key];
				}
			}
		}
		
		// is_html
		$is_html = ($this->input->post('is_html') == '1') ? TRUE : FALSE;
		
		// content
		$subject = $this->input->post('subject');
		$body = $this->input->post('body');
			
		$this->load->model('email_model');
		
		if ($action == 'new') {
			$email_id = $this->email_model->new_email(
													$hook,
													$parameters,
													$to,
													$bcc,
													$subject,
													$body,
													$is_html
												);
												
			$this->notices->SetNotice('Email added successfully.');
		}
		else {
			$email_id = $this->email_model->update_email(
													$this->input->post('email_id'),
													$hook,
													$parameters,
													$to,
													$bcc,
													$subject,
													$body,
													$is_html
												);

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
		$this->load->model('billing/subscription_plan_model');
		$this->load->model('store/products_model');
		$this->load->model('emails/email_model');
		
		$email = $this->email_model->get_email($id);
		$plans = $this->subscription_plan_model->get_plans();
		$products = $this->products_model->get_products();
		
		// get email body from template file
		$this->load->helper('file');
		$email['body'] = read_file(setting('path_email_templates') . '/' . $email['body_template']);
		
		$data = array(
					'hook' => $this->app_hooks->get_hook($email['hook']),
					'products' => $products,
					'plans' => $plans,
					'form' => $email,
					'form_title' => 'Edit Email',
					'form_action' => site_url('admincp/emails/post_email/edit/' . $email['id'])
					);
				
		$this->load->view('email_form',$data);
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
		
		$return = '<p><b>Available Variables for this Trigger Type</b></p>
				   <ul class="notes">
				   		<li>Not all values are available for each event.  For example, <span class="var">[[CUSTOMER_ADDRESS_1]]</span> cannot be replaced if the customer
				  	    does not have an address registered in the system.</li>
				   		<li>Usage Example: <span class="var">[[AMOUNT]]</span> will be replaced by a value like "34.95" in the email.</li>
				   		<li>To format dates, you can include a parameter in the variable such as, <span class="var">[[DATE|"M d, Y"]]</span> (output example: Aug 19, 2010).  You can
				   		specify any date format using either of PHP\'s <a href="http://www.php.net/date">date()</a> and <a href="http://www.php.net/strftime">strftime()</a>
				   		formatting styles.</li>
				   </ul>
				   <ul class="variables">';
				   
		// default variable added later
		$return .= '<li>[[SITE_NAME]]</li>';
		
		foreach ($variables as $variable) {
			$return .= '<li>[[' . strtoupper($variable) . ']]</li>';
		}
		
		$return .= '</ul><div style="clear:both"></div>';
		
		echo $return;
		
		return true;
	}
}