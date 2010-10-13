<?php

/**
* App Hooks Class
*
* Handles all email hooks and code hooks ("binds")
*
* Important methods:
*	- register() creates a new potential hook.  it's only run once, as hooks are stored in the DB.
*	- trigger($name, [$params...]) triggers an active hook.  this may involve sending emails or triggering code via binds.
* 	- data($type, $id) passes data for emails.  e.g., data('member', $user_id) makes member data available.
*	- data_var($name, $value) passes non-standard data for emails.  e.g., data_var('download_link', $download_link);
*	- bind($class, $method) binds code to a hook.  this class:method() will be called with whatever parameters are available.
*/

class App_hooks {
	public $CI;
	public $registered_hooks;
	public $hooks;
	public $binds;
	public $email_data_options;
	
	// email data storage
	private $data; // for all variables
	
	// to stop repetitions
	private $smarty_assigned = FALSE;
	
	function __construct () {
		$this->CI =& get_instance();
		
		// email data variables are stored in an array
		$this->data = array();
		
		// specify which data options are available in emails
		$this->email_data_options = array(
										'member',
										'order',
										'product',
										'invoice',
										'subscription',
										'subscription_plan'
									);
									
		foreach ($this->email_data_options as $option) {
			$this->$option = FALSE;
		}
		reset($this->email_data_options);
									
		/* cross-reference notes:
			- an invoice will load subscription data if applicable
			- an order will load invoice data
			- an invoice will load member data
			- a subscription will load member data
		*/	
		
		// load Smarty email parser
		$this->CI->load->library('smarty');
		$this->CI->smarty_email = new CI_Smarty;
		$this->CI->smarty_email->initialize(TRUE);
		
		$this->assign_defaults();
		
		// load hooks from database
		$result = $this->CI->db->get('hooks');
		
		foreach ($result->result_array() as $hook) {
			$this->hooks[$hook['hook_name']] = array(
								'id' => $hook['hook_id'],
								'name' => $hook['hook_name'],
								'description' => $hook['hook_description'],
								'email_data' => (!empty($hook['hook_email_data'])) ? unserialize($hook['hook_email_data']) : '',
								'other_email_data' => (!empty($hook['hook_other_email_data'])) ? unserialize($hook['hook_other_email_data']) : ''
							);
		}
		
		// load binds from database
		$result = $this->CI->db->get('binds');
		
		foreach ($result->result_array() as $bind) {
			$this->binds[$bind['hook_name']][] = array(
												'id' => $bind['bind_id'],
												'hook_name' => $bind['hook_name'],
												'class' => $bind['bind_class'],
												'method' => $bind['bind_method'],
												'filename' => $bind['bind_filename']
											); 
		}
	}
	
	function assign_defaults () {
		$settings = $this->CI->config->config;
		
		$this->CI->smarty_email->assign('setting', $settings);
		$this->CI->smarty_email->assign('settings', $settings);
		$this->CI->smarty_email->assign('site_name', setting('site_name'));
	}
	
	/**
	* Bind
	*
	* Binds a method/function a hook
	*
	* @param string $hook
	* @param string $class Set to FALSE for no class
	* @param string $method
	* @param string $filename Full path to file, in case it's not already been loaded
	*
	* @return int $bind_id
	*/
	function bind ($hook, $class = FALSE, $method, $filename) {
		if (!isset($this->hooks[$hook])) {
			die(show_error('Attempting to bind to a non-existant hook, "' . $hook . '"'));
		}
	
		$insert_fields = array(
							'hook_name' => $hook,
							'bind_class' => (!empty($class)) ? $class : '',
							'bind_method' => $method,
							'bind_filename' => $filename,
							'bind_created' => date('Y-m-d H:i:s')
						);
						
		$bind_id = $this->CI->db->insert('binds', $insert_fields);
		
		$this->binds[$hook][] = array(
								'id' => $bind_id,
								'hook_name' => $hook,
								'class' => $class,
								'method' => $method,
								'filename' => $filename
							); 
		
		return $bind_id;
	}
	
	/**
	* Get Hooks
	*
	* @return array Hooks
	*/
	function get_hooks () {
		ksort($this->hooks);
		return $this->hooks;
	}
	
