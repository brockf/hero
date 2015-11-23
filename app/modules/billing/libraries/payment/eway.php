<?php

/**
 * eWAY processing gateway.
 *
 * NOTE: This gateway does not use the eWay rebill API. Instead, it uses the Token Payments
 * API since we do not save credit card information and cannot provide some of the required
 * info for the Rebill API.
 *
 * Prod URL: http://www.eway.com.au/gateway/managedpayment
 * Test URL: https://www.eway.com.au/gateway/ManagedPaymentService/test/managedcreditcardpayment.asmx
 * Dev  URL: https://www.eway.com.au/gateway/ManagedPaymentService/test/managedcreditcardpayment.asmx
 * Rebill Prod URL: http://www.eway.com.au/gateway/managedpayment 
 * Rebill Test URL: https://www.eway.com.au/gateway/ManagedPaymentService/test/managedcreditcardpayment.asmx
 * Rebill Dev  URL: https://www.eway.com.au/gateway/ManagedPaymentService/test/managedcreditcardpayment.asmx
 *
 * Token Payment Test Information: 
 *		Test Customer ID		- 87654321
 *		Test Username 			- test@eway.com.au
 *		Test Password			- test123
 *		Test CreditCard #		- 4444333322221111
 *		Test ManagedCustomerID	- 987654321000
 *		Test CCV				- 123
 * 
 * @package 	OpenGateway
 * @author		Dave Ryan
 * @modified	Lonnie Ezell
 */

class eway
{
	var $settings;
	
	/**
	 * if true, will echo out debug strings to verify
	 * that things are working. 
	 */
	private $debug = false;
	
	//---------------------------------------------------------------
	
	function eway() {
		$this->settings = $this->Settings();
	}

	//---------------------------------------------------------------

	function Settings()
	{
		$settings = array();
		
		$settings['name'] = 'eWAY';
		$settings['class_name'] = 'eway';
		$settings['external'] = FALSE;
		$settings['no_credit_card'] = FALSE;
		$settings['description'] = 'eWAY is the premier gateway solution in Australia.';
		$settings['is_preferred'] = 1;
		$settings['setup_fee'] = '$0';
		$settings['monthly_fee'] = '$29';
		$settings['transaction_fee'] = '$0.50';
		$settings['purchase_link'] = 'https://www.eway.com.au/join/secure/signup.aspx';
		$settings['allows_updates'] = 1;
		$settings['allows_refunds'] = 0;
		$settings['requires_customer_information'] = 1;
		$settings['requires_customer_ip'] = 0;
		$settings['required_fields'] = array(
										'enabled',
										'mode', 
										'customer_id',
										'accept_visa',
										'accept_mc',
										'accept_discover',
										'accept_dc',
										'accept_amex',
										'username',
										'password'
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
																		'dev' => 'Development Server'
																		)
														),
										'customer_id' => array(
														'text' => 'Login ID',
														'type' => 'text'
														),
										
										'username' => array(
														'text' => 'Rebill Username',
														'type' => 'text'
														),
										
