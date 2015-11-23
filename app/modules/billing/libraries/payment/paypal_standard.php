<?php

class paypal_standard
{
	var $settings;

	// If set to TRUE, it will log data sent to and received from PayPal in /writeable/gateway_log.txt.
	private $debug	= false;

	//--------------------------------------------------------------------

	function paypal_standard() {
		$this->settings = $this->Settings();
	}

	//--------------------------------------------------------------------

	function Settings()
	{
		$settings = array();

		$settings['name'] = 'PayPal Express Checkout';
		$settings['class_name'] = 'paypal_standard';
		$settings['external'] = TRUE;
		$settings['no_credit_card'] = TRUE;
		$settings['description'] = 'PayPal Express Checkout is the easiest, cheapest way to accept payments online.  Any Website Payments Standard account supports it.';
		$settings['is_preferred'] = 1;
		$settings['setup_fee'] = '$0';
		$settings['monthly_fee'] = '$0';
		$settings['transaction_fee'] = '2.9% + $0.30';
		$settings['purchase_link'] = 'https://www.paypal.com/ca/mrb/pal=Q4XUN8HMLDQ2N';
		$settings['allows_updates'] = 0;
		$settings['allows_refunds'] = 0;
		$settings['requires_customer_information'] = 0;
		$settings['requires_customer_ip'] = 0;
		$settings['required_fields'] = array('enabled',
											 'mode',
											 'user',
											 'pwd',
											 'signature',
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
																		'test' => 'Sandbox'
																		)
														),
										'user' => array(
														'text' => 'API Username',
														'type' => 'text'
														),
										'pwd' => array(
														'text' => 'API Password',
														'type' => 'password'
														),
										'signature' => array(
														'text' => 'API Signature',
														'type' => 'text',
														),
										'currency' => array(
														'text' => 'Currency',
														'type' => 'select',
														'options' => array(
																		'USD' => 'US Dollar',
																		'AUD' => 'Australian Dollar',
																		'CAD' => 'Canadian Dollar',
																		'EUR' => 'Euro',
																		'GBP' => 'British Pound',
																		'JPY' => 'Japanese Yen',
																		'NZD' => 'New Zealand Dollar',
																		'CHF' => 'Swiss Franc',
																		'HKD' => 'Hong Kong Dollar',
																		'SGD' => 'Singapore Dollar',
																		'SEK' => 'Swedish Krona',
																		'DKK' => 'Danish Krone',
																		'PLN' => 'Polish Zloty',
																		'NOK' => 'Norwegian Krone',
																		'HUF' => 'Hungarian Forint',
																		'CZK' => 'Czech Koruna',
																		'ILS' => 'Israeli New Shekel',
																		'MXN' => 'Mexican Peso',
																		'BRL' => 'Brazilian Real',
																		'MYR' => 'Malaysian Ringgit',
																		'PHP' => 'Philippine Peso',
																		'TWD' => 'New Taiwan Dollar',
																		'THB' => 'Thai Baht',
																		'TRY' => 'Turkish Lira'
																	)
														)
											);
		return $settings;
	}

	//--------------------------------------------------------------------

	function TestConnection($gateway)
	{
		return TRUE;
	}

	//--------------------------------------------------------------------

	function Charge($order_id, $gateway, $customer, $amount, $credit_card, $return_url, $cancel_url)
	{
		$CI =& get_instance();
		$CI->load->model('billing/charge_data_model');
		$CI->load->helper('url');

		// save the return URL
		$CI->charge_data_model->Save($order_id, 'return_url', $return_url);

		$post_url = $this->GetAPIURL($gateway);

		$post = array();
		$post['version'] = '56.0';
		$post['method'] = 'SetExpressCheckout';
		$post['returnurl'] = site_url('callback/paypal_standard/confirm/' . $order_id);
		$post['cancelurl'] = (!empty($cancel_url)) ? $cancel_url : 'http://www.paypal.com';
		$post['noshipping'] = '1';
		$post['addroverride'] = '1';
		$post['allownote'] = '0';
		$post['localecode'] = $CI->config->item('locale');
		$post['solutiontype'] = 'Sole';
		$post['landingpage'] = 'Billing';
		$post['channeltype'] = 'Merchant';

		if (isset($customer['email'])) {
			$post['email'] = $customer['email'];
		}

		if (isset($customer['first_name'])) {
			$post['name'] = $customer['first_name'] . ' ' . $customer['last_name'];
		}

		if (isset($customer['address_1']) and !empty($customer['address_1'])) {
			$post['SHIPTONAME'] = $customer['first_name'] . ' ' . $customer['last_name'];
			$post['SHIPTOSTREET'] = $customer['address_1'];
			$post['SHIPTOSTREET2'] = $customer['address_2'];
			$post['SHIPTOCITY'] = $customer['city'];
			$post['SHIPTOSTATE'] = $customer['state'];
			$post['SHIPTOZIP'] = $customer['postal_code'];
			$post['SHIPTOCOUNTRYCODE'] = $customer['country'];
			$post['SHIPTOPHONENUM'] = $customer['phone'];
		}

		$post['paymentaction'] = 'sale';
		$post['user'] = $gateway['user'];
		$post['pwd'] = $gateway['pwd'];
		$post['signature'] = $gateway['signature'];
		$post['AMT'] = $amount;
		$post['L_DESC0'] = 'Invoice #' . $order_id;
		$post['L_AMT0'] = $amount;
		$post['L_QTY0'] = '1';
		$post['invnum'] = $order_id;
		$post['currencycode'] = $gateway['currency'];

		$response = $this->Process($post_url, $post);

		if ($this->debug)
		{
			$this->log_it('PayPal Express Charge Params: ', $post);
			$this->log_it('PayPal Express Charge Response: ', $response);
		}

		if (!empty($response['TOKEN'])) {
			$CI->load->model('billing/order_authorization_model');
			$CI->order_authorization_model->SaveAuthorization($order_id, $response['TOKEN']);

			// generate express checkout URL
			$url = $this->GetExpressCheckoutURL($gateway);

			$url .= '&token=' . $response['TOKEN'];

			$response_array = array(
							'not_completed' => TRUE, // don't mark charge as complete
							'redirect' => $url, // redirect the user to this address
							'charge_id' => $order_id
						);
			$response = $CI->response->TransactionResponse(1, $response_array);
		}
		else {
			$response_array = array('reason' => $response['L_ERRORCODE0'] . ' - ' . $response['L_LONGMESSAGE0']);
			$response = $CI->response->TransactionResponse(2, $response_array);
		}

		return $response;
	}

	//--------------------------------------------------------------------

	function Recur($gateway, $customer, $amount, $charge_today, $start_date, $end_date, $interval, $credit_card, $subscription_id, $total_occurrences, $return_url, $cancel_url)
	{
		$CI =& get_instance();

		$CI =& get_instance();
		$CI->load->model('billing/charge_data_model');
		$CI->load->helper('url');

		$amount = money_format("%!^i",$amount);

		$subscription = $CI->recurring_model->GetRecurring($subscription_id);

		// when we have a free trial, we want to throw out the initial charge.
		// this happens for all other gateways but because we setup a billing agreement with PayPal,
		// it's slightly different
		if ($charge_today === FALSE) {
			$amount = $subscription['amount'];
		}

		// save the return and cancel URLs
		$CI->charge_data_model->Save('r' . $subscription_id, 'return_url', $return_url);
		$CI->charge_data_model->Save('r' . $subscription_id, 'cancel_url', $cancel_url);

		// save the initial charge amount (it may be different, so we treat it as a separate first charge)
		$CI->charge_data_model->Save('r' . $subscription_id, 'first_charge', $amount);
		
		// save the "renewed" subscription

		$post_url = $this->GetAPIURL($gateway);

		// if the total occurrences are 1, we'll send it as a single payment to PayPal
		// otherwise, we'll send it as a normal PayPal recurring charge
		if ((int)$total_occurrences == 1 and $charge_today === TRUE) {
			$CI->charge_data_model->Save('r' . $subscription_id, 'paypal_charge_type', 'single');

			$post = array();
			$post['version'] = '56.0';
			$post['method'] = 'SetExpressCheckout';
			$post['returnurl'] = site_url('callback/paypal_standard/confirm_recur/' . $subscription_id);
			$post['cancelurl'] = (!empty($cancel_url)) ? $cancel_url : 'http://www.paypal.com';
			$post['noshipping'] = '1';
			$post['addroverride'] = '1';
			$post['allownote'] = '0';
			$post['localecode'] = $CI->config->item('locale');
			$post['solutiontype'] = 'Sole';
			$post['landingpage'] = 'Billing';
			$post['channeltype'] = 'Merchant';

			if (isset($customer['email'])) {
				$post['email'] = $customer['email'];
			}

			if (isset($customer['first_name'])) {
				$post['name'] = $customer['first_name'] . ' ' . $customer['last_name'];
			}

			if (isset($customer['address_1']) and !empty($customer['address_1'])) {
				$post['SHIPTONAME'] = $customer['first_name'] . ' ' . $customer['last_name'];
				$post['SHIPTOSTREET'] = $customer['address_1'];
				$post['SHIPTOSTREET2'] = $customer['address_2'];
				$post['SHIPTOCITY'] = $customer['city'];
				$post['SHIPTOSTATE'] = $customer['state'];
				$post['SHIPTOZIP'] = $customer['postal_code'];
				$post['SHIPTOCOUNTRYCODE'] = $customer['country'];
				$post['SHIPTOPHONENUM'] = $customer['phone'];
			}

			$post['paymentaction'] = 'sale';
			$post['user'] = $gateway['user'];
			$post['pwd'] = $gateway['pwd'];
			$post['signature'] = $gateway['signature'];
			$post['AMT'] = $amount;

			$item_description = 'One-time payment';

			if (isset($subscription['plan']['name'])) {
				$item_description = $subscription['plan']['name'];
			}

			$post['L_DESC0'] = $item_description;
			$post['L_AMT0'] = $amount;
			$post['L_QTY0'] = '1';
			$post['invnum'] = $subscription_id;
			$post['currencycode'] = $gateway['currency'];
		}
		else {
			$CI->charge_data_model->Save('r' . $subscription_id, 'paypal_charge_type', 'subscription');

			$post = array();
			$post['version'] = '56.0';
			$post['method'] = 'SetExpressCheckout';
			$post['returnurl'] = site_url('callback/paypal_standard/confirm_recur/' . $subscription_id);
			$post['cancelurl'] = (!empty($cancel_url)) ? $cancel_url : 'http://www.paypal.com';
			$post['noshipping'] = '1';
			$post['addroverride'] = '1';
			$post['allownote'] = '0';
			$post['localecode'] = $CI->config->item('locale');
			$post['solutiontype'] = 'Sole';
			$post['landingpage'] = 'Billing';
			$post['channeltype'] = 'Merchant';

			if (isset($customer['email'])) {
				$post['email'] = $customer['email'];
			}

			if (isset($customer['first_name'])) {
				$post['name'] = $customer['first_name'] . ' ' . $customer['last_name'];
			}

			if (isset($customer['address_1']) and !empty($customer['address_1'])) {
				$post['SHIPTONAME'] = $customer['first_name'] . ' ' . $customer['last_name'];
				$post['SHIPTOSTREET'] = $customer['address_1'];
				$post['SHIPTOSTREET2'] = $customer['address_2'];
				$post['SHIPTOCITY'] = $customer['city'];
				$post['SHIPTOSTATE'] = $customer['state'];
				$post['SHIPTOZIP'] = $customer['postal_code'];
				$post['SHIPTOCOUNTRYCODE'] = $customer['country'];
				$post['SHIPTOPHONENUM'] = $customer['phone'];
			}

			$post['PAYMENTACTION'] = 'sale';
			$post['user'] = $gateway['user'];
			$post['pwd'] = $gateway['pwd'];
			$post['signature'] = $gateway['signature'];
			$post['AMT'] = $amount;
			$post['invnum'] = $subscription_id;
			$post['currencycode'] = $gateway['currency'];
			$post['L_BILLINGTYPE0'] = 'RecurringPayments';

			$item_description = 'Recurring payment';

			$subscription = $CI->recurring_model->GetRecurring($subscription_id);

			if (isset($subscription['plan']['name'])) {
				$item_description = $subscription['plan']['name'];
			}

			$post['L_DESC0'] = $item_description;
			$post['L_AMT0'] = $amount;
			$post['L_QTY0'] = '1';

			// handle first charges unless there's a free trial
			if ($charge_today === TRUE) {
				// first recurring charge won't start until after the first interval
				// we'll run an instant payment first
				// old start date
				$adjusted_start_date = TRUE;
				$start_date = date('Y-m-d',strtotime($start_date)+(60*60*24*$interval));
			}

			// get true recurring rate, first
			// $subscription loaded above

			$description = ($subscription['amount'] != $amount) ? 'Initial charge: ' . $gateway['currency'] . $amount . ', then ' : '';
			$description .= $gateway['currency'] . money_format("%!^i",$subscription['amount']) . ' every ' . $interval . ' days until ' . date('Y-m-d', strtotime($subscription['end_date']));
			if ($charge_today === FALSE) {
				$description .= ' (free trial ends ' . $start_date . ')';
			}
			$post['L_BILLINGAGREEMENTDESCRIPTION0'] = $description;

			$CI->charge_data_model->Save('r' . $subscription_id, 'profile_description', $description);
		}

		$response = $this->Process($post_url, $post);

		if ($this->debug)
		{
			$this->log_it('PayPal Express Recur Params: ', $post);
			$this->log_it('PayPal Express Recur Response: ', $response);
		}

		if (!empty($response['TOKEN'])) {
			// generate express checkout URL
			$url = $this->GetExpressCheckoutURL($gateway);

			$url .= '&token=' . $response['TOKEN'];

			$response_array = array(
							'not_completed' => TRUE, // don't mark charge as complete
							'redirect' => $url, // redirect the user to this address
							'recurring_id' => $subscription_id
						);
			$response = $CI->response->TransactionResponse(100, $response_array);
		}
		else {
			$response_array = array('reason' => $response['L_ERRORCODE0'] . ' - ' . $response['L_LONGMESSAGE0']);
			$response = $CI->response->TransactionResponse(2, $response_array);
		}

		return $response;
	}

	//--------------------------------------------------------------------

	function Process($url, $post_data)
	{
		$CI =& get_instance();

		$data = '';

		// Build the data string for the request body
		foreach($post_data as $key => $value)
		{
			if(!empty($value))
			{
				$data .= strtoupper($key) . '=' . urlencode($value) . '&';
			}
		}

		// remove the extra ampersand
		$data = substr($data, 0, strlen($data) - 1);

		// setting the curl parameters.
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_VERBOSE, 1);

		// turning off the server and peer verification(TrustManager Concept).
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);

		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_POST, 1);

		// setting the nvpreq as POST FIELD to curl
		curl_setopt($ch, CURLOPT_POSTFIELDS, $data);

		// getting response from server
		$response = curl_exec($ch);

		// Throw an error if we can't continue. Will help in debugging.
		if (curl_error($ch))
		{
			show_error(curl_error($ch));
		}

		$response = $this->response_to_array($response);

		return $response;
	}

	//--------------------------------------------------------------------

	function CancelRecurring($subscription, $gateway)
	{
		$CI =& get_instance();

		// is this a real subscription, or an occurrences = 1 situation?
		// get charge data
		$CI->load->model('billing/charge_data_model');
		$data = $CI->charge_data_model->Get('r' . $subscription['subscription_id']);

		// we have to check for the existence of this key because older subscriptions
		// prior to this version (1.78) won't include this data
		if (isset($data['paypal_charge_type']) and $data['paypal_charge_type'] != 'subscription') {
			return TRUE;
		}

		$CI->load->model('billing/recurring_model');

		$post_url = $this->GetAPIURL($gateway);

		$post = array();
		$post['version'] = '60';
		$post['method'] = 'ManageRecurringPaymentsProfileStatus';
		$post['user'] = $gateway['user'];
		$post['pwd'] = $gateway['pwd'];
		$post['signature'] = $gateway['signature'];
		$post['profileid'] = $subscription['api_customer_reference'];
		$post['action'] = 'Cancel';

		$post_response = $this->Process($post_url, $post);

		if ($this->debug)
		{
			$this->log_it('PayPal Express CancelRecurring Params: ', $post);
			$this->log_it('PayPal Express CancelRecurring Response: ', $post_response);
		}

		if($post_response['ACK'] == 'Success') {
			$response = TRUE;
		} else {
			$response = FALSE;
		}

		return $response;
	}

	//--------------------------------------------------------------------

	function UpdateRecurring($gateway, $subscription, $customer, $params)
	{
		$CI =& get_instance();

		// is this a real subscription, or an occurrences = 1 situation?
		// get charge data
		$CI->load->model('billing/charge_data_model');
		$data = $CI->charge_data_model->Get('r' . $subscription['subscription_id']);

		// we have to check for the existence of this key because older subscriptions
		// prior to this version (1.78) won't include this data
		if (isset($data['paypal_charge_type']) and $data['paypal_charge_type'] != 'subscription') {
			return FALSE;
		}

		$CI->load->model('billing/recurring_model');

		$post_url = $this->GetAPIURL($gateway);

		$post = array();
		$post['version'] = '58.0';
		$post['method'] = 'UpdateRecurringPaymentsProfile';
		$post['user'] = $gateway['user'];
		$post['pwd'] = $gateway['pwd'];
		$post['signature'] = $gateway['signature'];
		$post['profileid'] = $subscription['api_customer_reference'];

		if(isset($params['amount'])) {
			$post['currencycode'] = $gateway['currency'];
			$post['amt'] = $params['amount'];
		}

		if(isset($params['customer_id'])){

			$post['firstname'] = $customer['first_name'];
			$post['lastname'] = $customer['last_name'];
			$post['street'] = $customer['address_1'];

			if($customer['address_1'] != '') {
				$post['street'] .= ' '.$customer['address_2'];
			}

			$post['city'] = $customer['city'];
			$post['state'] = $customer['state'];
			$post['zip'] = $customer['postal_code'];
		}

		$post_response = $this->Process($post_url, $post);

		if ($this->debug)
		{
			$this->log_it('PayPal Express UpdateRecurring Params: ', $post);
			$this->log_it('PayPal Express UpdateRecurring Response: ', $response);
		}

		if($post_response['ACK'] == 'Success') {
			$response = TRUE;
		} else {
			$response = FALSE;
		}

		return $response;
	}

	//--------------------------------------------------------------------

	function AutoRecurringCharge ($order_id, $gateway, $params) {
		return $this->ChargeRecurring($gateway, $params);
	}

	//--------------------------------------------------------------------

	function ChargeRecurring($gateway, $params)
	{
		// is this a real subscription, or an occurrences = 1 situation?
		// get charge data
		$CI =& get_instance();
		$CI->load->model('billing/charge_data_model');
		$data = $CI->charge_data_model->Get('r' . $params['subscription_id']);

		// we have to check for the existence of this key because older subscriptions
		// prior to this version (1.78) won't include this data
		if (isset($data['paypal_charge_type']) and $data['paypal_charge_type'] != 'subscription') {
			$response = array('success' => FALSE, 'reason' => 'Not a subscription.  Occurrences were zero when this subscription was created.');

			if ($this->debug)
			{
				$this->log_it('PayPal Express ChargeRecurring (Old Sub) Params: ', $params);
				$this->log_it('PayPal Express ChargeRecurring (Old Sub) Response: ', $response);
			}

			return $response;
		}

		$CI->load->library('billing/transaction_log');
		$CI->load->model('billing/recurring_model');

		$details = $this->GetProfileDetails($gateway, $params);
		if (!$details) {
			// if we didn't retrieve the profile properly, we'd rather let the subscription
			// go then cancel it due to a one-time connection issue
			return array('success' => TRUE);
		}

		/*
		* We used to check for failed payments but PayPal only marks them failed after like
		15 days...
		*/
		$failed_payments = $details['FAILEDPAYMENTCOUNT'];
		$status = $details['STATUS'];

		$response = array();

		if ($status != 'Cancelled' and (int)$failed_payments === 0) {
			$response['success'] = TRUE;

			// should we cancel this subscription?  i.e., will it expire before the next renew?
			// this is only important because PayPal's charge scheduling sometimes jumps the gun
			if (strtotime($params['end_date']) <= (strtotime($params['next_charge']) + (60*60*24*$params['charge_interval']))) {
				// silently cancel the subscription
				$CI->transaction_log->log_event(FALSE, $params['subscription_id'], 'paypal_pre_cancel', FALSE, __FILE__, __LINE__);
				$next_charge = $CI->recurring_model->GetNextChargeDate($params['subscription_id'], $params['next_charge']);
				$CI->db->update('subscriptions', array('next_charge' => $next_charge), array('subscription_id' => $params['subscription_id']));
				$CI->recurring_model->CancelRecurring($params['subscription_id'], TRUE);
			}
		} else {
			if ((int)$failed_payments > 0) {
				$CI->transaction_log->log_event(FALSE, $params['subscription_id'], 'paypal_charge_recurring_response', array('response' => 'Failed payments: ' . $failed_payments), __FILE__, __LINE__);
			}
			else {
				$CI->transaction_log->log_event(FALSE, $params['subscription_id'], 'paypal_charge_recurring_response', array('response' => 'Status: ' . $status), __FILE__, __LINE__);
			}

			$response['success'] = FALSE;
			$response['reason'] = "The charge has failed.";
		}

		if ($this->debug)
		{
			$this->log_it('PayPal Express ChargeRecurring Profile Details: ', $details);
			$this->log_it('PayPal Express ChargeRecurring Response: ', $response);
		}

		return $response;
	}

	//--------------------------------------------------------------------

	function Callback_confirm ($gateway, $charge, $params) {
		$CI =& get_instance();

		$url = $this->GetAPIURL($gateway);

		$post = array();
		$post['method'] = 'GetExpressCheckoutDetails';
		$post['token'] = $params['token'];
		$post['version'] = '56.0';
		$post['user'] = $gateway['user'];
		$post['pwd'] = $gateway['pwd'];
		$post['signature'] = $gateway['signature'];

		$response = $this->Process($url, $post);

		if ($this->debug)
		{
			$this->log_it('PayPal Express Callback Confirm Params: ', $post);
			$this->log_it('PayPal Express Callback Confirm Response: ', $response);
		}

		if (isset($response['TOKEN']) and $response['TOKEN'] == $params['token']) {
			// we're good

			// complete the payment
			$post = $response; // most of the data is from here
			unset($post['NOTE']);

			$post['METHOD'] = 'DoExpressCheckoutPayment';
			$post['TOKEN'] = $response['TOKEN'];
			$post['PAYMENTACTION'] = 'Sale';
			$post['version'] = '56.0';
			$post['user'] = $gateway['user'];
			$post['pwd'] = $gateway['pwd'];
			$post['signature'] = $gateway['signature'];

			$response = $this->Process($url, $post);

			if ($this->debug)
			{
				$this->log_it('PayPal Express DoExpressCheckoutPayment Request: ', $post);
				$this->log_it('PayPal Express DoExpressCheckoutPayment Response: ', $response);
			}

			if (isset($response['PAYMENTSTATUS']) and ($response['PAYMENTSTATUS'] == 'Completed' or $response['PAYMENTSTATUS'] == 'Pending' or $response['PAYMENTSTATUS'] == 'Processed')) {
				// we're good

				// save authorization (transaction id #)
				$CI->load->model('billing/order_authorization_model');
				$CI->order_authorization_model->SaveAuthorization($charge['id'], $response['TRANSACTIONID']);

				$CI->charge_model->SetStatus($charge['id'], 1);

				// get return URL from original request
				$CI->load->model('billing/charge_data_model');
				$data = $CI->charge_data_model->Get($charge['id']);

				// redirect back to user's site
				header('Location: ' . $data['return_url']);
				die();
			}
		}

		die(show_error('Your PayPal payment has failed.  Please contact the site administrator.'));
	}

	//--------------------------------------------------------------------

	function Callback_confirm_recur ($gateway, $subscription, $params) {
		$CI =& get_instance();

		// get charge data
		$CI->load->model('billing/charge_data_model');
		$data = $CI->charge_data_model->Get('r' . $subscription['id']);

		// this gets complex below, so we'll track the general success of this process
		// with a simple boolean variable
		$process_status = FALSE;

		$url = $this->GetAPIUrl($gateway);

		$post = array();
		$post['method'] = 'GetExpressCheckoutDetails';
		$post['token'] = $params['token'];
		$post['version'] = '56.0';
		$post['user'] = $gateway['user'];
		$post['pwd'] = $gateway['pwd'];
		$post['signature'] = $gateway['signature'];

		$response = $this->Process($url, $post);

		if ($this->debug)
		{
			$this->log_it('PayPal Express Callback Confirm Recur Params: ', $post);
			$this->log_it('PayPal Express Charge Callback Confirm Response: ', $response);
		}

		if (isset($response['TOKEN']) and $response['TOKEN'] == $params['token']) {
			// tokens match.  this is a legitimate PayPal request

			// do we need a first charge?
			if (date('Y-m-d',strtotime($subscription['start_date'])) == date('Y-m-d', strtotime($subscription['date_created']))) {
				$CI->load->model('billing/charge_model');

				// get the first charge amount (it may be different)
				$first_charge_amount = (isset($data['first_charge'])) ? $data['first_charge'] : $subscription['amount'];
				$first_charge_amount = (float)$first_charge_amount;

				if (!empty($first_charge_amount)) {
					$customer_id = (isset($subscription['customer']['id'])) ? $subscription['customer']['id'] : FALSE;
					$order_id = $CI->charge_model->CreateNewOrder($gateway['gateway_id'], $first_charge_amount, array(), $subscription['id'], $customer_id);

					// yes, the first charge is today
					$post = $response; // most of the data is from here
					unset($post['NOTE']);

					$post['METHOD'] = 'DoExpressCheckoutPayment';
					$post['TOKEN'] = $response['TOKEN'];
					$post['PAYMENTACTION'] = 'Sale';
					$post['version'] = '56.0';
					$post['user'] = $gateway['user'];
					$post['pwd'] = $gateway['pwd'];
					$post['signature'] = $gateway['signature'];

					$response_charge = $this->Process($url, $post);

					if ($this->debug)
					{
						$this->log_it('PayPal Express Callback Confirm Recur - First Charge Params: ', $post);
						$this->log_it('PayPal Express Callback Confirm Recur - First Charge Response: ', $response_charge);
					}

					if (!isset($response_charge['PAYMENTSTATUS']) or ($response_charge['PAYMENTSTATUS'] != 'Completed' and $response_charge['PAYMENTSTATUS'] != 'Pending' and $response_charge['PAYMENTSTATUS'] != 'Processed')) {
						die(show_error('Your PayPal payment has failed (initial setup).  Please contact the site administrator.'));
					}
					else {
						// create today's order
						// we assume it's good because the profile is OK

						$CI->load->model('billing/order_authorization_model');
						$CI->order_authorization_model->SaveAuthorization($order_id, $response_charge['TRANSACTIONID']);

						$CI->charge_model->SetStatus($order_id, 1);
					}

					// we'll also adjust the profile start date
					$adjusted_start_date = TRUE;
					$subscription['start_date'] = date('Y-m-d',strtotime($subscription['start_date'])+(60*60*24*$subscription['interval']));
				}
			}

			// if this was sent to PayPal as a recurring payment, we'll create the profile here
			if ($data['paypal_charge_type'] == 'subscription') {
				// continue with creating payment profile
				$post = $response; // most of the data is from here
				unset($post['NOTE']);

				$post['METHOD'] = 'CreateRecurringPaymentsProfile';
				$post['VERSION'] = '60';
				$post['user'] = $gateway['user'];
				$post['pwd'] = $gateway['pwd'];
				$post['signature'] = $gateway['signature'];
				$post['TOKEN'] = $response['TOKEN'];
				$post['DESC'] = $data['profile_description'];
				$post['PROFILESTARTDATE'] = date('c',strtotime($subscription['start_date']));
				$post['BILLINGPERIOD'] = 'Day';
				$post['BILLINGFREQUENCY'] = $subscription['interval'];
				$post['AMT'] = $subscription['amount'];

				$response_sub = $this->Process($url, $post);

				if ($this->debug)
				{
					$this->log_it('PayPal Express Create Payment Profile Params: ', $post);
					$this->log_it('PayPal Express Create Payment Profile Response: ', $response_sub);
				}


				if (isset($response_sub['PROFILEID'])) {
					// success!
					$CI->recurring_model->SaveApiCustomerReference($subscription['id'], $response_sub['PROFILEID']);

					$process_status = TRUE;
				}
			}
			else {
				// we know we are good because the first charge was executed before and, if it failed
				// we'd have die()'d above
				$process_status = TRUE;
			}

			if ($process_status === TRUE) {
				// success!
				$order_id = (isset($order_id)) ? $order_id : FALSE;

				$CI->recurring_model->SetActive($subscription['id']);

				// hook
				$CI->load->library('app_hooks');
				$CI->app_hooks->data('subscription', $subscription['id']);
				$CI->app_hooks->trigger('subscription_new', $subscription['id']);

				// trip a recurring charge?
				if ($order_id) {
					// hook
					$CI->app_hooks->data('invoice', $order_id);
					$CI->app_hooks->trigger('subscription_charge', $order_id, $subscription['id']);
				}

				$CI->app_hooks->reset();

				// redirect back to user's site
				header('Location: ' . $data['return_url']);
				die();
			}
			else {
				die(show_error('Your PayPal payment has failed (profile error).  Please contact the site administrator.'));
			}
		}

		die(show_error('Your PayPal payment has failed.  Please contact the site administrator.'));
	}

	//--------------------------------------------------------------------

	function GetProfileDetails($gateway, $params)
	{
		$CI =& get_instance();
		$CI->load->model('billing/recurring_model');

		$post_url = $this->GetAPIURL($gateway);

		$post = array();
		$post['version'] = '60';
		$post['method'] = 'GetRecurringPaymentsProfileDetails';
		$post['user'] = $gateway['user'];
		$post['pwd'] = $gateway['pwd'];
		$post['signature'] = $gateway['signature'];
		$post['profileid'] = $params['api_customer_reference'];

		$post_response = $this->Process($post_url, $post);

		if ($this->debug)
		{
			$this->log_it('PayPal Express GetProfileDetails Params: ', $post);
			$this->log_it('PayPal Express GetProfileDetails Response: ', $post_response);
		}

		if ($post_response['ACK'] == 'Success') {
			return $post_response;
		} else {
			return FALSE;
		}
	}

	//--------------------------------------------------------------------

	private function GetAPIURL ($gateway) {
		if ($gateway['mode'] == 'test') {
			return $gateway['url_test'];
		}
		else {
			return $gateway['url_live'];
		}
	}

	//--------------------------------------------------------------------

	private function GetExpressCheckoutURL ($gateway) {
		if ($gateway['mode'] == 'test') {
			return $gateway['arb_url_test'];
		}
		else {
			return $gateway['arb_url_live'];
		}
	}

	//--------------------------------------------------------------------

	private function response_to_array($string)
	{
		$string = urldecode($string);
		$pairs = explode('&', $string);
		$values = array();

		foreach($pairs as $pair)
		{
			list($key, $value) = explode('=', $pair);
			$values[$key] = $value;
		}

		return $values;
	}

	//--------------------------------------------------------------------

	/*
		Method: log_it()

		Logs the transaction to a file. Helpful with debugging callback
		transactions, since we can't actually see what's going on.

		Parameters:
			$heading	- A string to be placed above the resutls
			$params		- Typically an array to print_r out so that we can inspect it.
	*/
	public function log_it($heading, $params)
	{
		$file = FCPATH .'writeable/gateway_log.txt';

		$content = '';
		$content .= "# $heading\n";
		$content .= date('Y-m-d H:i:s') ."\n\n";
		$content .= print_r($params, true);
		file_put_contents($file, $content, FILE_APPEND);
	}

}
