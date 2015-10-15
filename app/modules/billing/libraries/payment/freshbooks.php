<?php

class freshbooks
{
	var $settings;
	
	function freshbooks () {
		$this->settings = $this->Settings();
	}

	function Settings()
	{
		$settings = array();
		
		$settings['name'] = 'Online Invoicing with FreshBooks';
		$settings['class_name'] = 'freshbooks';
		$settings['external'] = FALSE;
		$settings['no_credit_card'] = TRUE;
		$settings['description'] = 'FreshBooks is a popular, versatile online invoicing application.  Instead of collecting payments online, this gateway will create an invoice in your FreshBooks account for the customer.  This way, you can accept payments offline (or via other methods) and track your income and accounts receivable with FreshBooks.';
		$settings['is_preferred'] = 0;
		$settings['setup_fee'] = 'n/a';
		$settings['monthly_fee'] = 'n/a';
		$settings['transaction_fee'] = 'n/a';
		$settings['purchase_link'] = 'https://electricfunction.freshbooks.com/refer/www';
		$settings['allows_updates'] = 1;
		$settings['allows_refunds'] = 1;
		$settings['requires_customer_information'] = 1;
		$settings['requires_customer_ip'] = 0;
		$settings['required_fields'] = array(
										'enabled',
										'api_url',
										'auth_token',
										'item_name',
										'item_description'
										);
										
		$settings['field_details'] = array(
										'enabled' => array(
														'text' => 'Enable this gateway?',
														'type' => 'radio',
														'options' => array(
																		'1' => 'Enabled',
																		'0' => 'Disabled')
														),
										'api_url' => array(
														'text' => 'API URL',
														'type' => 'text'
														),
										'auth_token' => array(
														'text' => 'Authentication Token',
														'type' => 'text'
														),
										'item_name' => array(
														'text' => 'Invoice Item Name',
														'type' => 'text'
														),
										'item_description' => array(
														'text' => 'Invoice Item Description',
														'type' => 'text'
														)
											);
		
		return $settings;
	}
	
	function SendRequest ($api_url, $auth_token, $xml = '') {
		$curl = curl_init($api_url);
		curl_setopt($curl, CURLOPT_HEADER, 0);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($curl, CURLOPT_USERPWD, $auth_token);
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt($curl, CURLOPT_POST, 1);
		curl_setopt($curl, CURLOPT_POSTFIELDS, $xml);
		$result = curl_exec($curl);
		curl_close($curl);
		
		if (strpos($result,'<?xml') !== FALSE) {
			$CI =& get_instance();
			$CI->load->library('array_to_xml');
			
			$result = $CI->array_to_xml->toArray($result);
			
			// sometimes this array is empty but it doesn't mean
			// that the request didn't work
			if (empty($result)) {
				return TRUE;
			}
			
			return $result;
		}
		else {
			return FALSE;
		}
	}
	
	function TestConnection ($gateway) 
	{
		$xml = '<?xml version="1.0" encoding="utf-8"?>
				<request method="invoice.list">       
				</request>';
				
		$response = $this->SendRequest($gateway['api_url'], $gateway['auth_token'], $xml);
		
		if (!empty($response)) {
			return TRUE;
		}		
		else {
			return FALSE;
		}
	}
	
	function SaveClientID ($customer_id, $client_id) {
		$CI =& get_instance();
		$CI->load->model('billing/charge_data_model');
		$CI->charge_data_model->Delete('fb_customer_' . $customer_id);
		$CI->charge_data_model->Save('fb_customer_' . $customer_id, 'client_id', $client_id);
	}
	