										'password' => array(
														'text' => 'Rebill Password',
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
	
	//---------------------------------------------------------------
	
	/**
	 * Tests that the user information provided is a valid set of rules.
	 * We do this by creating a new test customer on the remote server.
	 *
	 * @param	array	$gateway	- the gateway object.
	 * @return	bool	true if client info appears correct.
	 */
	function TestConnection($gateway) 
	{	
		$customer = array(
			'Title'		=> 'Mr.',
	    	'FirstName' => 'Joe',
	    	'LastName'	=> 'Bloggs',
	    	'Address'	=> 'Blogg enterprises',
	    	'Suburb'	=> 'Capital City',
	    	'State'		=> 'act',
	    	'Company'	=> 'Bloggs',
	    	'PostCode'	=> '2111',
	    	'Country'	=> 'au',
	    	'Email'		=> 'test@eway.com.au',
	    	'Fax'		=> '0298989898',
	    	'Phone'		=> '0297979797',
	    	'Mobile'	=> '',
	    	'CustomerRef'=> 'Ref123',
	    	'JobDesc'	=> '',
	    	'Comments'	=> 'Please ship ASAP',
	    	'URL'		=> 'http://www.test.com.au',
	    	'CCNumber'	=> '4444333322221111',
	    	'CCNameOnCard'	=> 'Test Account',
	    	'CCExpiryMonth'	=> '12',
	    	'CCExpiryYear'	=> date('y')
	    );
		
		$response = $this->processSoap($gateway, $customer, 'CreateCustomer');
		
		if (isset($response['CREATECUSTOMERRESULT']))
		{
			return TRUE;
		}
		
		return FALSE;
	}
	
	//---------------------------------------------------------------
	
	//---------------------------------------------------------------
	// !CUSTOMER FUNCTIONS
	//---------------------------------------------------------------
		
	function createProfile($gateway, $customer, $credit_card, $subscription_id, $amount, $order_id)
	{
		$CI =& get_instance();
	
	    $xml = array(
	    	'Title'		=> isset($customer['title']) && !empty($customer['title']) ? $customer['title'] : 'Mr.',
	    	'FirstName' => $customer['first_name'],
	    	'LastName'	=> $customer['last_name'],
	    	'Address'	=> $customer['address_1'],
	    	'Suburb'	=> $customer['city'],
	    	'State'		=> $customer['state'],
	    	'Company'	=> $customer['company'],
	    	'PostCode'	=> $customer['postal_code'],
	    	'Country'	=> strtolower($customer['country']),
	    	'Email'		=> $customer['email'],
	    	'Fax'		=> 'N/A',
	    	'Phone'		=> 'N/A',
	    	'Mobile'	=> 'N/A',
	    	'CustomerRef'=> $order_id,
	    	'JobDesc'	=> 'N/A',
	    	'Comments'	=> 'N/A',
	    	'URL'		=> '',
	    	'CCNumber'	=> isset($credit_card['card_num']) ? $credit_card['card_num'] : '',
	    	'CCNameOnCard'	=> isset($credit_card['name']) ? $credit_card['name'] : '',
	    	'CCExpiryMonth'	=> isset($credit_card['exp_month']) ? $credit_card['exp_month'] : '',
	    	'CCExpiryYear'	=> isset($credit_card['exp_year']) ? substr($credit_card['exp_year'], -2, 2) : ''
	    );
    
		$response = $this->processSoap($gateway, $xml, 'CreateCustomer');
		
		if(isset($response['CREATECUSTOMERRESULT']) && is_numeric($response['CREATECUSTOMERRESULT']))
		{	
			$response['success'] = true;
			$response['client_id'] = $response['CREATECUSTOMERRESULT'];
			// Save the Auth information
			$CI->load->model('billing/recurring_model');
			$CI->recurring_model->SaveApiCustomerReference($subscription_id, $response['CREATECUSTOMERRESULT']);
			// Client successfully created at eWay. Now we ned to save the info here. 
			return $response;
		}
		else
		{
			$response['success'] = false;
			$response['reason'] = 'Could not create customer at eWay.';
			return $response;
		}
	}
	
	//---------------------------------------------------------------
	
	//---------------------------------------------------------------
	// !EVENT FUNCTIONS
	//---------------------------------------------------------------
		
	function Charge($order_id, $gateway, $customer, $amount, $credit_card)
	{ 
		$CI =& get_instance();
	
		// The Charge function is the only one to use the eWay Hosted Payments solution,
		// so the url's are not stored in the database. Instead, they are provided here.
		$post_url = $gateway['mode'] == 'prod' ? 'https://www.eway.com.au/gateway/xmlpayment.asp' : 'https://www.eway.com.au/gateway/xmltest/testpage.asp';
		
		$post['ewaygateway']['ewayCustomerID'] = $gateway['customer_id'];
		$post['ewaygateway']['ewayTotalAmount'] = number_format($amount,2,'','');
		
		$post['ewaygateway']['ewayCardNumber'] = $credit_card['card_num'];
		$post['ewaygateway']['ewayCardExpiryMonth'] = $credit_card['exp_month'];
		$post['ewaygateway']['ewayCardExpiryYear'] = substr($credit_card['exp_year'],-2,2);
		$post['ewaygateway']['ewayTrxnNumber'] = '';
		$post['ewaygateway']['ewayOption1'] = '';
		$post['ewaygateway']['ewayOption2'] = '';
		$post['ewaygateway']['ewayOption3'] = '';
		$post['ewaygateway']['ewayCustomerInvoiceDescription'] = '';
		$post['ewaygateway']['ewayCustomerInvoiceRef'] = $order_id;
		
		$post['ewaygateway']['ewayCardHoldersName'] = $customer['first_name'].' '.$customer['last_name'];
		$post['ewaygateway']['ewayCustomerFirstName'] = $customer['first_name'];
		$post['ewaygateway']['ewayCustomerLastName'] = $customer['last_name'];
		$post['ewaygateway']['ewayCustomerAddress'] = $customer['address_1'];
		if (isset($customer['address_2']) and !empty($customer['address_2'])) {
			$post['ewaygateway']['ewayCustomerAddress'] .= ' - '.$customer['address_2'];
		}
		$post['ewaygateway']['ewayCustomerPostcode'] = $customer['postal_code'];
		$post['ewaygateway']['ewayCustomerEmail'] = $customer['email'];
		
		if(isset($credit_card['cvv'])) {
			$post['ewaygateway']['ewayCVN'] = $credit_card['cvv'];
		}
		
		$CI->load->library('array_to_xml');
		$xml = $CI->array_to_xml->toXml($post);
		
		$xml = str_replace('<ResultSet>','', $xml);
		$xml = str_replace('</ResultSet>','', $xml);
		
		$response = $this->Process($post_url,$xml);
		
		if($response['ewayTrxnStatus'] == 'True')
		{ 
			$CI->load->model('billing/order_authorization_model');
			$CI->order_authorization_model->SaveAuthorization($order_id, $response['ewayTrxnNumber'], $response['ewayAuthCode']);
			$CI->charge_model->SetStatus($order_id, 1);
			
			$response_array = array('charge_id' => $order_id);
			$response = $CI->response->TransactionResponse(1, $response_array);
		}
		else
		{
			$CI->load->model('billing/charge_model');
			$CI->charge_model->SetStatus($order_id, 0);
			
			$response_array = array('reason' => $response['ewayTrxnError']);
			$response = $CI->response->TransactionResponse(2, $response_array);
		}
		
		return $response;
	}
	
	//---------------------------------------------------------------
	
	/**
	 *	Recur - called when an initial Recur charge comes through to
	 *	to create a subscription.
	 *
	 * 	Since we're using the TokenAPI and not an actual recurring api
	 *  this method's primary purpose is to simply setup a user and charge
	 *	them through the Token aPI.
	 */
		 
	function Recur ($gateway, $customer, $amount, $charge_today, $start_date, $end_date, $interval, $credit_card, $subscription_id, $total_occurrences = FALSE)
	{		
		$CI =& get_instance();
	
		// Create an order for today's payment
		$CI->load->model('billing/charge_model');
		$customer['customer_id'] = (isset($customer['customer_id'])) ? $customer['customer_id'] : FALSE;
		$order_id = $CI->charge_model->CreateNewOrder($gateway['gateway_id'], $amount, $credit_card, $subscription_id, $customer['customer_id'], $customer['ip_address']);
		
		// Create the recurring seed
		$response = $this->CreateProfile($gateway, $customer, $credit_card, $subscription_id, $amount, $order_id);
		
		// Process today's payment
		if ($charge_today === TRUE) {
			if ($gateway['mode'] != 'live') $response['client_id'] = '9876543211000';
		
			$response = $this->ChargeRecurring($gateway, $order_id, $response['client_id'], $amount);
		
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
		
		return $response;
	}
	
	//---------------------------------------------------------------
	
	function CancelRecurring($subscription)
	{ 
		// Recurring not stored at eWay, so do nothing here.
		return TRUE;	
	}
	
	//---------------------------------------------------------------
		
	function AutoRecurringCharge ($order_id, $gateway, $params) {
		return $this->ChargeRecurring($gateway, $order_id, $params['api_customer_reference'], $params['amount']);
	}
	
	//-----------------------------------------------------
	
	
	/**
	 *	Handles paying the recurring charge. 
	 *
	 *	NOTE: eWay does NOT provide a method in their API to trigger this, 
	 *	it appears that eWay handles this automatically, so we are using
	 * 	the TokenAPI.
	 */	 
	function ChargeRecurring ($gateway, $order_id, $customer_id, $amount) {
		$CI =& get_instance();
		
		if ($gateway['mode'] != 'live') $customer_id = '9876543211000';
	
		$xml = array(
			'managedCustomerID'	=> $customer_id,
			'amount'			=> number_format($amount, 2, '', ''),
			'invoiceReference'	=> $order_id,
			'invoiceDescription'=> 'Recurring Payment.'
		);
		
		$response = $this->processSoap($gateway, $xml, 'ProcessPayment');
		
		if (isset($response['EWAYTRXNSTATUS']) && $response['EWAYTRXNSTATUS'] == 'True')
		{
			$response['success']			= TRUE;
			$response['transaction_num']	= $response['EWAYTRXNNUMBER'];
			$response['auth_code']			= $response['EWAYAUTHCODE'];
			
			// Save the Auth information
			$CI->load->model('billing/order_authorization_model');
			$CI->order_authorization_model->SaveAuthorization($order_id, $response['EWAYTRXNNUMBER'], $response['EWAYAUTHCODE']);
		} else 
		{
			$response['success'] 	= FALSE;
			$response['reason']		= $response['EWAYTRXNERROR'];
		}
		
		return $response;
	}
	
	//---------------------------------------------------------------
		
	function UpdateRecurring($gateway, $subscription, $customer, $params)
	{
		// Recurring info not stored at eWay, so do nothing here...
		return TRUE;
	}
	
	//---------------------------------------------------------------
	
	//---------------------------------------------------------------
	// !PROCESSORS
	//---------------------------------------------------------------
	
	function Process($url, $xml)
	{			
		$ch = curl_init($url); // initiate curl object
		curl_setopt($ch, CURLOPT_HEADER, 0); // set to 0 to eliminate header info from response
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); // Returns response data instead of TRUE(1)
		curl_setopt($ch, CURLOPT_POSTFIELDS, $xml); // use HTTP POST to send form data
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-type: text/xml;charset=UTF-8'));
		
		// We need to make curl recognize the CA certificated so it can get by...
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);	// Verify it belongs to the server.
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);	// Check common exists and matches the server host name
		
