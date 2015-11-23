<?php

class sagepay
{
	var $settings;
	
	function sagepay() {
		$this->settings = $this->Settings();
	}

	function Settings()
	{
		$settings = array();
		
		$settings['name'] = 'SagePay';
		$settings['class_name'] = 'sagepay';
		$settings['external'] = FALSE;
		$settings['no_credit_card'] = FALSE;
		$settings['description'] = 'SagePay is the premier merchant account provider for the United Kingdom.';
		$settings['is_preferred'] = 1;
		$settings['setup_fee'] = '&pound;0';
		$settings['monthly_fee'] = '&pound;20';
		$settings['transaction_fee'] = '10p';
		$settings['purchase_link'] = 'https://support.protx.com/apply/default.aspx?PartnerID=D16D4B72-87D5-4E97-A743-B45078E146CB';
		$settings['allows_updates'] = 1;
		$settings['allows_refunds'] = 0;
		$settings['requires_customer_information'] = 1;
		$settings['requires_customer_ip'] = 0;
		$settings['required_fields'] = array(
										'enabled',
										'mode', 
										'vendor',
										'currency',
										'accept_visa',
										'accept_mc',
										'accept_discover',
										'accept_dc',
										'accept_amex'
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
																		'test' => 'Test Mode',
																		'simulator' => 'Simulator'
																		)
														),
										'vendor' => array(
														'text' => 'Vendor',
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
		// There's no way to test the connection at this point
		return TRUE;
	}
	
	function Charge($order_id, $gateway, $customer, $amount, $credit_card, $txtype = 'PAYMENT')
	{	
		$CI =& get_instance();
					
		$post_url = $this->GetAPIUrl($gateway);
		
		// get card type in proper format
		switch($credit_card['card_type']) {
			case 'visa';
				$card_type = 'VISA';
			break;
			case 'mc';
				$card_type = 'MC';
			break;
			case 'discover';
				$card_type = 'DC';
			break;
			case 'amex';
				$card_type = 'AMEX';
			break;
		}
		
		$post_values = array(
			"VPSProtocol" => "2.23",
			"TxType" => $txtype,
			"Vendor" => $gateway['vendor'],
			"VendorTxCode" => 'opengateway-' . $order_id,
			"Amount" => $amount,
			"Currency" => $gateway['currency'],
			"Description" => "API Payment at " . date('Y-m-d H:i:s') . " via " . $CI->config->item('server_name'),
			"CardHolder" => $credit_card['name'],
			"CardNumber" => $credit_card['card_num'],
			"ExpiryDate" => str_pad($credit_card['exp_month'], 2, "0", STR_PAD_LEFT) . substr($credit_card['exp_year'],-2,2),
			"CardType" => $card_type,
			"Apply3DSecure" => "2" // No 3DSecure checks, ever
		);

		if(isset($credit_card['cvv'])) {
			$post_values['CV2'] = $credit_card['cvv'];
		}	
		
		if (isset($customer['customer_id'])) {
			$post_values['BillingFirstNames'] = $customer['first_name'];
			$post_values['BillingSurname'] = $customer['last_name'];
			$post_values['BillingAddress1'] = $customer['address_1'];
			if (isset($customer['address_2']) and !empty($customer['address_2'])) {
				$post_values['BillingAddress2'] = ' - '.$customer['address_2'];
			}
			$post_values['BillingCity'] = $customer['city'];
			if (!empty($customer['state']) and !empty($customer['country']) and ($customer['country'] == 'US')) {
				// only for North American customers
				$post_values['BillingState'] = $customer['state'];
			}
			$post_values['BillingPostCode'] = $customer['postal_code'];
			$post_values['BillingCountry'] = $customer['country'];
			if (!empty($customer['phone'])) {
				$post_values['BillingPhone'] = $customer['phone'];
			}
			
			if (!empty($customer['email'])) {
				$post_values['CustomerEMail'] = $customer['email'];
			}
			
			if (!empty($customer['ip_address'])) {
				$post_values['ClientIPAddress'] = $customer['ip_address'];
			}
			
			// duplicate for delivery
			$post_values['DeliveryFirstNames'] = $customer['first_name'];
			$post_values['DeliverySurname'] = $customer['last_name'];
			$post_values['DeliveryAddress1'] = $customer['address_1'];
			if (isset($customer['address_2']) and !empty($customer['address_2'])) {
				$post_values['DeliveryAddress2'] = ' - '.$customer['address_2'];
			}
			$post_values['DeliveryCity'] = $customer['city'];
			if (!empty($customer['state']) and !empty($customer['country']) and ($customer['country'] == 'US')) {
				// only for North American customers
				$post_values['DeliveryState'] = $customer['state'];
			}
			$post_values['DeliveryPostCode'] = $customer['postal_code'];
			$post_values['DeliveryCountry'] = $customer['country'];
			if (!empty($customer['phone'])) {
				$post_values['DeliveryPhone'] = $customer['phone'];
			}
		}
		
		$response = $this->Process($order_id, $post_url, $post_values);
		
		if ($response['success'] == TRUE){
			$response_array = array('charge_id' => $order_id);
			$response = $CI->response->TransactionResponse(1, $response_array);
		} else {
			$response_array = array('reason' => $response['reason']);
			$response = $CI->response->TransactionResponse(2, $response_array);
		}
		
		return $response;
	}
	
