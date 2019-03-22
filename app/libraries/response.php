<?php

class Response
{	
	function FormatResponse ($array = '', $format = 'xml')
	{
		// Load the CI object
		$CI =& get_instance();
		
		// Check to make sure an array was passed
		if (is_array($array))
		{
			// Loop through the array and add it to our response array
			foreach($array as $key => $value)
			{
				$response[$key] = $value; 
			}
			
			// check the format
			$format = (!$format) ? 'xml' : $format;
			
			if ($format == 'xml') {
				//Load the XML library
				$CI->load->library('array_to_xml');
				$response = $CI->array_to_xml->toXML($response, 'response');
			}
			elseif ($format == 'php') {
				$response = serialize($response);
			}
			elseif ($format == 'json') {
				$response = json_encode($response);
			}
			
			//Return it
			return $response;

		}
		else
		{
			return FALSE;
		}
	}
	
	// return the transaction response
	function TransactionResponse($code, $response_array = FALSE)
	{
		if (!$code) {
			$this->SystemError('Response code not passed to function.');
		}
		
		$response = array(
							'00' => 'Connection Successful.',
							'01' => 'Connection Failed',
							'1' => 'Transaction approved.',
							'2' => 'Transaction declined',
							'50' => 'Transaction refunded.',
							'51' => 'Transaction could not be refunded.',
							'100' => 'Subscription created.',
							'101' => 'Subscription cancelled.',
							'102' => 'Subscription updated.',
							'103' => 'Subscription plan updated.  Changes will appear upon next charge.',
							'104' => 'Subscription credit card updated successfully.',
							'105' => 'Subscription credit card details could not be updated.',
							'200' => 'Customer created.',
							'201' => 'Customer updated.',
							'202' => 'Customer deleted.',
							'300' => 'Client created.',
							'301' => 'Client updated.',
							'302' => 'Client suspended.',
							'303' => 'Client unsuspended,',
							'304' => 'Client deleted.',
							'400' => 'Gateway created.',
							'401' => 'Gateway updated.',
							'402' => 'Gateway deleted.',
							'403' => 'Default gateway set.',	
							'500' => 'Plan created.',
							'501' => 'Plan updated.',
							'502' => 'Plan deleted.',
							'600' => 'Email created.',
							'601' => 'Email updated.',
							'602' => 'Email deleted.'
							);
				
		$responses = array(
							'response_code' => $code,
							'response_text' => $response[$code]
							);
							
		if($response_array) {
			$response = array_merge($responses, $response_array);
		} else {
			$response = $responses;
		}
		
		return $response;
	}
	
	// return a formatted error response to the client
	function Error ($code, $additional_text = FALSE) {
		if (!$code) {
			$this->SystemError('Error code not passed to function.');
		}
		
		$errors = array(
						'1000' => 'Invalid request.',
						'1001' => 'Unable to authenticate.',
						'1002' => 'Invalid request type.',
						'1004' => 'Required fields are missing for this request',
						'1005' => 'Gateway type is required.',
						'1006' => 'Invalid format passed.  Acceptable formats: xml, php, and json.',
						'1007' => 'Invalid country.',
						'1008' => 'Invalid email address',
						'1009' => 'Unspecified error in request.',
						'1010' => 'A secure SSL connection is required.',
						'1011' => 'Invalid timezone.',
						'1012' => 'For USA and Canada addresses, a valid 2-letter state/province abbreviation is required.',
						'2000' => 'Client is not authorized to create new clients.',
						'2001' => 'Invalid External API.',
						'2002' => 'Username is already in use.',
						'2003' => 'Password must be greater than 5 characters in length.',
						'2004' => 'Invalid client ID.',
						'2005' => 'Error contacting payment gateway.',
						'2006' => 'Only administrators can create new Service Provider accounts.',
						'2007' => 'Invalid client_type.',
						'3000' => 'Invalid gateway ID for this client.',
						'3001' => 'Gateway ID is required.',
						'3002' => 'Client ID is required.',
						'4000' => 'Invalid customer ID.',
						'4001' => 'Invalid charge ID.',
						'5000' => 'A valid Recurring ID is required.',
						'5001' => 'Start date cannot be in the past.',
						'5002' => 'End date cannot be in the past',
						'5003' => 'End date must be later than start date.',
						'5004' => 'A customer ID or cardholder name must be supplied.',
						'5005' => 'Error creating customer profile.',
						'5006' => 'Error creating customer payment profile.',
						'5007' => 'Dates must be valid and in YYYY-MM-DD format.',
						'5008' => 'Invalid credit card number',
						'5009' => 'Invalid amount.',
						'5010' => 'Recurring details are required.',
						'5011' => 'Invalid interval.',
						'5012' => 'A valid description is required.',
						'5014' => 'Error cancelling subscription',
						'5015' => 'You cannot modify the plan_id via UpdateRecurring.  You must use ChangeRecurringPlan to upgrade or downgrade a recurring charge.',
						'5016' => 'Recurring billings cannot be updated for this gateway. You must either (a) cancel this existing subscription and create a new one or (b) go and update the recurring transaction at your merchant control panel.',
						'5017' => 'Gateway is disabled.',
						'5018' => 'This gateway requires customer information to be processed.  Please include a customer_id of an existing customer or a customer node with new customer information in your request.',
						'5019' => 'This gateway requires the purchasing customer\'s IP address.  Please include a customer_ip_address node in your request.',
						'5020' => 'This gateway does not allow refunds via the API.',
						'5021' => 'Only active gateways can be updated with new credit card details.',
						'5022' => 'This subscription is free - updating credit card details is futile.',
						'5023' => 'The new gateway you have chosen requires customer information but this customer record currently doesn\'t exist.  Please use UpdateCustomer to add full customer details for this user before calling UpdateCreditCard.',
						'5024' => 'Only non-external gateways allow for a paid initial charge but free recurring charge.',
						'5025' => 'Subscriptions with a paid initial charge but free recurring charge must start immediately.',
						'6000' => 'A valid Charge ID is required.',
						'6001' => 'A valid Customer ID is required.',
						'6002' => 'A valid Recurring ID is required',
						'6003' => 'Nothing to update.',
						'6005' => 'Error updating Recurring details.',
						'6006' => 'A valid Plan ID is required.',
						'7000' => 'Invalid plan type.',
						'7001' => 'Invalid Plan ID.',
						'7002' => 'Invalid Free Trial amount.',
						'7003' => 'Invalid occurrences amount.',
						'8000' => 'Invalid Email Trigger.',
						'8001' => 'A valid Email ID is required.',
						'8002' => 'Email body must be encoded.'
						);
						
		$error_array = array(
							'error' => $code,
							'error_text' => $errors[$code]
							);
							
		if ($additional_text != FALSE) {
			$error_array['error_text'] .= '  ' . $additional_text;
		}
						
		// specify return format
		if (defined("_API")) {			
			return $this->FormatResponse($error_array);
		}
		else {
			show_error($error_array['error_text']);
			die();
		}
	}
	
	// a system error, not a client error
	function SystemError ($text = '') {
		if($text == '') {
			$text = 'General Error';
		}
		log_message('error', $text);
		echo $this->Error('01','System error.');
		die();
	}
}