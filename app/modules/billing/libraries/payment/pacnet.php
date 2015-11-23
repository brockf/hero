<?php

class pacnet
{
	var $settings;
	
	function pacnet() {
		$this->settings = $this->Settings();
	}

	function Settings()
	{
		$settings = array();
		
		$settings['name'] = 'Pacnet';
		$settings['class_name'] = 'pacnet';
		$settings['external'] = FALSE;
		$settings['no_credit_card'] = FALSE;
		$settings['description'] = 'Pacnet, and its RAVEN payment system, are a great way for companies to process transactions online.';
		$settings['is_preferred'] = 1;
		$settings['setup_fee'] = 'n/a';
		$settings['monthly_fee'] = 'n/a';
		$settings['transaction_fee'] = 'n/a';
		$settings['purchase_link'] = 'http://www.pacnetservices.com';
		$settings['allows_updates'] = 1;
		$settings['allows_refunds'] = 1;
		$settings['requires_customer_information'] = 0;
		$settings['requires_customer_ip'] = 0;
		$settings['required_fields'] = array('enabled',
											 'username',
											 'password',
											 'prn',
											 'currency',
											 'accept_visa',
											 'accept_mc',
											 'accept_discover',
											 'accept_dc',
											 'accept_amex');
											 
		$settings['field_details'] = array(
										'enabled' => array(
														'text' => 'Enable this gateway?',
														'type' => 'radio',
														'options' => array(
																		'1' => 'Enabled',
																		'0' => 'Disabled')
														),
										'username' => array(
														'text' => 'Raven Username',
														'type' => 'text'
														),
										'password' => array(
														'text' => 'Raven Shared Secret Password',
														'type' => 'password'
														),
										'prn' => array(
														'text' => 'Payment Routing Number',
														'type' => 'text'
														),
										'currency' => array(
														'text' => 'Currency',
														'type' => 'select',
														'options' => array(
																		'GBP' => 'GBP - Pound Sterling',
																		'EUR' => 'EUR - Euro',
																		'USD' => 'USD - US Dollar',
																		'AUD' => 'AUD - Australian Dollar',
																		'CAD' => 'CAD - Canadian Dollar',
																		'CHF' => 'CHF - Swiss Franc',
																		'DKK' => 'DKK - Danish Krone',
																		'HKD' => 'HKD - Hong Kong Dollar',
																		'IDR' => 'IDR - Rupiah',
																		'JPY' => 'JPY - Yen',
																		'LUF' => 'LUF - Luxembourg Franc',
																		'NOK' => 'NOK - Norwegian Krone',
																		'NZD' => 'NZD - New Zealand Dollar',
																		'SEK' => 'SEK - Swedish Krona',
																		'SGD' => 'SGD - Singapore Dollar',
																		'TRL' => 'TRL - Turkish Lira'
																	)
														),
										'accept_visa' => array(
														'text' => 'Accept VISA?',
														'type' => 'radio',
														'options' => array(
																		'1' => 'Yes',
																		'0' => 'No'
																	)
														),
										'accept_mc' => array(
														'text' => 'Accept MasterCard?',
														'type' => 'radio',
														'options' => array(
																		'1' => 'Yes',
																		'0' => 'No'
																	)
														),
										'accept_discover' => array(
														'text' => 'Accept Discover?',
														'type' => 'radio',
														'options' => array(
																		'1' => 'Yes',
																		'0' => 'No'
																	)
														),
										'accept_dc' => array(
														'text' => 'Accept Diner\'s Club?',
														'type' => 'radio',
														'options' => array(
																		'1' => 'Yes',
																		'0' => 'No'
																	)
														),
										'accept_amex' => array(
														'text' => 'Accept American Express?',
														'type' => 'radio',
														'options' => array(
																		'1' => 'Yes',
																		'0' => 'No'
																	)
														)
											);
		
		return $settings;
	}
	
	function TestConnection($gateway) 
	{
		$response = $this->Process($gateway, array('UserName','Timestamp'),array(),'hello');
		
		if (strstr($response['Response'],'Hello')) {
			return TRUE;
		}
		else {
			return FALSE;
		}
	}
	
