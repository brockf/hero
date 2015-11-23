<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class twocheckout {

	var $settings;
	
	var $base_url	= 'https://www.2checkout.com/api/';
	var $accept		= 'application';
	var $format		= 'json';
	
	var $CI;
	
	var $debug = false;
	
	//--------------------------------------------------------------------
	
	function __construct () {
		// Init our settings
		$this->settings = $this->Settings();
		
		$this->CI =& get_instance();
	}
	
	//--------------------------------------------------------------------
	
	function Settings()
	{
		$settings = array();
		
		$settings['name'] = '2Checkout';
		$settings['class_name'] = 'twocheckout';
		$settings['external'] = TRUE;
		$settings['no_credit_card'] = TRUE;
		$settings['description'] = '2Checkout is a simple 3rd-party PayPal alternative for international merchants.  After account creation, you must setup your Notifications in your 2CO control panel.  More information is available at <a href="http://help.electricfunction.com/kb/opengateway/how-to-setup-a-gateway-with-2checkout">in the support area</a>.';
		$settings['is_preferred'] = 1;
		$settings['setup_fee'] = '$49';
		$settings['monthly_fee'] = 'n/a';
		$settings['transaction_fee'] = '5.5% + $0.45';
		$settings['purchase_link'] = 'http://www.2checkout.com/';
		$settings['allows_updates'] = 0;
		$settings['allows_refunds'] = 1;
		$settings['requires_customer_information'] = 1;
		$settings['requires_customer_ip'] = 0;
		$settings['required_fields'] = array(
										'enabled',
										'mode', 
										'username',
										'password',
										'merchant_id',
										'currency'
										);
										
		$settings['field_details'] = array(
										'enabled' => array(
														'text' => 'Enable this gateway?',
														'type' => 'radio',
														'options' => array(
																		'1' => 'Enabled',
																		'0' => 'Disabled')
														),
										'mode' => array(
														'text' => 'Mode',
														'type' => 'select',
														'options' => array(
																		'live' => 'Live Mode',
																		'test' => 'Test Mode'
																		)
														),
										'username' => array(
														'text' => 'API Username',
														'type' => 'text'
														),
										
										'password' => array(
														'text' => 'API Password',
														'type' => 'password'
														),
										'merchant_id'	=> array(
														'text' => 'Merchant ID',
														'type' => 'text'
														),
										'currency'	=> array(
														'text'	=> 'Currency',
														'type'	=> 'select',
														'options'	=> array(
																		'USD'	=> 'US Dollar',
																		'ARP'	=> 'Argentino Peso',
																		'AUD'	=> 'Australian Dollar',
																		'BRL'	=> 'Brazilian Real',
																		'CAD'	=> 'Canadian Dollar',
																		'DKK'	=> 'Danish Kroner',
																		'EUR'	=> 'Euro',
																		'GBP'	=> 'GBP Sterlings',
																		'HKD'	=> 'Hong Kong Dollar',
																		'INR'	=> 'Indian Rupee',
																		'JPY'	=> 'Japanese Yen',
																		'MXN'	=> 'Mexican Peso',
																		'NAX'	=> 'New Zealand Dollar',
																		'NOK'	=> 'Norwegian Kroner',
																		'ZAL'	=> 'South African Rand',
																		'SEK'	=> 'Swedish Kroner',
																		'CHF'	=> 'Swiss Franc'
																	)
														)
											);
		
		return $settings;
	}
	
	//--------------------------------------------------------------------

	/**
	 *	Verifies that our connection is working as expected by returning the
	 * detailed company info for the user.
	 * 
	 * Also verifies that json is available on the server.
	 */
	public function TestConnection($gateway) 
	{
		if (!function_exists('json_encode'))
		{
			return false;
		}
	
		return TRUE;
	}
	
	//--------------------------------------------------------------------
	
	/**
	 * Charging with 2CO does NOT require the product to be setup within
	 * the 2CO backend.
	 */
	public function Charge($order_id, $gateway, $customer, $amount, $credit_card, $return_url, $cancel_url)
	{
		$this->CI->load->helper('url');
		$this->CI->load->model('billing/charge_data_model');
		
		// Return redirect information
		$url = site_url('callback/twocheckout/form_redirect/'. $order_id);
		
		// save return redirect URL
		$this->CI->charge_data_model->Save($order_id, 'return_url', $return_url);
		
		$response_array = array(
						'not_completed' => TRUE, // don't mark charge as complete
						'redirect' 		=> $url, // redirect the user to this address
						'charge_id' 	=> $order_id
					);
		$response = $this->CI->response->TransactionResponse(100, $response_array);
		
		if ($this->debug)
		{
			echo '<h2>Charge TransactionResponse</h2>';
			print_r($response);
			die();
		}

		return $response;
	}
	
	//--------------------------------------------------------------------
	
	/**
	 *	Recur - called when an initial Recur charge comes through to
	 *	to create a subscription.
	 *
	 */
	public function Recur($gateway, $customer, $amount, $charge_today, $start_date, $end_date, $interval, $credit_card, $subscription_id, $total_occurrences, $return_url, $cancel_url) 
	{
		$this->CI->load->helper('url');
		$this->CI->load->model('billing/charge_data_model');

		/*
		 	First thing, we need to verify that the product exists at 2CO
		 	or we need to create it. In this case, the product is a recurring 
		 	plan with a certain name and price
		*/
	
		// Get our subscription details
		$subscription = $this->CI->recurring_model->GetRecurring($subscription_id);
		
		// Are we dealing with a plan or a one-time?
		$plan_name = !empty($subscription['plan']['name']) ? $subscription['plan']['name'] : 'One Time Recurring Plan';

		$plan_id = !empty($subscription['plan']['id']) ? $subscription['plan']['id'] : $subscription_id;

		// Verify or create product
		if (!$product_id = $this->api_product_exists($plan_name, $amount, $gateway))
		{
			$product_id = $this->api_create_product($plan_name, $plan_id, $amount, $start_date, $end_date, $interval, $gateway);
		}
		
		/*
			Save the data for use within the redirect
		*/
		$this->CI->charge_data_model->Save('r'.$subscription_id, 'product_id', $product_id);
		
		// save the initial charge amount (it may be different, so we treat it as a separate first charge)
		$this->CI->charge_data_model->Save('r' . $subscription_id, 'first_charge', $amount);
		
		// save return redirect URL
		$this->CI->charge_data_model->Save('r'.$subscription_id, 'return_url', $return_url);
	
		// create the order if we need to
		if ($charge_today === TRUE) {
			$this->CI->load->model('billing/charge_model');
			
			$customer_id = $customer['id'];
			
			$order_id = $this->CI->charge_model->CreateNewOrder($gateway['gateway_id'], $amount, array(), $subscription_id, $customer_id);
			
			// the order will be set as failed but the callback_order_created_recur function will set it OK
		}
	
		/*
			Since the actual recording of the success of the payment is handled
			in the 'order_created' callback, we simply issue a redirect to take 
			the user to the 2CO checkout form.
		*/
		$response_array = array(
						'not_completed' => TRUE, // don't mark charge as complete
						'redirect' 		=> $url = site_url('callback/twocheckout/form_redirect_recur/'. $subscription_id), // redirect the user to this address
						'subscription_id' 	=> $subscription_id
					);
					
		if (isset($order_id)) {
			$response_array['charge_id'] = $order_id;
		}
							
		$response = $this->CI->response->TransactionResponse(100, $response_array);

		return $response;
	}
	
	//--------------------------------------------------------------------
	
	public function CancelRecurring($subscription, $gateway) 
	{
		// First, we need to get the details of the sale so we have the line_item_id
		$sale_details = $this->Process('sales/detail_sale', array('invoice_id' => $subscription['api_payment_reference']), $gateway);
		
		if (!$sale_details or !isset($sale_details->lineitem_id))
		{
			return FALSE;
		}
		
		// Now try to cancel
		
		$response = $this->Process('sales/stop_lineitem_recurring', array('lineitem_id' => $sale_details->lineitem_id), $gateway);

		if ($response->response_code == 'OK') {
			return TRUE;
		} else {
			return FALSE;
		}
	}
	
	//--------------------------------------------------------------------
	
	public function UpdateRecurring($gateway, $subscription, $customer, $params) 
	{
		return FALSE;
	}
	
	//--------------------------------------------------------------------
	
	public function AutoRecurringCharge ($order_id, $gateway, $params) {
		return $this->ChargeRecurring($gateway, $params);
	}
	
	//--------------------------------------------------------------------
	
	public function ChargeRecurring($gateway, $params) 
	{
		// We'll say that the charge went through, because as soon as it doesn't go through,
		// this subscription is killed via the recurring_installment_stops
		
		return array('success' => TRUE);
	}
	
	//--------------------------------------------------------------------

	public function Refund ($gateway, $charge, $authorization)
	{				
		$data = array(
			'invoice_id'	=> $authorization->tran_id,
			'amount'		=> $charge['amount'],
			'currency'		=> 'vendor',
			'category'		=> '5',
			'comment'		=> 'Issued by Vendor.'
		);
		
		$response = $this->Process('sales/refund_invoice', $data, $gateway);

		if ($response->response_code == 'OK') {
			return TRUE;
		} else {
			return FALSE;
		}
	}

	//--------------------------------------------------------------------	
	
	/**
	 *	Retrieves the charge id from the parameters passed in. This is used
	 * by the callback controller to retrieve the order information.
	 *
	 * @param	array	$params		An array of $_POST and $_GET params passed by the gateway
	 * @return	int		$charge_id	The id of the charge to look up.
	 */
	public function GetChargeId($params) 
	{
		if (isset($params['vendor_order_id']))
		{
			return $params['vendor_order_id'];
		}
		elseif (isset($params['merchant_order_id'])) {
			return $params['merchant_order_id'];
		}
		
		return 0;
	}
	
	//--------------------------------------------------------------------
	
	// Simply states whether this call is a recurring call or not
	// by searching in the parameters passed from 2CO.
	public function is_recurring($params) 
	{
		if (isset($params['recurring']) && $params['recurring'] == '1')
		{
			return true;
		}
		
		return false;
	}
	
	//--------------------------------------------------------------------
	
	
	//--------------------------------------------------------------------
	// !API CALLS
	//--------------------------------------------------------------------

	public function api_product_exists($product_name, $amount, $gateway) 
	{
		$response = $this->Process('products/list_products', array('product_name' => $product_name), $gateway);
		
		if (!isset($response->products) or empty($response->products)) {
			return false;
		}
		
		foreach ($response->products as $product)
		{
			if ($product->name == $product_name && $product->price == $amount)
			{
				return $product->assigned_product_id;
			}
		}
		
		return false;
	}
	
	//--------------------------------------------------------------------

	public function api_create_product($plan_name, $plan_id, $amount, $start_date, $end_date, $interval, $gateway) 
	{
		$data = array(
			'name'				=> $plan_name,
			'price'				=> $amount,
			'vendor_product_id'	=> $plan_id,
			'tangible'			=> 0,
			'recurring'			=> 1,
			'recurrence'		=> (int)($interval/7) .' Week',
			'duration'			=> (int)((strtotime($end_date) - strtotime($start_date)) / 604800) .' Week',
			'commission'		=> 0
		);
		
		$response = $this->Process('products/create_product', $data, $gateway);
		
		if ($response->response_code = 'OK')
		{
			return $response->assigned_product_id;
		}
		
		return false;
	}
	
	//--------------------------------------------------------------------
	
	
	//--------------------------------------------------------------------
	// !CALLBACKS
	//--------------------------------------------------------------------
	
	/**
	 * Used by the Charge method to redirect the user to the 2CO sales page.
	 */
	public function Callback_form_redirect($gateway, $charge, $params) 
	{ 
		$this->CI->load->helper('url');
			
		// Build the form info to send to 2CO
		$post = array(
			'sid'			=> $gateway['merchant_id'],
			'total'			=> $charge['amount'],
			'cart_order_id'	=> 'Invoice: '. $charge['id'],
			'id_type'		=> '1'
		);
		
		// product info
		//$post['c_prod'] 		= $charge['type'];
		//$post['c_name']			= $charge['type'];
		//$post['c_description']	= $charge['type'];
		//$post['c_price']		= $charge['amount'];
		
		// Test/dev mode?
		if ($gateway['mode'] != 'live')
		{
			$post['demo'] = 'Y';
		}
		
		$post['fixed'] 				= 'Y';
		$post['x_receipt_link_url'] = site_url('callback/twocheckout/redirect/' . $charge['id']);
		$post['merchant_order_id']	= $charge['id'];
		$post['pay_method'] 		= 'CC';
		$post['skip_landing']		= 'Y';
		
		// Billing info
		if (isset($charge['customer']['first_name']))
		{
			$post['card_holder_name']	= $charge['customer']['first_name'] .' '. $charge['customer']['last_name'];
		}
		if (isset($charge['customer']['address_1']) and !empty($charge['customer']['address_1']))
		{
			$post['street_address']	 = $charge['customer']['address_1'];
			$post['street_address2'] = $charge['customer']['address_2'];
			$post['city']			= $charge['customer']['city'];
			$post['state']			= $charge['customer']['state'];
			$post['zip']			= $charge['customer']['postal_code'];
			$post['country']		= $charge['customer']['country'];
		}
		$post['email']	= $charge['customer']['email'];
		$post['phone']	= $charge['customer']['phone'];
	
		$data = '';
		foreach ($post as $key => $value)
		{
			$data .= "&$key=$value";
		}
	
		//redirect('http://developers.2checkout.com/return_script/?'. trim($data, '& '));
		redirect('https://www.2checkout.com/checkout/purchase?'. trim($data, '& '));
	}
	
	//--------------------------------------------------------------------

	/**
	 * Used by the Recur method to redirect the user to the 2CO sales page.
	 */
	public function Callback_form_redirect_recur($gateway, $charge, $params) 
	{
		$this->CI->load->model('billing/charge_data_model');
		$this->CI->load->helper('url');
		
		$charge_data = $this->CI->charge_data_model->Get('r'. $charge['id']);
	
		// Build our form info for 2CO
		$post = array(
			'sid'			=> $gateway['merchant_id'],
			'product_id'	=> $charge_data['product_id'],
			'quantity'		=> '1',
			'demo'			=> $gateway['mode'] != 'live' ? 'Y' : 'n',
			'fixed'			=> 'Y',
			'merchant_order_id'	=> $charge['id'],
			'pay_method'	=> 'CC',
			'skip_landing'	=> 'Y',
			'x_receipt_link_url' => site_url('callback/twocheckout/redirect_recur/' . $charge['id'])
		);
		
		// Billing info
		if (isset($charge['customer']['first_name']))
		{
			$post['card_holder_name']	= $charge['customer']['first_name'] .' '. $charge['customer']['last_name'];
		}
		if (isset($charge['customer']['address_1']) and !empty($charge['customer']['address_1']))
		{
			$post['street_address']	 = $charge['customer']['address_1'];
			$post['street_address2'] = $charge['customer']['address_2'];
			$post['city']			= $charge['customer']['city'];
			$post['state']			= $charge['customer']['state'];
			$post['zip']			= $charge['customer']['postal_code'];
			$post['country']		= $charge['customer']['country'];
		}
		
		$post['email']	= $charge['customer']['email'];
		$post['phone']	= $charge['customer']['phone'];
		
		$data = '';
		foreach ($post as $key => $value)
		{
			$data .= "&$key=$value";
		}
		
		//redirect('http://developers.2checkout.com/return_script/?'. trim($data, '& '));
		redirect('https://www.2checkout.com/checkout/purchase?'. trim($data, '& '));
	}
	
	//--------------------------------------------------------------------
	
	public function Callback_redirect ($gateway, $charge, $params) {
		$this->CI->load->model('billing/charge_data_model');
		$this->CI->load->helper('url');
		
		$charge_data = $this->CI->charge_data_model->Get($charge['id']);
		
		$this->CI->load->model('billing/order_authorization_model');
		$this->CI->order_authorization_model->SaveAuthorization($charge['id'], $charge['id']);
		
		$this->CI->charge_model->SetStatus($charge['id'], 1);
		
		return redirect($charge_data['return_url']);
	}
	
	public function Callback_redirect_recur ($gateway, $charge, $params) {
		$this->CI->load->model('billing/charge_data_model');
		$this->CI->load->helper('url');
		
		$charge_data = $this->CI->charge_data_model->Get('r'. $charge['id']);
		
		return redirect($charge_data['return_url']);
	}
	
	/**
	 * Called when a user succesffully creates either a Charge or a Recurring charge. 
	 * Saves the information to the datase and lets us know it's a successfull charge.
	 */
	public function Callback_order_created($gateway, $charge, $params) 
	{	
		// Is this a recurring order?
		if ($params['recurring'] == '1') {
			$this->Callback_recurring_order_created($gateway, $charge, $params);
		}
		else {
			// we do this logic in Callback_redirect now
		}
	}
	
	//--------------------------------------------------------------------

	/*
		Called by 2CO whenever a recurring order is created.
	*/
	public function Callback_recurring_order_created($gateway, $subscription, $params) 
	{			
		$this->CI->load->model('billing/recurring_model');
		
		$this->CI->load->model('billing/charge_data_model');
		$data = $this->CI->charge_data_model->Get('r' . $subscription['id']);
		
		// do we have a first charge to process
		$result = $this->CI->db->where('subscription_id',$subscription['id'])
							   ->from('orders')
							   ->get();
							   						   
		if ($result->num_rows() > 0) {
			$order = $result->row_array();
			
			$this->CI->load->model('billing/charge_model');
			$this->CI->load->model('billing/order_authorization_model');
			
			// we may not have the transaction ID if it's Pending
			$this->CI->order_authorization_model->SaveAuthorization($order['order_id'], $params['sale_id']);
			
			$this->CI->charge_model->SetStatus($order['order_id'], 1);
		}

		$order_id = (isset($order_id)) ? $order_id : FALSE;
	
		$this->CI->recurring_model->SetActive($subscription['id']);
		
		// hook
		$this->CI->load->library('app_hooks');
		$this->CI->app_hooks->data('subscription', $subscription['id']);
		$this->CI->app_hooks->trigger('subscription_new', $subscription['id']);
		
		// trip a recurring charge?
		if ($order_id) {
			// hook
			$this->CI->app_hooks->data('invoice', $order_id);
			$this->CI->app_hooks->trigger('subscription_charge', $order_id, $subscription['id']);
		}
		
		$this->CI->app_hooks->reset();
		
		// Save our Invoice ID so we can get details of line_items later.
		$this->CI->recurring_model->SaveApiPaymentReference($subscription['id'], $params['invoice_id']);
		
		// (don't) Delete our temp data!
		// we used to delete this and it screwed the post-order redirects
		//$this->CI->charge_data_model->delete('r'. $subscription['id']);
		die();
	}
	
	//--------------------------------------------------------------------
	
	/*
		Called by 2CO on a successfull installment charge.
		
		This method saves the order authorization information.
	*/
	public function Callback_recurring_installment_success($gateway, $subscription, $params) 
	{
		// OG will automatically create successful orders, so there's no need to duplicate this here
		
		/*
		$this->CI->load->model('billing/recurring_model');
		$this->CI->load->model('billing/charge_model');
		
		// Record a new charge for this one.
		$customer_id = (isset($subscription['customer']['id'])) ? $subscription['customer']['id'] : FALSE;
		$order_id = $this->CI->charge_model->CreateNewOrder($gateway['gateway_id'], $subscription['amount'], array(), $subscription['id'], $customer_id);
		
		// hook
		$this->CI->load->library('app_hooks');
		$this->CI->app_hooks->data('subscription', $subscription['id']);
		$this->CI->app_hooks->data('invoice', $order_id);
		$this->CI->app_hooks->trigger('subscription_charge', $order_id, $subscription['id']);
		
		$this->CI->app_hooks->reset();
		
		// Save our Invoice ID so we can get details of line_items later.
		$this->CI->recurring_model->SaveApiPaymentReference($subscription['id'], $params['invoice_id']);*/
	}
	
	//--------------------------------------------------------------------
	
	/*
		Called by 2CO on a failure to bill a recurring payment.
		
		This simply records the failure in the database.
	*/
	public function Callback_recurring_installment_fail($gateway, $subscription, $params) 
	{
		$this->CI->load->model('billing/recurring_model');
		
		$this->CI->recurring_model->AddFailure($subscription['id'], 1);
	}
	
	//--------------------------------------------------------------------

	/*
		Called by 2CO when a recurring plan has been stopped.
	*/
	public function Callback_recurring_installment_stopped($gateway, $subscription, $params) 
	{
		// Mark the subscription as inactive
		$this->CI->load->model('billing/recurring_model');
		
		$this->CI->recurring_model->MakeInactive($subscription['id']);
		
		$this->CI->recurring_model->CancelRecurring($subscription['id'], TRUE);
		
		$this->CI->load->library('app_hooks');
		$this->CI->app_hooks->data('subscription', $subscription['id']);
		$this->CI->app_hooks->trigger('subscription_expire', $subscription['id']);
		
		$this->CI->app_hooks->reset();
	}
	
	//--------------------------------------------------------------------
	
	//--------------------------------------------------------------------
	// !PROCESSORS
	//--------------------------------------------------------------------

	public function Process($url_suffix, $data, $gateway) 
	{	
		if(!is_array($data)) 
		{
			$resp = $this->return_response(array('Error' => 'Value passed in was not an array of at least one key/value pair.'));
		} 
		else 
		{
			if (strpos($url_suffix, 'http') !== false)
			{
				$url = $url_suffix;
			}
			else
			{
				$url = $this->base_url . $url_suffix;
			}
			
			$ch = curl_init($url);
			curl_setopt($ch, CURLOPT_HEADER, 0);
			
			curl_setopt($ch, CURLOPT_HTTPHEADER, array("Accept: {$this->accept}/{$this->format}"));
			curl_setopt($ch, CURLINFO_HEADER_OUT, true);
			curl_setopt($ch, CURLOPT_POST, true);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
			//curl_setopt($ch, CURLOPT_PROTOCOLS, CURLPROTO_HTTPS);
			curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
			curl_setopt($ch, CURLOPT_USERPWD, "{$gateway['username']}:{$gateway['password']}");
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);	// Verify it belongs to the server.
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);	// Check common exists and matches the server host name
			
			if(count($data) > 0) {
				curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
			}
			
			$resp = curl_exec($ch);
			
			if ($this->debug)
			{
				echo '<h2>CURL Response</h2><pre>';
				print_r(curl_getinfo($ch));
				echo 'CURL Error = '. curl_error($ch) ."<br/>";
			}
			curl_close($ch);
		}
		
		if ($this->debug)
		{
			echo '<p>URL = '. $url .'</p>';
			echo '<h2>Process Results</h2>';
			var_dump($resp);
			die();
		}

		return json_decode($this->return_response($resp));
	}
	
	//--------------------------------------------------------------------
	
	//--------------------------------------------------------------------
	// ! PRIVATE METHODS
	//--------------------------------------------------------------------
	
	/**
	 *	Formats the return response based upon the content types. 
	 *
	 * @param	string	$contents	An array where keys are nodes and values are the node data
	 * @return	array
	 */
	public function return_response($contents) 
	{	
		switch($this->format) {
			case 'xml':
				if(preg_match('/<response>/', $contents)) {
					return $contents;
				} else {
					$xml = new XmlConstruct('response');
					$xml->fromArray($contents);
					return $xml->getDocument();
					return $xml->output();
				}
			break;
			case 'json':
				if(preg_match('/response :/', $contents)) {
					return $contents;
				} else {
					$jsonData = json_encode($contents);
					return json_decode($jsonData);
				}
			break;
			case 'html':
				if(preg_match('/\<dt\>response_code\<\/dt\>/', $contents)) {
					return $contents;
				} else {
					$htmlOut = '';
					foreach($contents as $key => $val) {
						$htmlOut .= "<ul>$key<li>$val</li></ul>\n";
					}
					return $htmlOut;
				}
			break;
		}
	}
	
	//--------------------------------------------------------------------
	
	/**
	 * Returns the proper url for the remote gateway.
	 *
	 * Note that $mode param defaults to false, which will
	 * return the token payments url. If $mode is 'rebill', 
	 * then it will return the rebill url.
	 */
	private function GetAPIUrl ($gateway, $mode = FALSE) {
		if ($mode == FALSE) {
			// Get the proper URL
			switch($gateway['mode'])
			{
				case 'live':
					$post_url = $gateway['url_live'];
				break;
				case 'test':
					$post_url = $gateway['url_test'];
				break;
				case 'dev':
					$post_url = $gateway['url_dev'];
				break;
			}
		}
		elseif ($mode == 'rebill') {
			// Get the proper URL
			switch($gateway['mode'])
			{
				case 'live':
					$post_url = $gateway['arb_url_live'];
				break;
				case 'test':
					$post_url = $gateway['arb_url_test'];
				break;
				case 'dev':
					$post_url = $gateway['arb_url_dev'];
				break;
			}
		}
		
		return $post_url;
	}
	
	//--------------------------------------------------------------------
	
	private function xml2array($xml) {
        $xmlary = array();
                
        $reels = '/<(\w+)\s*([^\/>]*)\s*(?:\/>|>(.*)<\/\s*\\1\s*>)/s';
        $reattrs = '/(\w+)=(?:"|\')([^"\']*)(:?"|\')/';

        preg_match_all($reels, $xml, $elements);

        foreach ($elements[1] as $ie => $xx) {
                $xmlary[$ie]["name"] = $elements[1][$ie];
                
                if ($attributes = trim($elements[2][$ie])) {
                        preg_match_all($reattrs, $attributes, $att);
                        foreach ($att[1] as $ia => $xx)
                                $xmlary[$ie]["attributes"][$att[1][$ia]] = $att[2][$ia];
                }

                $cdend = strpos($elements[3][$ie], "<");
                if ($cdend > 0) {
                        $xmlary[$ie]["text"] = substr($elements[3][$ie], 0, $cdend - 1);
                }

                if (preg_match($reels, $elements[3][$ie]))
                        $xmlary[$ie]["elements"] = $his->xml2array($elements[3][$ie]);
                else if ($elements[3][$ie]) {
                        $xmlary[$ie]["text"] = $elements[3][$ie];
                }
        }

        return $xmlary;
	}
}