	function GetCreateClient ($gateway, $customer) {
		$CI =& get_instance();
		
		// we may get arrays from GetCustomer which have the "id" key, not "customer_id"
		$customer['customer_id'] = (isset($customer['id'])) ? $customer['id'] : $customer['customer_id'];
	
		// does this client already exist in FreshBooks?
		$CI->load->model('billing/charge_data_model');
		$data = $CI->charge_data_model->Get('fb_customer_' . $customer['customer_id']);
		
		// if not, let's create it
		if (empty($data) or !isset($data['client_id'])) {
			// create the client
			$company = (!empty($customer['company'])) ? $customer['company'] : $customer['first_name'] . ' ' . $customer['last_name'];
			$phone = (!empty($customer['phone'])) ? '<work_phone>' . $customer['phone'] . '</work_phone>' : '';
			
			if (!empty($customer['address_1'])) {
				$address = '<p_street1>' . $customer['address_1'] . '</p_street1>
						    <p_street2>' . $customer['address_2'] . '</p_street2>
						    <p_city>' . $customer['city'] . ' </p_city>
						    <p_state>' . $customer['state'] . '</p_state>
						    <p_country>' . $customer['country'] . '</p_country>
						    <p_code>' . $customer['postal_code'] . '</p_code>';
			}
			else {
				$address = '';
			}
			
			$xml = '<?xml version="1.0" encoding="utf-8"?>
					<request method="client.create">
					  <client>
					    <first_name>' . $customer['first_name'] . '</first_name>
					    <last_name>' . $customer['last_name'] . '</last_name>
					    <organization>' . $company . '</organization>
					    <email>' . $customer['email'] . '</email>
					    ' . $phone . '
						' . $address .'
					  </client>
					</request>';
					
			$response = $this->SendRequest($gateway['api_url'], $gateway['auth_token'], $xml);
			
			if (empty($response) or !isset($response['client_id'])) {
				return FALSE;
			}
			
			$client_id = $response['client_id'];
			
			// save this ID
			$this->SaveClientID($customer['customer_id'], $client_id);
			
			$created_client = TRUE;
		}
		else {
			$client_id = $data['client_id'];
			$created_client = FALSE;
		}
		
		// check to see if client is up to date...
		if ($created_client == FALSE) {
			$xml = '<?xml version="1.0" encoding="utf-8"?>  
					<request method="client.get">  
					  <client_id>' . $client_id . '</client_id>  
					</request> ';
					
			$response = $this->SendRequest($gateway['api_url'], $gateway['auth_token'], $xml);

			if (empty($response) or !isset($response['client'])) {
				// we have likely deleted the client, so let's create a new one
				// create the client
				$company = (!empty($customer['company'])) ? $customer['company'] : $customer['first_name'] . ' ' . $customer['last_name'];
				$phone = (!empty($customer['phone'])) ? '<work_phone>' . $customer['phone'] . '</work_phone>' : '';
				
				if (!empty($customer['address_1'])) {
					$address = '<p_street1>' . $customer['address_1'] . '</p_street1>
							    <p_street2>' . $customer['address_2'] . '</p_street2>
							    <p_city>' . $customer['city'] . ' </p_city>
							    <p_state>' . $customer['state'] . '</p_state>
							    <p_country>' . $customer['country'] . '</p_country>
							    <p_code>' . $customer['postal_code'] . '</p_code>';
				}
				else {
					$address = '';
				}
				
				$xml = '<?xml version="1.0" encoding="utf-8"?>
						<request method="client.create">
						  <client>
						    <first_name>' . $customer['first_name'] . '</first_name>
						    <last_name>' . $customer['last_name'] . '</last_name>
						    <organization>' . $company . '</organization>
						    <email>' . $customer['email'] . '</email>
						    ' . $phone . '
							' . $address .'
						  </client>
						</request>';
						
				$response = $this->SendRequest($gateway['api_url'], $gateway['auth_token'], $xml);
				
				if (empty($response) or !isset($response['client_id'])) {
					return FALSE;
				}
				
				$client_id = $response['client_id'];
				
				// save this ID
				$this->SaveClientID($customer['customer_id'], $client_id);
			}
			else {
				// compare to see if we need an update
				$update_client = FALSE;
				if ($response['client']['first_name'] != $customer['first_name']) {
					$update_client = TRUE;
				}
				if ($response['client']['last_name'] != $customer['last_name']) {
					$update_client = TRUE;
				}
				if ($response['client']['email'] != $customer['email']) {
					$update_client = TRUE;
				}
				if ($response['client']['p_street1'] != $customer['address_1']) {
					$update_client = TRUE;
				}
				if ($response['client']['p_city'] != $customer['city']) {
					$update_client = TRUE;
				}
				if ($response['client']['p_code'] != $customer['postal_code']) {
					$update_client = TRUE;
				}
				
				if ($update_client == TRUE) {
					// update the client
					$company = (!empty($customer['company'])) ? $customer['company'] : $customer['first_name'] . ' ' . $customer['last_name'];
					$phone = (!empty($customer['phone'])) ? '<work_phone>' . $customer['phone'] . '</work_phone>' : '';
					
					if (!empty($customer['address_1'])) {
						$address = '<p_street1>' . $customer['address_1'] . '</p_street1>
								    <p_street2>' . $customer['address_2'] . '</p_street2>
								    <p_city>' . $customer['city'] . ' </p_city>
								    <p_state>' . $customer['state'] . '</p_state>
								    <p_country>' . $customer['country'] . '</p_country>
								    <p_code>' . $customer['postal_code'] . '</p_code>';
					}
					else {
						$address = '';
					}
					
					$xml = '<?xml version="1.0" encoding="utf-8"?>
							<request method="client.update">
							  <client>
							  	<client_id>' . $client_id . '</client_id>
							    <first_name>' . $customer['first_name'] . '</first_name>
							    <last_name>' . $customer['last_name'] . '</last_name>
							    <organization>' . $company . '</organization>
							    <email>' . $customer['email'] . '</email>
							    ' . $phone . '
								' . $address .'
							  </client>
							</request>';
							
					$response = $this->SendRequest($gateway['api_url'], $gateway['auth_token'], $xml);
					
					if (empty($response)) {
						return FALSE;
					}
				}
			}
		}
		
		// and return the client ID...
		return $client_id;
	}
	