	function Charge($order_id, $gateway, $customer, $amount, $credit_card)
	{			
		$CI =& get_instance();
		
		$variables = array(
							'PaymentRoutingNumber' => $gateway['prn'],
							'PaymentType' => 'cc_debit',
							'Amount' => (int)($amount * 100),
							'CurrencyCode' => $gateway['currency'],
							'CardNumber' => $credit_card['card_num'],
							'ExpiryDate' => str_pad($credit_card['exp_month'], 2, "0", STR_PAD_LEFT) . substr($credit_card['exp_year'],-2,2),
							'Reference' => $order_id
						);
						
		if (isset($credit_card['cvv'])) {
			$variables['CVV2'] = $credit_card['cvv'];
		}  
		
		$variables['CardIssuerName'] = $credit_card['name'];
		
		if (isset($customer['ip_address']) and !empty($customer['ip_address'])) {
			$variables['CustomerIP'] = $customer['ip_address'];
		}
		
		$response = $this->Process($gateway, array('UserName','Timestamp','Amount','CurrencyCode','Reference'), $variables, 'submit');
		
		if (isset($response['Status']) and ($response['Status'] == 'Approved' or $response['Status'] == 'Submitted' or $response['Status'] == 'InProgress')) {
			$CI->load->model('billing/order_authorization_model');
			$CI->order_authorization_model->SaveAuthorization($order_id, $response['TrackingNumber'], '');
			$response_array = array('charge_id' => $order_id);
			$response = $CI->response->TransactionResponse(1, $response_array);
		} else {
			$response_array = array('reason' => (isset($response['Status'])) ? 'Error ' . $response['Status'] : 'Undefined Error');
			$response = $CI->response->TransactionResponse(2, $response_array);
		}
		
		return $response;
	}
	
	function Recur ($gateway, $customer, $amount, $charge_today, $start_date, $end_date, $interval, $credit_card, $subscription_id, $total_occurrences = FALSE)
	{
		$CI =& get_instance();
		
		$CI->load->model('billing/order_authorization_model');
		
		// is there a payment for today?
		if ($charge_today === TRUE) {
			// Create an order for today's payment
			$CI->load->model('billing/charge_model');
			$customer['customer_id'] = (isset($customer['customer_id'])) ? $customer['customer_id'] : FALSE;
			$order_id = $CI->charge_model->CreateNewOrder($gateway['gateway_id'], $amount, $credit_card, $subscription_id, $customer['customer_id'], $customer['ip_address']);
			
			$variables = array(
								'PaymentRoutingNumber' => $gateway['prn'],
								'PaymentType' => 'cc_debit',
								'Amount' => (int)($amount * 100),
								'CurrencyCode' => $gateway['currency'],
								'CardNumber' => $credit_card['card_num'],
								'ExpiryDate' => str_pad($credit_card['exp_month'], 2, "0", STR_PAD_LEFT) . substr($credit_card['exp_year'],-2,2),
								'Reference' => $order_id
							);
							
			if (isset($credit_card['cvv'])) {
				$variables['CVV2'] = $credit_card['cvv'];
			}  
			
			$variables['CardIssuerName'] = $credit_card['name'];
			
			if (isset($customer['ip_address']) and !empty($customer['ip_address'])) {
				$variables['CustomerIP'] = $customer['ip_address'];
			}
			
			$response = $this->Process($gateway, array('UserName','Timestamp','Amount','CurrencyCode','Reference'), $variables, 'submit');
			
			if (isset($response['Status']) and ($response['Status'] == 'Approved' or $response['Status'] == 'Submitted' or $response['Status'] == 'InProgress')) {
				$CI->recurring_model->SaveApiAuthNumber($subscription_id, $response['TrackingNumber']);
				$CI->order_authorization_model->SaveAuthorization($order_id, $response['TrackingNumber'], '');

				$CI->charge_model->SetStatus($order_id, 1);
				$response_array = array('charge_id' => $order_id, 'recurring_id' => $subscription_id);
				$response = $CI->response->TransactionResponse(100, $response_array);
			} else {
				// Make the subscription inactive
				$CI->recurring_model->MakeInactive($subscription_id);
				$CI->charge_model->SetStatus($order_id, 0);
				
				$response_array = array('reason' => (isset($response['Status'])) ? 'Error ' . $response['Status'] : 'Undefined Error');
				$response = $CI->response->TransactionResponse(2, $response_array);
			}
		} else {	
			// authorize
			$variables = array(
								'PaymentRoutingNumber' => $gateway['prn'],
								'PaymentType' => 'cc_preauth',
								'Amount' => '1',
								'CurrencyCode' => $gateway['currency'],
								'CardNumber' => $credit_card['card_num'],
								'ExpiryDate' => str_pad($credit_card['exp_month'], 2, "0", STR_PAD_LEFT) . substr($credit_card['exp_year'],-2,2),
								'Reference' => md5(time())
							);
							
			if (isset($credit_card['cvv'])) {
				$variables['CVV2'] = $credit_card['cvv'];
			}  
			
			$variables['CardIssuerName'] = $credit_card['name'];
			
			if (isset($customer['ip_address']) and !empty($customer['ip_address'])) {
				$variables['CustomerIP'] = $customer['ip_address'];
			}
			
			$response = $this->Process($gateway, array('UserName','Timestamp','Amount','CurrencyCode','Reference'), $variables, 'submit');
			
			if (isset($response['Status']) and ($response['Status'] == 'Approved' or $response['Status'] == 'Submitted' or $response['Status'] == 'InProgress')) {
				$CI->recurring_model->SaveApiAuthNumber($subscription_id, $response['TrackingNumber']);
				
				$response_array = array('recurring_id' => $subscription_id);
				$response = $CI->response->TransactionResponse(100, $response_array);
			} else {
				// Make the subscription inactive
				$CI->recurring_model->MakeInactive($subscription_id);
				
				$response_array = array('reason' => (isset($response['Status'])) ? 'Error ' . $response['Status'] : 'Undefined Error');
				$response = $CI->response->TransactionResponse(2, $response_array);
			}
		}
		
		return $response;
	}
	
