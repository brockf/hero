<?php

class wirecard
{
	var $settings;
	
	function wirecard() {
		$this->settings = $this->Settings();
	}

	function Settings()
	{
		$settings = array();
		
		$settings['name'] = 'Wirecard';
		$settings['class_name'] = 'wirecard';
		$settings['external'] = FALSE;
		$settings['no_credit_card'] = FALSE;
		$settings['description'] = 'Wirecard is a premium provider of online credit card processing services available for international merchants.';
		$settings['is_preferred'] = 1;
		$settings['setup_fee'] = '$33';
		$settings['monthly_fee'] = '$19';
		$settings['transaction_fee'] = '$0.23';
		$settings['purchase_link'] = 'http://www.wirecard.com/products/payment/credit-card-processing.html';
		$settings['allows_updates'] = 1;
		$settings['allows_refunds'] = 1;
		$settings['requires_customer_information'] = 1;
		$settings['requires_customer_ip'] = 1;
		$settings['required_fields'] = array(
										'enabled',
										'mode',
										'gateway_url',
										'username',
										'password',
										'businesscasesignature',
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
																		'demo' => 'Test Mode'
																		)
														),
										'gateway_url' => array(
														'text' => 'Gateway URL',
														'type' => 'text'
														),
										'username' => array(
														'text' => 'Username',
														'type' => 'text'
														),
										'password' => array(
														'text' => 'Password',
														'type' => 'password'
														),
										'businesscasesignature' => array(
														'text' => 'Business Case Signature',
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
																		'ISK' => 'ISK - Icelandic Krona',
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
	
	function Charge($order_id, $gateway, $customer, $amount, $credit_card)
	{	
		$CI =& get_instance();
		
		$transaction = $this->TransactionArray($order_id, $gateway, $customer, $amount, $credit_card);
		
		$response = $this->Process($gateway, $transaction);
		
		$result = $response['W_RESPONSE']['W_JOB']['FNC_CC_PURCHASE']['CC_TRANSACTION']['PROCESSING_STATUS'];
		
		if ($result['FunctionResult'] == 'ACK' or $result['FunctionResult'] == 'PENDING') {
			$CI->load->model('billing/order_authorization_model');
			$CI->order_authorization_model->SaveAuthorization($order_id, $result['GuWID']);
			
			$CI->load->model('billing/charge_model');
			$CI->charge_model->SetStatus($order_id, 1);
			
			$response_array = array('charge_id' => $order_id);
			$response = $CI->response->TransactionResponse(1, $response_array);
		} else {
			$response_array = array('reason' => $result['ERROR']['Message'] . ' (' . $result['ERROR']['Number'] . ')');
			$response = $CI->response->TransactionResponse(2, $response_array);
		}
		
		return $response;
	}
	
	function Recur ($gateway, $customer, $amount, $charge_today, $start_date, $end_date, $interval, $credit_card, $subscription_id, $total_occurrences = FALSE)
	{		
		$CI =& get_instance();
		
		$CI->load->model('billing/order_authorization_model');
		
		$successful_recur = FALSE;
		
		// if a payment is to be made today, process it.
		if ($charge_today === TRUE) {
			// Create an order for today's payment
			$CI->load->model('billing/charge_model');
			$order_id = $CI->charge_model->CreateNewOrder($gateway['gateway_id'], $amount, $credit_card, $subscription_id, $customer['customer_id'], $customer['ip_address']);
			
			$transaction = $this->TransactionArray($order_id, $gateway, $customer, $amount, $credit_card, 'Initial');
			
			$response = $this->Process($gateway, $transaction);

			$result = $response['W_RESPONSE']['W_JOB']['FNC_CC_PURCHASE']['CC_TRANSACTION']['PROCESSING_STATUS'];
			
			if ($result['FunctionResult'] == 'ACK' or $result['FunctionResult'] == 'PENDING') {
				$CI->charge_model->SetStatus($order_id, 1);
				$CI->order_authorization_model->SaveAuthorization($order_id, $result['GuWID']);
				$response_array = array('charge_id' => $order_id, 'recurring_id' => $subscription_id);
				$response = $CI->response->TransactionResponse(100, $response_array);
				$successful_recur = TRUE;
			} else {
				// Make the subscription inactive
				$CI->recurring_model->MakeInactive($subscription_id);
				
				$response_array = array('reason' => $result['ERROR']['Message'] . ' (' . $result['ERROR']['Number'] . ')');
				$response = $CI->response->TransactionResponse(2, $response_array);
			}
		} else {
			// we need to process an initial AUTHENTICATE transaction in order to send REPEATs later
			
			// we'll use a $0 order not linked to any customer
			$CI->load->model('billing/charge_model');
			$order_id = $CI->charge_model->CreateNewOrder($gateway['gateway_id'], '0.00', $credit_card, $subscription_id);
			
			$transaction = $this->TransactionArray($order_id, $gateway, $customer, $amount, $credit_card, 'Initial', 'FNC_CC_AUTHORIZATION');
			
			$response = $this->Process($gateway, $transaction);
			
			$result = $response['W_RESPONSE']['W_JOB']['FNC_CC_AUTHORIZATION']['CC_TRANSACTION']['PROCESSING_STATUS'];
			
			if ($result['FunctionResult'] == 'ACK' or $result['FunctionResult'] == 'PENDING') {
				$CI->charge_model->SetStatus($order_id, 1);
				$response_array = array('recurring_id' => $subscription_id);
				$response = $CI->response->TransactionResponse(100, $response_array);
				$successful_recur = TRUE;
			} else {
				// Make the subscription inactive
				$CI->recurring_model->MakeInactive($subscription_id);
				
				$response_array = array('reason' => $result['ERROR']['Message'] . ' (' . $result['ERROR']['Number'] . ')');
				$response = $CI->response->TransactionResponse(2, $response_array);
			}
		}
		
		if ($successful_recur == TRUE) {
			// let's save the transaction details for future REPEATs
			$CI->recurring_model->SaveApiPaymentReference($subscription_id, $result['GuWID']);
		}
		
		return $response;
	}
	
	function Refund ($gateway, $charge, $authorization)
	{	
		$CI =& get_instance();
		
		$transaction = array(
			'WIRECARD_BXML' => array(
				'W_REQUEST' => array(
					'W_JOB' => array(
							'BusinessCaseSignature' => $gateway['businesscasesignature'],
							'FNC_CC_BOOKBACK' => array(
								'CC_TRANSACTION' => array(
									'TransactionID' => $charge['id'],
									'GuWID' => $authorization->tran_id,
									'Amount' => (int)($charge['amount'] * 100),
									'Currency' => $gateway['currency']
								)
							)
						)
					)	
				)	
			);
			
		$response = $this->Process($gateway, $transaction);
		
		if (isset($response['W_RESPONSE']['W_JOB']['FNC_CC_BOOKBACK']['CC_TRANSACTION']['PROCESSING_STATUS']['FunctionResult']) and $response['W_RESPONSE']['W_JOB']['FNC_CC_BOOKBACK']['CC_TRANSACTION']['PROCESSING_STATUS']['FunctionResult'] == 'ACK') {
			return TRUE;
		}
		else {
			return FALSE;
		}
	}
	
	function CancelRecurring($subscription)
	{	
		return TRUE;
	}
	
	function AutoRecurringCharge ($order_id, $gateway, $params) {		
		return $this->ChargeRecurring($gateway, $order_id, $params['api_payment_reference'], $params['amount']);
	}
	
	function ChargeRecurring($gateway, $order_id, $GuWID, $amount)
	{		
		$CI =& get_instance();
		
		$transaction = array(
						'WIRECARD_BXML' => array(
							'W_REQUEST' => array(
								'W_JOB' => array(
										'BusinessCaseSignature' => $gateway['businesscasesignature'],
										'FNC_CC_PURCHASE' => array(
											'CC_TRANSACTION' => array(
												'TransactionID' => $order_id,
												'GuWID' => $GuWID,
												'Amount' => (int)($amount * 100),
												'Currency' => $gateway['currency'],
												'CountryCode' => $CI->config->item('locale'),
												'RECURRING_TRANSACTION' => array(
													'Type' => 'Repeated',
												)
											)
										)
									)
								)	
							)	
						);

		$response = $this->Process($gateway, $transaction);
		
		$result = $response['W_RESPONSE']['W_JOB']['FNC_CC_PURCHASE']['CC_TRANSACTION']['PROCESSING_STATUS'];
		
		if ($result['FunctionResult'] == 'ACK' or $result['FunctionResult'] == 'PENDING'){
			$response = array(
							'success' => TRUE
						);
		} else {
			$response = array();
			
			$response['success'] = FALSE;
			$response['reason'] = $response['reason'];
		}	
		
		return $response;
	}
	
	function UpdateRecurring()
	{
		return TRUE;
	}
	
	function Process($gateway, $transaction_array)
	{
		$CI =& get_instance();
		
		$CI->load->library('array_to_xml');
		
		$transaction_xml = $CI->array_to_xml->ToXML($transaction_array);
		
		// make unique adjustments
		$transaction_xml = (string)$transaction_xml;
		$transaction_xml = str_replace('<WIRECARD_BXML>','<WIRECARD_BXML xmlns:xsi="http://www.w3.org/1999/XMLSchema-instance">',$transaction_xml);
		$transaction_xml = str_replace('<CC_TRANSACTION>','<CC_TRANSACTION mode="' . $gateway['mode'] . '">',$transaction_xml);
		$transaction_xml = str_replace('<Amount>','<Amount minorunits="2">',$transaction_xml);
		$transaction_xml = str_replace('<ResultSet>','',$transaction_xml);
		$transaction_xml = str_replace('</ResultSet>','',$transaction_xml);
		
		$post_url = $gateway['gateway_url'];
		
		$request = curl_init($post_url); // initiate curl object
		curl_setopt($request, CURLOPT_HTTPHEADER, Array("Content-Type: text/xml", "Authorization: Basic " . base64_encode($gateway['username'] . ':' . $gateway['password'])));
		curl_setopt($request, CURLOPT_HEADER, 0); // set to 0 to eliminate header info from response
		curl_setopt($request, CURLOPT_RETURNTRANSFER, 1); // Returns response data instead of TRUE(1)
		curl_setopt($request, CURLOPT_POSTFIELDS, $transaction_xml); // use HTTP POST to send form data
		curl_setopt($request, CURLOPT_SSL_VERIFYPEER, FALSE); // uncomment this line if you get no gateway response.
		$post_response = curl_exec($request); // execute curl post and store results in $post_response
		
		curl_close ($request); // close curl object
	
		$response = $CI->array_to_xml->ToArray($post_response);
		
		return $response;
	}
	
	function TransactionArray ($order_id, $gateway, $customer, $amount, $credit_card, $type = 'Single', $node = 'FNC_CC_PURCHASE') {
		$CI =& get_instance();

		$transaction = array(
						'WIRECARD_BXML' => array(
							'W_REQUEST' => array(
								'W_JOB' => array(
										'BusinessCaseSignature' => $gateway['businesscasesignature'],
										$node => array(
											'CC_TRANSACTION' => array(
												'TransactionID' => $order_id,
												'Amount' => (int)($amount * 100),
												'Currency' => $gateway['currency'],
												'CountryCode' => $CI->config->item('locale'),
												'RECURRING_TRANSACTION' => array(
													'Type' => $type,
												),
												'CREDIT_CARD_DATA' => array(
													'CreditCardNumber' => $credit_card['card_num'],
													'CVC2' => $credit_card['cvv'],
													'ExpirationYear' => $credit_card['exp_year'],
													'ExpirationMonth' => str_pad($credit_card['exp_month'], 2, "0", STR_PAD_LEFT),
													'CardHolderName' => isset($credit_card['name']) ? $credit_card['name'] : $credit_card['card_name']
												),
												'CONTACT_DATA' => array(
													'IPAddress' => isset($customer['ip_address']) ? $customer['ip_address'] : '0.0.0.0'
												),
												'CORPTRUSTCENTER_DATA' => array(
													'ADDRESS' => array(
														'FirstName' => $customer['first_name'],
														'LastName' => $customer['last_name'],
														'Address1' => $customer['address_1'],
														'Address2' => $customer['address_2'],
														'City' => $customer['city'],
														'State' => $customer['state'],
														'ZipCode' => $customer['postal_code'],
														'Country' => $customer['country'],
														'Email' => $customer['email']
													)
												)
											)
										)
									)
								)	
							)	
						);
						
		// if this is outside Canada or USA, we don't need state
		if ($customer['country'] != 'US' or $customer['country'] != 'CA') {
			unset($transaction['WIRECARD_BXML']['W_REQUEST']['W_JOB'][$node]['CC_TRANSACTION']['CORPTRUSTCENTER_DATA']['ADDRESS']['State']);
		}
						
		return $transaction;
	}
}