	function Charge ($order_id, $gateway, $customer, $amount, $credit_card, $return_url, $cancel_url)
	{	
		$CI =& get_instance();
		
		$fb_client_id = $this->GetCreateClient($gateway, $customer);
		if (empty($fb_client_id)) {
			return $CI->response->TransactionResponse(2, array('reason' => 'Unable to create or retrieve the client ID properly.'));
		}
		
		// create the invoice
		$xml = '<request method="invoice.create">
				  <invoice>
				    <client_id>' . $fb_client_id . '</client_id>  
				    <status>sent</status>    
				    <return_uri>' . $return_url . '</return_uri>
				
				    <lines>
				      <line>
				        <name>' . $gateway['item_name'] . '</name>
				        <description>' . $gateway['item_description'] . ' (Charge #' . $order_id . ')</description>
				        <unit_cost>' . $amount . '</unit_cost>
				        <quantity>1</quantity>
				      </line>
				    </lines>
				  </invoice>
				</request>';
				
		$response = $this->SendRequest($gateway['api_url'], $gateway['auth_token'], $xml);
		
		if (empty($response) or !isset($response['invoice_id'])) {
			return $CI->response->TransactionResponse(2, array('reason' => 'Unable to create invoice.'));
		}
		
		$response_array = array('charge_id' => $order_id);
		$response = $CI->response->TransactionResponse(1, $response_array);
		
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

			$response = $this->Charge($order_id, $gateway, $customer, $amount, array(), $return_url, $cancel_url);
			
			if (!empty($response) and $response['response_code'] != '2') {
				$CI->charge_model->SetStatus($order_id, 1);
				$response_array = array('charge_id' => $order_id, 'recurring_id' => $subscription_id);
				$response = $CI->response->TransactionResponse(100, $response_array);
			}
			else {
				$response = $CI->response->TransactionResponse(2, array('reason' => 'Unable to create initial invoice.'));
			}
		}
		else {
			// we'll create the FreshBooks client, but that's it
			$fb_client_id = $this->GetCreateClient($gateway, $customer);
			
			if (!empty($fb_client_id)) {
				$response = $CI->response->TransactionResponse(100, array('recurring_id' => $subscription_id));
			}
			else {
				$response = $CI->response->TransactionResponse(2, array('reason' => 'Unable to create the FreshBooks client.'));
			}
		}
		
		return $response;
	}
	
	function Refund ($gateway, $charge, $authorization)
	{	
		$xml = '<?xml version="1.0" encoding="utf-8"?>
				<request method="invoice.delete">
				  <invoice_id>344</invoice_id>
				</request>';
				
		$response = $this->SendRequest($gateway['api_url'], $gateway['auth_token'], $xml);
		
		if (!empty($response)) {
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
		$CI =& get_instance();
		
		// get customer array
		$CI->load->model('billing/customer_model');
		$customer = $CI->customer_model->GetCustomer($params['customer_id']);
		
		$fb_client_id = $this->GetCreateClient($gateway, $customer);
		if (empty($fb_client_id)) {
			return array('success' => FALSE, 'reason' => 'Unable to retrieve FreshBooks client ID.');
		}
		
		// create the invoice
		$xml = '<request method="invoice.create">
				  <invoice>
				    <client_id>' . $fb_client_id . '</client_id>  
				    <status>sent</status>    
				
				    <lines>
				      <line>
				        <name>' . $gateway['item_name'] . '</name>
				        <description>' . $gateway['item_description'] . ' (Recurring #' . $params['subscription_id'] . ' &amp; Charge #' . $order_id . ')</description>
				        <unit_cost>' . $params['amount'] . '</unit_cost>
				        <quantity>1</quantity>
				      </line>
				    </lines>
				  </invoice>
				</request>';
				
		$response = $this->SendRequest($gateway['api_url'], $gateway['auth_token'], $xml);
		
		if (empty($response) or !isset($response['invoice_id'])) {
			return array('success' => FALSE, 'reason' => 'Unable to create recurring FreshBooks invoice.');
		}
		else {
			$response = array();
			$response['success'] = TRUE;
	
			return $response;
		}
	}
	
	function UpdateRecurring()
	{
		return TRUE;
	}
}