	/**
	* Get Hook
	*
	* @param string $name
	*
	* @return array Hooks
	*/
	function get_hook ($name) {
		return $this->hooks[$name];
	}
	
	/**
	* Register Hook
	*
	* Creates a new potential hook in the database, for code/emails to be latched on to
	*
	* @param string $name
	* @param string $description A layman description of the hook
	* @param array $email_data
	* @param array $other_email_data An array of other variables available for a customized hook
	*
	* @return int $hook_id
	*/
	function register ($name, $description = '', $email_data = array(), $other_email_data = array()) {
		$insert_fields = array(
							'hook_name' => $name,
							'hook_email_data' => (empty($email_data)) ? '' : serialize((array)$email_data),
							'hook_other_email_data' => (empty($other_email_data)) ? '' : serialize((array)$other_email_data),
							'hook_description' => $description,
							'hook_created' => date('Y-m-d H:i:s')
						);
		
		$hook_id = $this->CI->db->insert('hooks', $insert_fields);						
		
		$this->hooks[$name] = array(
								'id' => $hook_id,
								'name' => $name,
								'description' => $description,
								'email_data' => (empty($email_data)) ? '' : (array)$email_data,
								'other_email_data' => (empty($other_email_data)) ? '' : (array)$other_email_data
							);
							
		return $hook_id;
	}
	
	/**
	* Register Data
	*
	* Loads a type of data into the hook before calling.
	* This makes it available for emails.
	* They may cross-reference to save resources.
	*
	* @param string $data
	* @param int $id
	*
	* @return boolean TRUE
	*/
	function data ($type, $id) {
		if ($type == 'member') {
			// check for cross-reference
			if ($this->member == $id) {
				return TRUE;
			}
			
			$this->CI->load->model('users/user_model');
			$user = $this->CI->user_model->get_user($id, TRUE);
			
			if (empty($user)) {
				die(show_error('Trigger Data: Unable to load data for user #' . $id));
			}
			
			$this->data_var('member',$user);
			
			// save ID
			$this->member = $user['id'];
		}
		elseif ($type == 'order') {
			// check for cross-reference
			if ($this->order == $id) {
				return TRUE;
			}
			
			$this->CI->load->model('store/order_model');
			$order = $this->CI->order_model->get_order($id, 'order_id');

			if (empty($order)) {
				die(show_error('Trigger Data: Unable to load data for order #' . $id));
			}
					
			// shipping address
			if (!empty($order['shipping'])) {	
				$this->CI->load->helper('format_street_address');					
				$shipping_address = str_replace('<br />',"\n",str_replace("\n",'',format_street_address($order['shipping'])));
			} else {
				$shipping_address = FALSE;
			}
			$this->data_var('shipping_address', $shipping_address);
			
			// products
			$products = $this->CI->order_model->get_order_products(array('invoice_id' => $order['invoice_id']));
			$this->data_var('products', $products);
			
			// cross-reference
			$this->data('invoice', $order['invoice_id']);
			
			// save ID
			$this->order = $order['invoice_id'];
		}
		elseif ($type == 'product') {
			// check for cross-reference
			if ($this->product == $id) {
				return TRUE;
			}
			
			$this->CI->load->model('store/products_model');
			$product = $this->CI->products_model->get_product($id);
			
			if (empty($product)) {
				die(show_error('Trigger Data: Unable to load data for product #' . $id));
			}
			
			$this->data_var('product', $product);
			
			// save ID
			$this->product = $product['id'];
		}
		elseif ($type == 'invoice') {
			$this->CI->load->helper('format_street_address');
			
			// check for cross-reference
			if ($this->invoice == $id) {
				return TRUE;
			}
		
			$this->CI->load->model('billing/invoice_model');
			$invoice = $this->CI->invoice_model->get_invoice($id);
			
			if (empty($invoice)) {
				die(show_error('Trigger Data: Unable to load data for invoice #' . $id));
			}
			
			// for consistency, we'll move the billing address to a main variable
			$billing_address = $invoice['billing_address'];
			unset($invoice['billing_address']);
			
			// ... and format it
			$billing_address = str_replace('<br />',"\n",str_replace("\n",'',format_street_address($billing_address)));
			
			$this->data_var('invoice', $invoice);
			$this->data_var('billing_address', $billing_address);
			
			// cross-reference
			if ($invoice['subscription_id']) {
				$this->data('subscription', $invoice['subscription_id']);
			}
			
			$this->data('member',$invoice['user_id']);
			
			// save ID
			$this->invoice = $invoice['id'];
		}
		elseif ($type == 'subscription') {
			// check for cross-reference
			if ($this->subscription == $id) {
				return TRUE;
			}
		
			$this->CI->load->model('billing/subscription_model');
			$subscription = $this->CI->subscription_model->get_subscription($id);
			
			if (empty($subscription)) {
				die(show_error('Trigger Data: Unable to load data for subscription #' . $id));
			}
			
			$this->data_var('subscription', $subscription);
			
			// cross reference
			$this->data('member', $subscription['user_id']);
			$this->data('subscription_plan', $subscription['plan_id']);
			
			// save ID
			$this->subscription = $subscription['id'];
		}
		elseif ($type == 'subscription_plan') {
			// check for cross-reference
			if ($this->subscription_plan == $id) {
				return TRUE;
			}
		
			$this->CI->load->model('billing/subscription_plan_model');
			$subscription_plan = $this->CI->subscription_plan_model->get_plan($id);
			
			if (empty($subscription_plan)) {
				die(show_error('Trigger Data: Unable to load data for subscription plan #' . $id));
			}
			
			$this->data_var('subscription_plan', $subscription_plan);
			
			// save ID
			$this->subscription_plan = $subscription_plan['id'];
		}
		
		return TRUE;
	}
	
