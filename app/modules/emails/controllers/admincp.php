<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/*
* Emails Control Panel
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
		
		$this->admin_navigation->parent_active('configuration');
		
		$this->admin_navigation->module_link('New Email',site_url('admincp/emails/new_email'));
		$this->admin_navigation->module_link('Edit Global Email Layout',site_url('admincp/emails/email_layout'));
	}
	
	function send() {
		$this->admin_navigation->clear_module_links();
	
		// get usergroups
		$this->load->model('users/usergroup_model');
		$usergroups = $this->usergroup_model->get_usergroups();
		
		// get templates
		$this->load->model('emails/email_template_model');
		$templates = $this->email_template_model->get_templates();
		
		// have we been passed users to pre-populate this method with?
		$passed_users = array();
		
		$users = $this->session->flashdata('email_users');
		if (!empty($users)) {
			foreach ($users as $user) {
				$result = $this->db->select('user_email')
								   ->select('user_first_name')
								   ->select('user_last_name')
								   ->select('user_id')
								   ->from('users')
								   ->where('user_id',$user)
								   ->get();
								   
				if ($result->num_rows() > 0) {
					$user = $result->row_array();
				
					$passed_users[] = array(
									'name' => $user['user_last_name'] . ', ' . $user['user_first_name'],
									'email' => $user['user_email'],
									'id' => $user['user_id']
									);
				}								   
			}
		}
		
		// get CKEditor to show
		if (!defined('INCLUDE_CKEDITOR')) {
			define('INCLUDE_CKEDITOR','TRUE');
		}
		
		$data = array(
					'usergroups' => $usergroups,
					'templates' => $templates,
					'passed_users' => $passed_users
					);
	
		$this->load->view('send', $data);
	}
	
	function post_send() {
		$this->load->library('form_validation');
		$this->form_validation->set_rules('subject','Subject','required');
		$this->form_validation->set_rules('body','Body','required');
		
		if ($this->form_validation->run() === FALSE) {
			// errors
			$this->notices->SetError(validation_errors());
			
			return redirect('admincp/emails/send');
		}
		
		$recipients = array();
		$queue_mail = FALSE;
		$is_html = ($this->input->post('html') == '1') ? TRUE : FALSE;
		
		// build list of recipients
		if (!empty($_POST['recipient_groups'])) {
			foreach ($_POST['recipient_groups'] as $group) {
				$members = $this->user_model->get_users(array('group' => $group));
				
				if (!empty($members)) {	
					$queue_mail = TRUE;
								
					foreach ($members as $member) {
						$recipients[] = array('email' => $member['email'], 'first_name' => $member['first_name'], 'last_name' => $member['last_name']);
					}
				}
			}
		}
		
		if (!empty($_POST['recipient_members'])) {
			foreach ($_POST['recipient_members'] as $member) {
				$member = $this->user_model->get_user($member);
				
				if (!empty($member)) {
					$recipients[] = array('email' => $member['email'], 'first_name' => $member['first_name'], 'last_name' => $member['last_name']);
				}
			}
		}
		
		if (!empty($_POST['recipient_emails'])) {
			foreach ($_POST['recipient_emails'] as $email) {
				$recipients[] = array('email' => $email, 'first_name' => 'FirstName', 'last_name' => 'LastName');
			}
		}
		
		if (empty($recipients)) {
			$this->notices->SetError('No recipients were selected.');
			
			return redirect('admincp/emails/send');
		}
		
		// send, but not to duplicates
		// initialize email
		$this->load->library('email');
		
		$settings = array();
		if ($is_html === TRUE) {
			$settings['mailtype'] = 'html';
		}
		
		$this->email->initialize($settings);
		
		// track duplicates
		$sent = array();
		
		foreach ($recipients as $recipient) {
			if (in_array($recipient['email'], $sent)) {
				continue;
			}
			
			// variables
			$search = array('[member_first_name]', '[member_last_name]', '[member_email]');
			$replace = array($recipient['first_name'], $recipient['last_name'], $recipient['email']);
			
			// parse message
			$subject = str_ireplace($search, $replace, $this->input->post('subject'));
			$body = str_ireplace($search, $replace, $this->input->post('body'));
			
			// we may be sending images with a relative link...
			$body = str_ireplace('src="writeable/','src="' . base_url() . '/writeable/', $body);
		
			// send full email
			$this->email->from(setting('site_email'), setting('site_name'));
			$this->email->to($recipient['email']);
			
			$this->email->subject($subject);
			$this->email->message($body);
			
			// app hook
			$this->app_hooks->trigger('mass_email_pre', $recipient, $this->input->post('subject'), $this->input->post('body'), $queue_mail);
			$this->app_hooks->reset();
			
			$this->email->send($queue_mail);
			$this->email->clear();
			
			$sent[] = $recipient['email'];
		}
		
		// save as new template?
		if ($this->input->post('new_template')) {
			$this->load->model('emails/email_template_model');
			$this->email_template_model->new_template($this->input->post('new_template_name'), $this->input->post('subject'), $this->input->post('body'), $is_html);
		}
		
		// app hook
		$this->app_hooks->trigger('mass_email', $recipients, $this->input->post('subject'), $this->input->post('body'), $queue_mail);
		$this->app_hooks->reset();
		
		$this->notices->SetNotice('Email sent successfully to ' . count($sent) . ' members.');
		
		return redirect('admincp/emails/send');
	}
	
	function member_search () {
		$members = $this->user_model->get_users(array(
											'keyword' => $this->input->post('keyword'),
											'limit' => '50'
										));
		
		$this->load->helper('array_to_json');
		
		if (empty($members)) {
			return print(array_to_json(array()));
		}
		
		$results = array();
		foreach ($members as $member) {
			$results[$member['id']] = $member['last_name'] . ', ' . $member['first_name'] . ' (' . $member['email'] . ')';
		}
		
		return print(array_to_json($results));
	}
	
	function load_template_subject () {
		$this->load->model('emails/email_template_model');
		$template = $this->email_template_model->get_template($this->input->post('template_id'));
		
		if (empty($template)) {
			return '';
		}
		
		return print($template['subject']);
	}
	
	function load_template_body () {
		$this->load->model('emails/email_template_model');
		$template = $this->email_template_model->get_template($this->input->post('template_id'));
		
		if (empty($template)) {
			return '';
		}
		
		return print($template['body']);
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
		if (module_installed('billing')) {
			$this->load->model('billing/subscription_plan_model');
			$plans = $this->subscription_plan_model->get_plans();
		}
		else {
			$plans = FALSE;
		}
		
		
		if (module_installed('store')) {
			$this->load->model('store/products_model');
			$products = $this->products_model->get_products();
		}
		else {
			$products = FALSE;
		}
		
		$hook = $this->app_hooks->get_hook($this->input->get('hook'));
		
		$data = array(
					'hook' => $hook,
					'variables' => $this->email_variables($hook['email_data'], $hook['other_email_data']),
					'products' => $products,
					'plans' => $plans,
					'form_title' => 'Create New Email',
					'form_action' => site_url('admincp/emails/post_email/new')
					);
				
		$this->load->view('email_form',$data);
	}
	
	/**
	* Email Variables
	*
	* Return a list of possible email variables
	*
	* @param array $email_data from get_hook().  The "email_data" array stored for the hook
	* @param array $other_email_data The "other_email_data" array stored for the hook
	*
	* @return array sorted alphabetically
	*/
	function email_variables ($email_data, $other_email_data = array()) {
		$vars = array();
		
		if (is_array($email_data) and in_array('member', $email_data)) {
			// dynamically load variables from a get_user() call
			$user = $this->user_model->get();
			
			foreach ($user as $key => $v) {
				if (is_bool($v)) {
					$type = 'boolean';
				}
				elseif (is_numeric($v)) {
					$type = 'integer';
				}
				elseif (is_array($v)) {
					$type = 'array';
				}
				else {
					$type = 'string';
				}
				$vars[] = array('tag' => '{$member.' . $key . '}', 'type' => $type);
			}
		}
		
		if (is_array($email_data) and in_array('product', $email_data)) {
			$vars[] = array('tag' => '{$product.id}', 'type' => 'integer');
			$vars[] = array('tag' => '{$product.url}', 'type' => 'url');
			$vars[] = array('tag' => '{$product.url_path}', 'type' => 'url_path');
			$vars[] = array('tag' => '{$product.quick_add_to_cart_url}', 'type' => 'url');
			$vars[] = array('tag' => '{$product.collections}', 'type' => 'array');
			$vars[] = array('tag' => '{$product.name}', 'type' => 'string');
			$vars[] = array('tag' => '{$product.description}', 'type' => 'string');
			$vars[] = array('tag' => '{$product.price}', 'type' => 'float');
			$vars[] = array('tag' => '{$product.weight}', 'type' => 'float');
			$vars[] = array('tag' => '{$product.requires_shipping}', 'type' => 'boolean');
			$vars[] = array('tag' => '{$product.track_inventory}', 'type' => 'boolean');
			$vars[] = array('tag' => '{$product.inventory}', 'type' => 'integer');
			$vars[] = array('tag' => '{$product.inventory_allow_oversell}', 'type' => 'boolean');
			$vars[] = array('tag' => '{$product.sku}', 'type' => 'string');
			$vars[] = array('tag' => '{$product.is_taxable}', 'type' => 'boolean');
			$vars[] = array('tag' => '{$product.is_download}', 'type' => 'boolean');
			$vars[] = array('tag' => '{$product.download_name}', 'type' => 'string');
			$vars[] = array('tag' => '{$product.download_size}', 'type' => 'integer');
			$vars[] = array('tag' => '{$product.feature_image}', 'type' => 'filename');
			$vars[] = array('tag' => '{$product.feature_image_url}', 'type' => 'url');
		}
		
		if (is_array($email_data) and in_array('order', $email_data)) {
			$vars[] = array('tag' => '{$order_totals.shipping}', 'type' => 'float');						
			$vars[] = array('tag' => '{$order_totals.subtotal}', 'type' => 'float');
			$vars[] = array('tag' => '{$order_totals.tax}', 'type' => 'float');
			$vars[] = array('tag' => '{$order_totals.discount}', 'type' => 'float');
			$vars[] = array('tag' => '{$order_totals.total}', 'type' => 'float');
		
			$vars[] = array('tag' => '{$shipping_address}', 'type' => 'string');
			$vars[] = array('tag' => '{$products}', 'type' => 'array');
			$vars[] = array('tag' => '{$products.X.id}', 'type' => 'integer');
			$vars[] = array('tag' => '{$products.X.url}', 'type' => 'url');
			$vars[] = array('tag' => '{$products.X.url_path}', 'type' => 'url_path');
			$vars[] = array('tag' => '{$products.X.quick_add_to_cart_url}', 'type' => 'url');
			$vars[] = array('tag' => '{$products.X.collections}', 'type' => 'array');
			$vars[] = array('tag' => '{$products.X.name}', 'type' => 'string');
			$vars[] = array('tag' => '{$products.X.description}', 'type' => 'string');
			$vars[] = array('tag' => '{$products.X.quantity}', 'type' => 'integer');
			$vars[] = array('tag' => '{$products.X.price}', 'type' => 'float');
			$vars[] = array('tag' => '{$products.X.weight}', 'type' => 'float');
			$vars[] = array('tag' => '{$products.X.requires_shipping}', 'type' => 'boolean');
			$vars[] = array('tag' => '{$products.X.track_inventory}', 'type' => 'boolean');
			$vars[] = array('tag' => '{$products.X.inventory}', 'type' => 'integer');
			$vars[] = array('tag' => '{$products.X.inventory_allow_oversell}', 'type' => 'boolean');
			$vars[] = array('tag' => '{$products.X.sku}', 'type' => 'string');
			$vars[] = array('tag' => '{$products.X.is_taxable}', 'type' => 'boolean');
			$vars[] = array('tag' => '{$products.X.is_download}', 'type' => 'boolean');
			$vars[] = array('tag' => '{$products.X.download_name}', 'type' => 'string');
			$vars[] = array('tag' => '{$products.X.download_size}', 'type' => 'integer');
			$vars[] = array('tag' => '{$products.X.feature_image}', 'type' => 'filename');
			$vars[] = array('tag' => '{$products.X.feature_image_url}', 'type' => 'url');
		}
		
		if (is_array($email_data) and in_array('invoice', $email_data)) {
			$vars[] = array('tag' => '{$billing_address}', 'type' => 'string');
			$vars[] = array('tag' => '{$invoice.id}', 'type' => 'integer');
			$vars[] = array('tag' => '{$invoice.gateway}', 'type' => 'string');
			$vars[] = array('tag' => '{$invoice.date}', 'type' => 'date');
			$vars[] = array('tag' => '{$invoice.amount}', 'type' => 'float');
			$vars[] = array('tag' => '{$invoice.card_last_four}', 'type' => 'integer');
			$vars[] = array('tag' => '{$invoice.tax_name}', 'type' => 'string');
			$vars[] = array('tag' => '{$invoice.tax_paid}', 'type' => 'float');
			$vars[] = array('tag' => '{$invoice.tax_rate}', 'type' => 'float');
			$vars[] = array('tag' => '{$invoice.shipping_name}', 'type' => 'string');
			$vars[] = array('tag' => '{$invoice.coupon_id}', 'type' => 'int');
			$vars[] = array('tag' => '{$invoice.coupon_name}', 'type' => 'string');
			$vars[] = array('tag' => '{$invoice.coupon_code}', 'type' => 'string');
			$vars[] = array('tag' => '{$invoice.billing_address}', 'type' => 'array');
			$vars[] = array('tag' => '{$invoice.billing_address.first_name}', 'type' => 'string');
			$vars[] = array('tag' => '{$invoice.billing_address.last_name}', 'type' => 'string');
			$vars[] = array('tag' => '{$invoice.billing_address.company}', 'type' => 'string');
			$vars[] = array('tag' => '{$invoice.billing_address.address_1}', 'type' => 'string');
			$vars[] = array('tag' => '{$invoice.billing_address.address_2}', 'type' => 'string');
			$vars[] = array('tag' => '{$invoice.billing_address.city}', 'type' => 'string');
			$vars[] = array('tag' => '{$invoice.billing_address.state}', 'type' => 'string');
			$vars[] = array('tag' => '{$invoice.billing_address.country}', 'type' => 'string');
			$vars[] = array('tag' => '{$invoice.billing_address.postal_code}', 'type' => 'string');
			$vars[] = array('tag' => '{$invoice.billing_address.email}', 'type' => 'string');
		}
		
		if (is_array($email_data) and in_array('subscription', $email_data)) {
			$vars[] = array('tag' => '{$subscription.id}', 'type' => 'integer');
			$vars[] = array('tag' => '{$subscription.gateway_id}', 'type' => 'integer');
			$vars[] = array('tag' => '{$subscription.date_created}', 'type' => 'date');
			$vars[] = array('tag' => '{$subscription.amount}', 'type' => 'float');
			$vars[] = array('tag' => '{$subscription.interval}', 'type' => 'integer');
			$vars[] = array('tag' => '{$subscription.start_date}', 'type' => 'date');
			$vars[] = array('tag' => '{$subscription.end_date}', 'type' => 'date');
			$vars[] = array('tag' => '{$subscription.last_charge_date}', 'type' => 'date');
			$vars[] = array('tag' => '{$subscription.next_charge_date}', 'type' => 'date');
			$vars[] = array('tag' => '{$subscription.cancel_date}', 'type' => 'date');
			$vars[] = array('tag' => '{$subscription.number_occurrences}', 'type' => 'integer');
			$vars[] = array('tag' => '{$subscription.active}', 'type' => 'boolean');
			$vars[] = array('tag' => '{$subscription.renewing_subscription_id}', 'type' => 'integer');
			$vars[] = array('tag' => '{$subscription.updating_subscription_id}', 'type' => 'integer');
			$vars[] = array('tag' => '{$subscription.card_last_four}', 'type' => 'integer');
			$vars[] = array('tag' => '{$subscription.renew_link}', 'type' => 'url');
			$vars[] = array('tag' => '{$subscription.cancel_link}', 'type' => 'url');
			$vars[] = array('tag' => '{$subscription.update_cc_link}', 'type' => 'url');
			$vars[] = array('tag' => '{$subscription.is_recurring}', 'type' => 'boolean');
			$vars[] = array('tag' => '{$subscription.is_active}', 'type' => 'boolean');
			$vars[] = array('tag' => '{$subscription.is_renewed}', 'type' => 'boolean');
			$vars[] = array('tag' => '{$subscription.is_updated}', 'type' => 'boolean');
		}
		
		if (is_array($email_data) and in_array('subscription_plan', $email_data)) {
			$vars[] = array('tag' => '{$subscription_plan.id}', 'type' => 'integer');
			$vars[] = array('tag' => '{$subscription_plan.name}', 'type' => 'string');
			$vars[] = array('tag' => '{$subscription_plan.initial_charge}', 'type' => 'float');
			$vars[] = array('tag' => '{$subscription_plan.amount}', 'type' => 'float');
			$vars[] = array('tag' => '{$subscription_plan.interval}', 'type' => 'integer');
			$vars[] = array('tag' => '{$subscription_plan.free_trial}', 'type' => 'integer');
			$vars[] = array('tag' => '{$subscription_plan.occurrences}', 'type' => 'integer');
			$vars[] = array('tag' => '{$subscription_plan.is_taxable}', 'type' => 'boolean');
			$vars[] = array('tag' => '{$subscription_plan.active_subscribers}', 'type' => 'integer');
			$vars[] = array('tag' => '{$subscription_plan.promotion}', 'type' => 'integer');
			$vars[] = array('tag' => '{$subscription_plan.demotion}', 'type' => 'integer');
			$vars[] = array('tag' => '{$subscription_plan.description}', 'type' => 'string');
			$vars[] = array('tag' => '{$subscription_plan.add_to_cart}', 'type' => 'url');
		}
		
		if (!empty($other_email_data)) {
			foreach ($other_email_data as $data) {
				$vars[] = array('tag' => '{$' . $data . '}', 'type' => 'unknown');
			}
		}
		
		return $vars;
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
		
		$hook = $this->app_hooks->get_hook($email['hook']);
		
		$data = array(
					'hook' => $hook,
					'variables' => $this->email_variables($hook['email_data'], $hook['other_email_data']),
					'products' => $products,
					'plans' => $plans,
					'form' => $email,
					'form_title' => 'Edit Email',
					'form_action' => site_url('admincp/emails/post_email/edit/' . $email['id'])
					);
				
		$this->load->view('email_form',$data);
	}
	
	/**
	* Email layout
	*/
	function email_layout () {
		$this->load->helper('file');

		$layout = read_file(setting('path_email_templates') . '/email_layout.thtml');
		
		if ($layout === FALSE) {
			die(show_error('email_layout.thtml does not exist at ' . setting('path_email_templates') . '.  Please create it and make sure that
			it is writeable.'));
		}
		
		$this->load->library('admin_form');
		$form = new Admin_form;
		
		$form->fieldset('Email Layout');
		$form->textarea('Global Email Layout Template','html', htmlspecialchars($layout), 'This layout is used in all other email templates by default.  It uses Smarty markup.', TRUE, FALSE, TRUE, '100%', '300px');

		$data = array(
					'form' => $form->display(),
					'form_action' => site_url('admincp/emails/post_email_layout')
				);
				
		$this->load->view('email_layout', $data);
	}
	
	/**
	* Post Email Layout
	*/
	function post_email_layout () {
		$this->load->model('emails/email_model');
		
		$this->email_model->update_layout($this->input->post('html'));
		
		$this->notices->SetNotice('Email layout updated successfully.');
		
		redirect('admincp/emails/email_layout');
	}
}