// End twocheckout class

//--------------------------------------------------------------------

/**
 * XMLParser Class
 *
 * This class loads an XML document into a SimpleXMLElement that can
 * be processed by the calling application.  This accepts xml strings,
 * files, and DOM objects.  It can also perform the reverse, converting
 * an SimpleXMLElement back into a string, file, or DOM object.
 *
 * I am not sure who the original author of this class is as it was
 * never documented. Henceforth, I am reliquishing ownership of this
 * class.
 */
class XmlConstruct extends XMLWriter
{
		private $formal = false;
    /**
     * Constructor.
     * @param string $prm_rootElementName A root element's name of a current xml document
     * @param string $prm_xsltFilePath Path of a XSLT file.
     * @access public
     * @param null
     */
    public function __construct($prm_rootElementName, $formal=false, $prm_xsltFilePath='') {
				$this->formal = $formal;
        $this->openMemory();
        $this->setIndent(true);
        $this->setIndentString(' ');
				if($this->formal) {
		        $this->startDocument('1.0', 'UTF-8');
				}

        if($prm_xsltFilePath) {
            $this->writePi('xml-stylesheet', 'type="text/xsl" href="'.$prm_xsltFilePath.'"');
        }

        $this->startElement($prm_rootElementName);
    }

    /**
     * Set an element with a text to a current xml document.
     * @access public
     * @param string $prm_elementName An element's name
     * @param string $prm_ElementText An element's text
     * @return null
     */
    public function setElement($prm_elementName, $prm_ElementText) {
        $this->startElement($prm_elementName);
        $this->text($prm_ElementText);
        $this->endElement();
    }

    /**
     * Construct elements and texts from an array.
     * The array should contain an attribute's name in index part
     * and a attribute's text in value part.
     * @access public
     * @param array $prm_array Contains attributes and texts
     * @return null
     */
    public function fromArray($prm_array) {
      if(is_array($prm_array)) {
        foreach ($prm_array as $index => $element) {
          if(is_array($element)) {
            $this->startElement($index);
            $this->fromArray($element);
            $this->endElement();
          }
          else
            $this->setElement($index, $element);
         
        }
      }
    }

    /**
     * Return the content of a current xml document.
     * @access public
     * @param null
     * @return string Xml document
     */
    public function getDocument() {
        $this->endElement();
				if($this->formal) {
		        $this->endDocument();
				}
        return $this->outputMemory();
    }

}
