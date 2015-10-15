<?php

class exact
{
	var $settings;

	// If set to TRUE, it will log data sent to and received from PayPal in /writeable/gateway_log.txt.
	private $debug	= false;

	//--------------------------------------------------------------------

	function exact() {
		$this->settings = $this->Settings();
	}

	//--------------------------------------------------------------------

	function Settings()
	{
		$settings = array();

		$settings['name'] = 'E-xact';
		$settings['class_name'] = 'exact';
		$settings['external'] = FALSE;
		$settings['no_credit_card'] = FALSE;
		$settings['description'] = 'E-xact from VersaPay is the perfect gateway for both Canadian and American merchants.';
		$settings['is_preferred'] = 1;
		$settings['setup_fee'] = '$149.99';
		$settings['monthly_fee'] = '$29.99';
		$settings['transaction_fee'] = '$0.25';
		$settings['purchase_link'] = 'http://ecommerce.versapay.com/';
		$settings['allows_updates'] = 1;
		$settings['allows_refunds'] = 1;
		$settings['requires_customer_information'] = 0;
		$settings['requires_customer_ip'] = 0;
		$settings['required_fields'] = array('enabled',
											 'terminal_id',
											 'password',
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
										'terminal_id' => array(
														'text' => 'Gateway ID',
														'type' => 'text'
														),
										'password' => array(
														'text' => 'Password',
														'type' => 'password'
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

	//--------------------------------------------------------------------

	function TestConnection($gateway)
	{
		$post_url = $gateway['url_live'];

		$trxnProperties = array(
					'ExactID'			=> $gateway['terminal_id'],
			  		'Password'			=> $gateway['password'],
					'Transaction_Type'  => '00',
				 	'Card_Number' 		=> '4222222222222222',
					'Expiry_Date'		=> '1099',
					'CVD_Presence_Ind' 	=> '9',
					'DollarAmount' 		=> 1
		  		);

		$trxnProperties = $this->CompleteArray($trxnProperties);

		$trxnResult = $this->Process($trxnProperties, $post_url);

		if (isset($trxnResult->ExactID)) {
			return TRUE;
		} else {
			return FALSE;
		}
	}

	//--------------------------------------------------------------------

	function Charge($order_id, $gateway, $customer, $amount, $credit_card)
	{
		$CI =& get_instance();

		$post_url = $gateway['url_live'];

		$transaction = array(
					'User_Name'			=> '',
					'ExactID'			=> $gateway['terminal_id'],
			  		'Password'			=> $gateway['password'],
					'Transaction_Type'  => '00',
				 	'Card_Number' 		=> $credit_card['card_num'],
					'Expiry_Date'		=> str_pad($credit_card['exp_month'], 2, "0", STR_PAD_LEFT) . substr($credit_card['exp_year'],-2,2),
					'CVD_Presence_Ind' 	=> '1',
					'Customer_Ref' 		=> $order_id,
					'DollarAmount' 		=> $amount
		  		);

		if (isset($credit_card['cvv'])) {
			$transaction['VerificationStr2'] = $credit_card['cvv'];
		}

		if (isset($customer['customer_id'])) {
			// build customer's name from customer array
			$transaction['CardHoldersName'] = $customer['first_name'].' '.$customer['last_name'];
		}
		else {
			// automatically get customer's name from credit card
			$name = explode(' ', $credit_card['name']);
			$transaction['CardHoldersName'] = $name[0] . ' ' . $name[1];
		}

		if (isset($customer['ip_address']) and !empty($customer['ip_address'])) {
			$transaction['Client_IP'] = $customer['ip_address'];
		}

		$transaction = $this->CompleteArray($transaction);

		$transaction_result = $this->Process($transaction, $post_url);

		if($transaction_result->EXact_Resp_Code == '00' and $transaction_result->Transaction_Approved === TRUE){
			$CI->load->model('billing/order_authorization_model');
			$CI->order_authorization_model->SaveAuthorization($order_id, $transaction_result->Transaction_Tag, $transaction_result->Authorization_Num);
			$response_array = array('charge_id' => $order_id);
			$response = $CI->response->TransactionResponse(1, $response_array);
		} else {
			$response_array = array('reason' => $transaction_result->EXact_Message);
			$response = $CI->response->TransactionResponse(2, $response_array);
		}

		return $response;
	}

	//--------------------------------------------------------------------

	function Recur ($gateway, $customer, $amount, $charge_today, $start_date, $end_date, $interval, $credit_card, $subscription_id, $total_occurrences = FALSE)
	{
		$CI =& get_instance();

		// Create an order for today's (potential) payment
		$CI->load->model('billing/charge_model');
		$customer['customer_id'] = (isset($customer['customer_id'])) ? $customer['customer_id'] : FALSE;
		$order_id = $CI->charge_model->CreateNewOrder($gateway['gateway_id'], $amount, $credit_card, $subscription_id, $customer['customer_id'], $customer['ip_address']);

		// Create the recurring seed
		$response = $this->CreateProfile($gateway, $customer, $credit_card, $subscription_id, $amount, $order_id);

		if ($response['success'] == TRUE) {
			// Process today's payment
			if ($charge_today === TRUE) {
				$response = $this->ChargeRecurring($gateway, $order_id, $response['transaction_tag'], $response['auth_num'], $amount);

				if($response['success'] == TRUE){
					$CI->charge_model->SetStatus($order_id, 1);
					$response_array = array('charge_id' => $order_id, 'recurring_id' => $subscription_id);
					$response = $CI->response->TransactionResponse(100, $response_array);
				} else {
					// Make the subscription inactive
					$CI->recurring_model->MakeInactive($subscription_id);
					$CI->charge_model->SetStatus($order_id, 0);

					$response_array = array('reason' => $response['reason']);
					$response = $CI->response->TransactionResponse(2, $response_array);
				}
			} else {
				$response = $CI->response->TransactionResponse(100, array('recurring_id' => $subscription_id));
			}
		}
		else {
			// Make the subscription inactive
			$CI->recurring_model->MakeInactive($subscription_id);
			$CI->charge_model->SetStatus($order_id, 0);

			$response_array = array('reason' => $response['reason']);
			$response = $CI->response->TransactionResponse(2, $response_array);
		}

		return $response;
	}

	//--------------------------------------------------------------------

	function Refund ($gateway, $charge, $authorization)
	{
		$CI =& get_instance();

		$post_url = $gateway['url_live'];

		$trxnProperties = array(
						'ExactID'			=> $gateway['terminal_id'],
				  		'Password'			=> $gateway['password'],
						'Transaction_Type'  => '34',
					 	'Transaction_Tag'	=> $authorization->tran_id,
						'Authorization_Num'	=> $authorization->authorization_code,
						'Customer_Ref' 		=> $charge['id'],
						'DollarAmount' 		=> $charge['amount']
			        );

		$trxnProperties = $this->CompleteArray($trxnProperties);

		$post_response = $this->Process($trxnProperties, $post_url);

		if ($post_response->EXact_Resp_Code == '00') {
			return TRUE;
		} else {
			return FALSE;
		}
	}

	//--------------------------------------------------------------------

	function CreateProfile($gateway, $customer, $credit_card, $subscription_id, $amount, $order_id)
	{
		$CI =& get_instance();

		$post_url = $gateway['url_live'];

		// Create the recurring seed

		$transaction = array(
		'ExactID'			=> $gateway['terminal_id'],
  		'Password'			=> $gateway['password'],
		'Transaction_Type'  => '40',
	 	'Card_Number' 		=> $credit_card['card_num'],
		'Expiry_Date'		=> str_pad($credit_card['exp_month'], 2, "0", STR_PAD_LEFT) . substr($credit_card['exp_year'],-2,2),
		'CVD_Presence_Ind' 	=> '1',
		'Customer_Ref' 		=> $order_id,
		'DollarAmount' 		=> ((float)$amount == 0) ? '1' : $amount
	    );

		if (isset($credit_card['cvv'])) {
			$transaction['VerificationStr2'] = $credit_card['cvv'];
		}

		if (isset($customer['customer_id'])) {
			$transaction['CardHoldersName'] = $customer['first_name'] . ' ' . $customer['last_name'];
		} else {
			$name = explode(' ', $credit_card['card_name']);
			$transaction['CardHoldersName'] = $name[0] . ' ' . $name[1];
		}

		if (isset($customer['ip_address']) and !empty($customer['ip_address'])) {
			$transaction['Client_IP'] = $customer['ip_address'];
		}

		$transaction = $this->CompleteArray($transaction);

		$post_response = $this->Process($transaction, $post_url, $order_id);

		if($post_response->EXact_Resp_Code == '00' and $post_response->Transaction_Approved === TRUE) {
			$response['success'] = TRUE;
			// Save the Auth information
			$CI->load->model('billing/recurring_model');
			$CI->recurring_model->SaveApiCustomerReference($subscription_id, $post_response->Transaction_Tag);
			$CI->recurring_model->SaveApiAuthNumber($subscription_id, $post_response->Authorization_Num);
			$response['transaction_tag'] = $post_response->Transaction_Tag;
			$response['auth_num'] = $post_response->Authorization_Num;
		} else {
			$response['success'] = FALSE;
			$response['reason'] = $post_response->EXact_Message;
		}

		return $response;
	}

	//--------------------------------------------------------------------

	function AutoRecurringCharge ($order_id, $gateway, $params) {
		return $this->ChargeRecurring($gateway, $order_id, $params['api_customer_reference'], $params['api_auth_number'], $params['amount']);
	}

	//--------------------------------------------------------------------

	function ChargeRecurring($gateway, $order_id, $transaction_tag, $auth_num, $amount)
	{
		$CI =& get_instance();

		$post_url = $gateway['url_live'];

		// Create the charge

		$trxnProperties = array(
		'ExactID'			=> $gateway['terminal_id'],
  		'Password'			=> $gateway['password'],
		'Transaction_Type'  => '30',
	 	'Transaction_Tag'	=> $transaction_tag,
		'Authorization_Num'	=> $auth_num,
		'Customer_Ref' 		=> $order_id,
		'DollarAmount' 		=> $amount
        );

		$trxnProperties = $this->CompleteArray($trxnProperties);

		$post_response = $this->Process($trxnProperties, $post_url, $order_id);

		$response = array();

		if ($post_response->EXact_Resp_Code == '00' and $post_response->Transaction_Approved === TRUE) {
			$response['success'] = TRUE;
			// Save the Auth information
			$CI->load->model('billing/order_authorization_model');
			$CI->order_authorization_model->SaveAuthorization($order_id, $post_response->Transaction_Tag, $post_response->Authorization_Num);
		} else {
			$response['success'] = FALSE;
			$response['reason'] = $post_response->EXact_Message;
		}

		return $response;
	}

	//--------------------------------------------------------------------

	function CancelRecurring($subscription)
	{
		return TRUE;
	}

	//--------------------------------------------------------------------

	function UpdateRecurring()
	{
		return TRUE;
	}

	//--------------------------------------------------------------------

	function Process($trxnProperties, $post_url)
	{
		$trxn = array("Transaction"=>$trxnProperties);
//die(var_dump($trxn));
		$client = new SoapClient($post_url);

		$trxnResult = $client->__soapCall('SendAndCommit', $trxn);

		return $trxnResult;
	}

	//--------------------------------------------------------------------

	function CompleteArray($array = array())
	{
		$complete_if_blank = array(
								"Ecommerce_Flag",
								"XID",
								"ExactID",
								"CAVV",
								"Password",
								"CAVV_Algorithm",
								"Transaction_Type",
								"Reference_No",
								"Customer_Ref",
								"Reference_3",
								"Client_IP",
								"Client_Email",
								"Language",
								"Card_Number",
								"Expiry_Date",
								"CardHoldersName",
								"Track1",
								"Track2",
								"Authorization_Num",
								"Transaction_Tag",
								"DollarAmount",
								"VerificationStr1",
								"VerificationStr2",
								"CVD_Presence_Ind",
								"Secure_AuthRequired",
								"Secure_AuthResult",

								// Level 2 fields
								"ZipCode",
								"Tax1Amount",
								"Tax1Number",
								"Tax2Amount",
								"Tax2Number",

								"SurchargeAmount",	//Used for debit transactions only
								"PAN",
								"User_Name"
							);

		while (list(,$v) = each($complete_if_blank)) {
			if (!key_exists($v, $array)) {
				$array[$v] = '';
			}
		}

		return $array;
	}

	//--------------------------------------------------------------------

	public function get_url($gateway)
	{
		if (strpos($gateway['terminal_id'], 'AD') === 0)
		{
			// It needs a different endpoint.
			//return 'https://api-demo.e-xact.com/transaction/wsdl';
		}
//return "https://secure2.e-xact.com/vplug-in/transaction/rpc-enc/service.asmx";
		return $gateway['url_live'];
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

	//--------------------------------------------------------------------
}