		$post_response = curl_exec($ch); // execute curl post and store results in $post_response
		
		if (curl_errno($ch) == CURLE_OK)
		{
			$response_xml = @simplexml_load_string($post_response);
			$CI =& get_instance();
			$CI->load->library('array_to_xml');
			$response = $CI->array_to_xml->toArray($response_xml);
			
			return $response;
		}
		
		return FALSE;
		
	}
	
	//---------------------------------------------------------------
	
	/**
	 * ProcessSoap()
	 * 
	 * Uses the SOAP protocol to talk with eway. Sends a secure header
	 * 
	 */
	function processSoap($gateway, $xml, $action)
	{		
		$CI =& get_instance();
		$CI->load->library('array_to_xml');	

		$url = $this->GetAPIUrl($gateway, 'rebill');
		
		$header = '<?xml version="1.0" encoding="utf-8"?>
<soap:Envelope xmlns:soap="http://www.w3.org/2003/05/soap-envelope" xmlns:man="https://www.eway.com.au/gateway/managedpayment">
	<soap:Header>
		<man:eWAYHeader>
			<man:eWAYCustomerID>'. $gateway['customer_id'] .'</man:eWAYCustomerID>
			<man:Username>'. $gateway['username'] .'</man:Username>
			<man:Password>'. $gateway['password'] .'</man:Password>
		</man:eWAYHeader>
	</soap:Header>';
	
		$body = '<soap:Body>'. $this->to_xml($xml, $action, 'man') . '</soap:Body></soap:Envelope>';
		
		$request = trim($header) . trim($body);
			
		// Send the request via CURL
		$ch = curl_init($url); // initiate curl object
		curl_setopt($ch, CURLOPT_HEADER, 0); // set to 0 to eliminate header info from response
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); // Returns response data instead of TRUE(1)
		curl_setopt($ch, CURLOPT_POSTFIELDS, $request); // use HTTP POST to send form data
		curl_setopt ($ch, CURLOPT_HTTPHEADER, array('SOAPAction: https://www.eway.com.au/gateway/managedpayment/'.$action, 'Content-type: text/xml')); 
		
		// We need to make curl ignore the CA certificate so it can get by...
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);	// Verify it belongs to the server.
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);	// Check common exists and matches the server host name
		
		$post_response = curl_exec($ch); // execute curl post and store results in $post_response
		
		if (curl_errno($ch) == CURLE_OK)
		{
			// No CURL Errors, so translate the returned XML into a usable object to return
			$p = xml_parser_create();	// Create a parser
			xml_parse_into_struct($p, trim($post_response), $response, $index);	// Parse into a $response array
			
			// Any errors? 
			if (xml_get_error_code($p) != XML_ERROR_NONE)
			{
				xml_parser_free($p);	// Free the parser
				return false;
			}
			xml_parser_free($p);	// Free the parser
			
			// Combine into an organized array...
			$response = $this->format_xml_array($response, $index);
			
			return $response;
		} else 
		{
			echo 'Curl Error Number: '. curl_errno($ch) .', Error: '. curl_error($ch);
		}
		
		return FALSE;
		
	}
	
	//---------------------------------------------------------------
	
	//---------------------------------------------------------------
	
	//---------------------------------------------------------------
	// !UTILITY FUNCTIONS
	//---------------------------------------------------------------
	
	/**
	 * Returns the proper url for the remote gateway.
	 *
	 * Note that $mode param defaults to false, which will
	 * return the token payments url. If $mode is 'arb', 
	 * then it will return the rebill url.
	 */
	function GetAPIUrl ($gateway, $mode = FALSE) {
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
					//$post_url = $gateway['arb_url_live'];
					$post_url = 'https://www.eway.com.au/gateway/ManagedPaymentService/managedCreditCardPayment.asmx';
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
	
	//---------------------------------------------------------------
	
	public function aus_date($time=null, $format='d/m/Y') 
	{
		// Use today's date as the timestamp if none given.
		if (!is_numeric($time))
		{
			$time = time();
		}
	
		if (!function_exists('local_to_gmt'))
		{
			$this->load->helper('date');
		}
		
		// Standardize time to GMT
		$time = local_to_gmt($time);
		
		// Convert to Australian time
		$time = gmt_to_local($time, 'UP10');
		
		return date($format, $time);
	}
	
	//---------------------------------------------------------------
	
	public function to_xml($array=array(), $action='', $ns='man')
	{
		if (!count($array) || empty($action))
		{
			return false;
		}
		
		$xml = "<$ns:$action>\n";
		
		foreach ($array as $key => $value)
		{
			$xml .= "\t<$ns:$key>$value</$ns:$key>\n";
		}
		
		$xml .= "</$ns:$action>\n";
		
		return $xml;
	}
	
	//---------------------------------------------------------------
	
	private function format_xml_array($orig, $index)
	{
		$response = array();
		
		foreach ($index as $key => $values)
		{
			$response[$key] = isset($orig[$values[0]]['value']) ? $orig[$values[0]]['value'] : '';
		}
		
		return $response;
	}
	
	//---------------------------------------------------------------
	
}