	function Recur ($gateway, $customer, $amount, $charge_today, $start_date, $end_date, $interval, $credit_card, $subscription_id, $total_occurrences = FALSE, $return_url = '', $cancel_url = '')
	{		
		$CI =& get_instance();
		
		// if a payment is to be made today, process it.
		if ($charge_today === TRUE) {
			// Create an order for today's payment
			$CI->load->model('billing/charge_model');
			$order_id = $CI->charge_model->CreateNewOrder($gateway['gateway_id'], $amount, $credit_card, $subscription_id, $customer['customer_id'], $customer['ip_address']);
			
			$response = $this->Charge($order_id, $gateway, $customer, $amount, $credit_card);
			
			if ($response['response_code'] == '1') {
				$CI->charge_model->SetStatus($order_id, 1);
				$response_array = array('charge_id' => $order_id, 'recurring_id' => $subscription_id);
				$response = $CI->response->TransactionResponse(100, $response_array);
			} else {
				// Make the subscription inactive
				$CI->recurring_model->MakeInactive($subscription_id);
				
				$response_array = array('reason' => $response['reason']);
				$response = $CI->response->TransactionResponse(2, $response_array);
			}
		} else {
			// we need to process an initial AUTHENTICATE transaction in order to send REPEATs later
			
			// generate a fake random Order ID - this isn't a true order
			$order_id = rand(100000,1000000);
			$response = $this->Charge($order_id, $gateway, $customer, $amount, $credit_card, 'AUTHENTICATE');
			
			if ($response['response_code'] == '1') {
				$response_array = array('recurring_id' => $subscription_id);
				$response = $CI->response->TransactionResponse(100, $response_array);
			} else {
				// Make the subscription inactive
				$CI->recurring_model->MakeInactive($subscription_id);
				
				$response_array = array('reason' => $response['reason']);
				$response = $CI->response->TransactionResponse(2, $response_array);
			}
		}
		
		// let's save the transaction details for future REPEATs
		
		// for SagePay:
		//		api_customer_reference = VPSTxId
		//		api_payment_reference = VendorTxCode|VendorTxAuthNo
		//		api_auth_number = SecurityKey
		
		// these authorizations were saved during $this->Process()
		if ($response['response_code'] != '2') {
			$authorizations = $CI->order_authorization_model->GetAuthorization($order_id);
			
			$CI->recurring_model->SaveApiCustomerReference($subscription_id, $authorizations->tran_id);
			$CI->recurring_model->SaveApiPaymentReference($subscription_id, $authorizations->order_id . '|' . $authorizations->authorization_code);
			$CI->recurring_model->SaveApiAuthNumber($subscription_id, $authorizations->security_key);
		}
		
		return $response;
	}
	
	function CancelRecurring($subscription)
	{	
		return TRUE;
	}
	
	function AutoRecurringCharge ($order_id, $gateway, $params) {		
		return $this->ChargeRecurring($gateway, $order_id, $params['api_customer_reference'], $params['api_payment_reference'], $params['api_auth_number'], $params['amount']);
	}
	