	function Refund ($gateway, $charge, $authorization)
	{		
		$CI =& get_instance();
		
		$variables = array(
							'PaymentRoutingNumber' => $gateway['prn'],
							'PaymentType' => 'cc_credit',
							'Amount' => (int)($charge['amount'] * 100),
							'CurrencyCode' => $gateway['currency'],
							'TemplateNumber' => $authorization->tran_id,
							'Reference' => md5(time())
						);
		
		$response = $this->Process($gateway, array('UserName','Timestamp','Amount','CurrencyCode','Reference'), $variables, 'submit');
		
		if (isset($response['Status']) and ($response['Status'] == 'Approved' or $response['Status'] == 'Submitted' or $response['Status'] == 'InProgress')) {
			return TRUE;
		} else {
			return FALSE;
		}
	}
	
	function AutoRecurringCharge ($order_id, $gateway, $params) {
		return $this->ChargeRecurring($gateway, $order_id, $params['api_auth_number'], $params['amount']);
	}
	
	function ChargeRecurring($gateway, $order_id, $template_number, $amount)
	{
		$CI =& get_instance();
		
		$variables = array(
							'PaymentRoutingNumber' => $gateway['prn'],
							'PaymentType' => 'cc_debit',
							'Amount' => (int)($amount * 100),
							'CurrencyCode' => $gateway['currency'],
							'TemplateNumber' => $template_number,
							'Reference' => md5(time())
						);
		
		$response = $this->Process($gateway, array('UserName','Timestamp','Amount','CurrencyCode','Reference'), $variables, 'submit');
		
		if (isset($response['Status']) and ($response['Status'] == 'Approved' or $response['Status'] == 'Submitted' or $response['Status'] == 'InProgress')) {
			$response['success'] = TRUE;
			// Save the Auth information
			$CI->load->model('billing/order_authorization_model');
			$CI->order_authorization_model->SaveAuthorization($order_id, $response['TrackingNumber']);
		} else {
			$response['success'] = FALSE;
			$response['reason'] = (isset($response['Status'])) ? 'Error ' . $response['Status'] : 'Undefined Error';
		}
		
		return $response;
	}
	
	function CancelRecurring($subscription)
	{	
		return TRUE;
	}
	
	function UpdateRecurring()
	{
		return TRUE;
	}
	
	function Process($gateway, $signature_variables = array(), $variables = array(), $method = 'hello') 
	{
		$url = $this->GetAPIURL($gateway) . '/' . $method;
		
		// start post_string
		$post_string = '';
		foreach ($variables as $key => $value) {
			$post_string .= $key . '=' . urlencode($value) . '&';
		}
		
		// build signature
		$variables['UserName'] = $gateway['username'];
		$variables['Timestamp'] = gmdate('Y-m-d\TH:i:s.000\Z');
		
		$signature_string = '';
		foreach ($signature_variables as $variable) {
			$signature_string .= $variables[$variable] . ',';
		}
		$signature_string = rtrim($signature_string, ',');
		
		$sha1 = strtoupper(sha1($signature_string));
		
		$signature = strtoupper(sha1($sha1 . ',' . $gateway['password']));
		
		// finish $post_string
		$post_string .= 'UserName=' . urlencode($variables['UserName']) . '&Timestamp=' . urlencode($variables['Timestamp']) . '&Signature=' . $signature;
		
		$request = curl_init($url); // initiate curl object
		curl_setopt($request, CURLOPT_HEADER, 0); // set to 0 to eliminate header info from response
		curl_setopt($request, CURLOPT_RETURNTRANSFER, 1); // Returns response data instead of TRUE(1)
		curl_setopt($request, CURLOPT_POSTFIELDS, $post_string); // use HTTP POST to send form data
		$post_response = curl_exec($request); // execute curl post and store results in $post_response
		curl_close ($request); // close curl object
		
		$response = array();
		
		$pairs = explode('&', $post_response);
		
		if (is_array($pairs)) {
		    foreach ($pairs as $pair)
		    {
				list($key, $value) = explode('=', $pair);
				if ($key != '')
				{
					$key = htmlspecialchars(urldecode($key));
					$value = htmlspecialchars(urldecode($value));
					$response[$key] = $value;
				}
		    }
	    }
	    else {
	    	return FALSE;
	    }
				
		return $response;
	}
	
	function GetAPIURL ($gateway) {
		return $gateway['url_live'];
	}
}