	/**
	* Register Variable
	*
	* Saves an individual variable into the data for this hook call.
	*
	* @param string $name
	* @param string $value
	*/
	function data_var ($name, $value) {
		$this->data[$name] = $value;
	}
	
	/**
	* Trigger Hook
	*
	* Triggers a hook, after passing it all necessary data.  If the data is lacking, an error will be thrown.
	* Other parameters can be passed and they will be sent to latches in their order.
	*
	* @param string $name
	* @params [...$optional_params...]
	*/
	function trigger ($name) {
		// check that hook exists
		if (!isset($this->hooks[$name])) {
			die(show_error('Trigger: Invalid hook call, "' . $name . '".'));
		}
		
		// load hook
		$hook = $this->hooks[$name];
		
		// check that data is good
		if (is_array($hook['email_data'])) {
			foreach ($hook['email_data'] as $data) {
				if (!isset($this->$data)) {
					die(show_error('Trigger: "' . $data . '" data not available at hook call.'));
				}
			}
		}
		
		if (is_array($hook['other_email_data'])) {
			foreach ($hook['other_email_data'] as $data) {
				if (!isset($this->data[$data])) {
					die(show_error('Trigger: "' . $data . '" data variable not available at hook call.'));
				}
			}
		}
		
		// get arguments from parameter, everything after the $name
		$args = func_get_args();
		if (count($args) == 1) {
			// we only have the $name parameter, no additional optional arguments
			$args = array();
		}
		elseif (count($args) > 0) {
			unset($args[0]);
			
			// we have additional arguments, now in the $args parameter
			// these will be passed to any method/function bind calls
		}
		else {
			$args = array();
		}
		
		// execute code latched to hook
		if (isset($this->binds[$hook['name']]) and !empty($this->binds[$hook['name']])) {
			// we have binds
			
			foreach ($this->binds[$hook['name']] as $bind) {
				$class = $bind['class'];
				$method = $bind['method'];
				$lower_class = strtolower($class);
				
				if (!empty($class)) {
					if (isset($this->CI->$lower_class) and is_object($this->CI->$lower_class)) {
						// it's in the CI superobject
						
						call_user_func_array(array($this->CI->$lower_class, $method), $args);
					}
					elseif (class_exists($class)) {
						// the class exists, but no in the CI superobject
						$bind_class = new $class;
						call_user_func_array(array($bind_class,$method),$args);
					}
					else {
						// the class isn't loaded, we'll call the file and load it
						
						include(FCPATH . $bind['filename']);
						$bind_class = new $class;
						call_user_func_array(array($bind_class,$method), $args);
					}
				}
				else {
					// it's a non-class-bound function
					if (function_exists($method)) {
						// the function exists, call it
						call_user_func_array($method, $args);
					}
					else {
						// file hasn't been loaded, load and call it
						include(FCPATH . $bind['filename']);
						call_user_func_array($method, $args);
					}
				}
			}
		}
		
		// send emails based on hook
		$this->CI->load->model('emails/email_model');
		$emails = $this->CI->email_model->get_emails(array('hook' => $hook['name']));
		
		if (!empty($emails)) {
			foreach ($emails as $key => $email) {
				$send_email = TRUE;
				
				// do we have parameters to meet?
				if (!empty($email['parameters'])) {
					foreach ($email['parameters'] as $param => $value) {
						// get operator
						list($param,$operator) = explode(' ',$param);
						if (empty($operator)) {
							$operator = '==';
						}
						else {
							$operator = trim($operator);
						}
						
						$param = trim($param);
					
						if ($operator == '==' and $this->$param != $value) {
							$send_email = FALSE;
						}
						elseif ($operator == '!=' and $this->$param == $value) {
							$send_email = FALSE;
						}
					}
				}
				
				if ($send_email == TRUE) {
					$this->send_email($email);
				}
			}
		}
	}
	