	function ChargeRecurring($gateway, $order_id, $VPSTxId, $VendorTxCodeVendorTxAuthNo, $SecurityKey, $amount)
	{		
		$CI =& get_instance();
		
		list($VendorTxCode,$VendorTxAuthNo) = explode('|',$VendorTxCodeVendorTxAuthNo);
		
		$post_url = $this->GetAPIUrl($gateway, 'repeat');
		
		$post_values = array(
			"VPSProtocol" => "2.23",
			"TxType" => 'REPEAT',
			"Vendor" => $gateway['vendor'],
			"VendorTxCode" => 'opengateway-' . $order_id,
			"Amount" => $amount,
			"Currency" => $gateway['currency'],
			"Description" => "API Payment at " . date('Y-m-d H:i:s'),
			"RelatedVPSTxId" => $VPSTxId,
			"RelatedVendorTxCode" => 'opengateway-' . $VendorTxCode,
			"RelatedTxAuthNo" => $VendorTxAuthNo,
			"RelatedSecurityKey" => $SecurityKey,
			"AccountType" => "C"
		);

		$response = $this->Process($order_id, $post_url, $post_values);
		
		if ($response['success'] == TRUE){
			return $response;
		} else {
			$response['success'] = FALSE;
			$response['reason'] = $response['reason'];
			
			return $response;
		}	
	}
	
	function UpdateRecurring()
	{
		return TRUE;
	}
	
	function Process($order_id, $post_url, $post_values)
	{
		$CI =& get_instance();
		$CI->load->model('billing/charge_model');
		
		// build NVP post string
		$post_string = "";
		foreach($post_values as $key => $value) {
			$post_string .= "$key=" . urlencode( $value ) . "&";
		}
		$post_string = rtrim($post_string, "& ");
		
		$request = curl_init($post_url); // initiate curl object
		curl_setopt($request, CURLOPT_HEADER, 0); // set to 0 to eliminate header info from response
		curl_setopt($request, CURLOPT_RETURNTRANSFER, 1); // Returns response data instead of TRUE(1)
		curl_setopt($request, CURLOPT_POSTFIELDS, $post_string); // use HTTP POST to send form data
		curl_setopt($request, CURLOPT_SSL_VERIFYPEER, TRUE); // uncomment this line if you get no gateway response.
		$post_response = curl_exec($request); // execute curl post and store results in $post_response
		
		curl_close ($request); // close curl object
		
		$response_lines = explode("\r\n",$post_response);
		
		if (!is_array($response_lines)) {
			// we didn't receive back a series of newlines like we thought we would
			$response = array();
			$response['success'] = FALSE;
			
			return $response;
		}
		
		// put into array
		$response = array();
		foreach ($response_lines as $line) {
			if (!empty($line)) {
				list($name,$value) = explode('=',$line);
				$response[$name] = $value;
			}
		}
		
		// the OK message changes depending on the type
		if ($post_values['TxType'] == 'PAYMENT') {
			$ok_message = 'OK';
		}
		elseif ($post_values['TxType'] == 'REPEAT') {
			$ok_message = 'OK';
		}
		elseif ($post_values['TxType'] == 'AUTHENTICATE') {
			$ok_message = 'REGISTERED';
		}
		
		// did it process properly?
		if($response['Status'] == $ok_message) {
			$CI->load->model('billing/order_authorization_model');
			$CI->order_authorization_model->SaveAuthorization($order_id, $response['VPSTxId'], $response['TxAuthNo'], $response['SecurityKey']);
			$CI->charge_model->SetStatus($order_id, 1);
			
			$response['success'] = TRUE;
		} else {
			$CI->load->model('billing/charge_model');
			$CI->charge_model->SetStatus($order_id, 0);
			
			$response['success'] = FALSE;
			$response['reason'] = $response['StatusDetail'];
		}

		return $response;
	}
	
	function GetAPIUrl($gateway, $mode = FALSE) {
		if ($mode == FALSE) {
			switch($gateway['mode']) {
				case 'live':
					$post_url = $gateway['url_live'];
				break;
				case 'test':
					$post_url = $gateway['url_test'];
				break;
				case 'simulator':
					$post_url = $gateway['url_dev'];
				break;
			}
		}
		elseif ($mode == 'repeat') {
			switch($gateway['mode']) {
				case 'live':
					$post_url = $gateway['arb_url_live'];
				break;
				case 'test':
					$post_url = $gateway['arb_url_test'];
				break;
				case 'simulator':
					$post_url = $gateway['arb_url_dev'];
				break;
			}
		}
		
		return $post_url;
	}
}