	/**
	* Clear All Data and Variables from Hook
	*/
	function reset () {
		$this->CI->smarty_email->clearAllAssign();
		$this->smarty_assigned = FALSE;
		
		// clear data
		$this->data = array();
		
		foreach ($this->email_data_options as $option) {
			$this->$option = FALSE;
		}
		reset($this->email_data_options);
		
		$this->assign_defaults();
		
		return TRUE;
	} 
	
	/**
	* Send Email
	*
	* Sends an email from a standard email array
	* 
	* @param array $email A standard email array from email_model::get_emails()
	*/
	function send_email ($email) {
		$this->CI->load->library('email');
		
		// dynamic config
		$config = array();
		$config['mailtype'] = ($email['is_html'] == TRUE) ? 'html' : 'text';
		$config['wordwrap'] = ($email['is_html'] == TRUE) ? FALSE : TRUE;
		$this->CI->email->initialize($config);
		
		// To: 
		$to = array();
		foreach ($email['recipients'] as $recipient) {
			if ($recipient == 'member' and $this->member != FALSE) {
				$to[] = $this->data['member']['email'];
			}
			elseif ($recipient == 'admin') {
				$to[] = setting('site_email');
			}
			else {
				$to[] = $recipient;
			}
		}
		
		if (empty($to)) {
			return FALSE;
		}
		
		$this->CI->email->to($to);
		
		// BCC:
		$bcc = array();
		foreach ($email['bccs'] as $recipient) {
			if ($recipient == 'member') {
				$bcc[] = $this->data['member']['email'];
			}
			elseif ($recipient == 'admin') {
				$bcc[] = setting('site_email');
			}
			else {
				$bcc[] = $recipient;
			}
		}
		
		// From: 
		$this->CI->email->from(setting('site_email'), setting('email_name'));
		
		if (!$this->smarty_assigned) {
			// assign variables to smarty
			foreach ($this->data as $k => $v) {
				// if HTML, let's run nl2br()
				if ($email['is_html'] == TRUE and !is_array($v)) {
					$v = nl2br($v);
				}
				
				$this->CI->smarty_email->assign($k, $v);
			}
			
			$this->smarty_assigned = TRUE;
		}
		
		// Build Subject
		$subject = $this->CI->smarty_email->fetch($email['subject_template']);
		$this->CI->email->subject($subject);
		
		// Build Body
		$body = $this->CI->smarty_email->fetch($email['body_template']);
		$this->CI->email->message($body);
		
		// Send!
		$this->CI->email->send();
		$this->CI->email->clear();
		
		// Send BCC
		if (!empty($bcc)) {
			$this->CI->email->to($bcc);
			$this->CI->email->from(setting('site_email'), setting('email_name'));
			$this->CI->email->subject($subject);
			$this->CI->email->message($body);
			$this->CI->email->send();
			$this->CI->email->clear();
		}
		
		return TRUE;
	}
}