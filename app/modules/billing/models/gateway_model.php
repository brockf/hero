<?php
/**
* Gateway Model 
*
* Contains all the methods used to create and manage client gateways, process credit card charges, and create recurring subscriptions.
*
* @copyright Electric Function, Inc.
* @author Electric Function, Inc.
* @package Hero Framework
*
*/

class Gateway_model extends CI_Model
{	
	private $CI;
	
	function Gateway_model()
	{
		parent::__construct();
		
		$this->CI =& get_instance();
		$this->CI->load->library('billing/transaction_log');
	}
	
	/**
	* Create a new gateway instance
	*
	* Creates a new gateway instance in the gateways table.  Inserts the different gateway paramaters into the 
	* gateway_params table.  These are not declared in this documentation as they can be anything.  Returns the resulting gateway_id.
	*
	* @param string $params['gateway_type'] The type of gateway to be created (authnet, exact etc.)
	* @param int $params['accept_mc'] Whether the gateway will accept Mastercard
	* @param int $params['accept_visa'] Whether the gateway will accept Visa
	* @param int $params['accept_amex'] Whether the gateway will accept American Express
	* @param int $params['accept_discover'] Whether the gateway will accept Discover
	* @param int $params['accept_dc'] Whether the gateway will accept Diner's Club
	* @param int $params['enabled'] Whether the gateway is enabled or disabled
	* @param string $params['alias'] The gateway's alias (optional)
	* 
	* @return int New Gateway ID
	*/
	
	function NewGateway($params)
	{
		// Get the gateway type
		if(!isset($params['gateway_type'])) {
			die($this->response->Error(1005));
		}
		
		$gateway_type = $params['gateway_type'];
		
		// Validate the required fields
		$this->load->library('billing/payment/'.$gateway_type);
		$settings = $this->$gateway_type->Settings();
		$required_fields = $settings['required_fields'];
		$this->load->library('field_validation');
		$validate = $this->field_validation->ValidateRequiredGatewayFields($required_fields, $params);
		
		// Get the external API id
		$external_api_id = $this->GetExternalApiId($gateway_type);
		
		// Create the new Gateway
		
		$create_date = date('Y-m-d');
		
		$insert_data = array(
							'external_api_id' 	=> $external_api_id,
							'alias'				=> (isset($params['alias']) and !empty($params['alias'])) ? $params['alias'] : $settings['name'],
							'enabled'			=> $params['enabled'],
							'create_date'		=> $create_date
							);  
		
		$this->db->insert('gateways', $insert_data);
		
		$new_gateway_id = $this->db->insert_id();
		
		// Add the params, but not the client id or gateway type
		unset($params['authentication']);
		unset($params['gateway_type']);
		unset($params['enabled']);
		unset($params['type']);
		unset($params['alias']);
		
		$this->load->library('encrypt');
		
		foreach($params as $key => $value)
		{
			$insert_data = array(
								'gateway_id'	=> $new_gateway_id,
								'field' 			=> $key,
								'value'				=> $this->encrypt->encode($value)
								);  
		
			$this->db->insert('gateway_params', $insert_data);
		}
		
		if ($this->config->item('default_gateway') == 0) {
			$this->MakeDefaultGateway($new_gateway_id);
		}
		
		return $new_gateway_id;
	}
	
	/**
	* Process a credit card charge
	*
	* Processes a credit card CHARGE transaction using the gateway_id to use the proper client gateway.
	* Returns an array response from the appropriate payment library
	*
	* @param int $gateway_id The gateway ID to process this charge with
	* @param float $amount The amount to charge (e.g., "50.00")
	* @param array $credit_card The credit card information
	* @param int $credit_card['card_num'] The credit card number
	* @param int $credit_card['exp_month'] The credit card expiration month in 2 digit format (01 - 12)
	* @param int $credit_card['exp_year'] The credit card expiration year (YYYY)
	* @param string $credit_card['name'] The credit card cardholder name.  Required only is customer ID is not supplied.
	* @param int $credit_card['cvv'] The Card Verification Value.  Optional
	* @param int $customer_id The ID of the customer to link the charge to
	* @param array $customer An array of customer data to create a new customer with, if no customer_id
	* @param float $customer_ip The optional IP address of the customer
	* @param string $return_url The URL for external payment processors to return the user to after payment
	* @param string $cancel_url The URL to send if the user cancels an external payment
	*
	* @return mixed Array with response_code and response_text
	*/
	
	function Charge($gateway_id, $amount, $credit_card = array(), $customer_id = FALSE, $customer = array(), $customer_ip = FALSE, $return_url = FALSE, $cancel_url = FALSE)
	{
		$this->CI->load->library('field_validation');
		
		// Get the gateway info to load the proper library
		$gateway = $this->GetGatewayDetails($gateway_id);
		
		// is gateway enabled?
		if (!$gateway or $gateway['enabled'] == '0') {
			die($this->response->Error(5017));
		}
		
		// load the gateway
		$gateway_name = $gateway['name'];
		$this->load->library('billing/payment/'.$gateway_name);
		$gateway_settings = $this->$gateway_name->Settings();
		
		// validate function arguments
		if ($amount == FALSE or ((empty($credit_card) and $gateway_settings['no_credit_card'] == FALSE and $gateway_settings['external'] == FALSE) and $amount != '0.00' and $amount != '0')) {
			die($this->CI->response->Error(1004));
		}
		
		if (!empty($credit_card)) {
			// validate the Credit Card number
			$credit_card['card_num'] = trim(str_replace(array(' ','-'),'',$credit_card['card_num']));
			$credit_card['card_type'] = $this->field_validation->ValidateCreditCard($credit_card['card_num'], $gateway);
			
			if (!$credit_card['card_type']) {
				die($this->response->Error(5008));
			}
			
			if (!isset($credit_card['exp_month']) or empty($credit_card['exp_month'])) {
				die($this->response->Error(5008));
			}
			
			if (!isset($credit_card['exp_year']) or empty($credit_card['exp_year'])) {
				die($this->response->Error(5008));
			}
		}
		
		// Validate the amount
		$amount = $this->field_validation->ValidateAmount($amount);
		
		if($amount === FALSE) {
			die($this->response->Error(5009));
		}
		
		// Get the customer details if a customer id was included
		if (!empty($customer_id)) {
			$this->CI->load->model('billing/customer_model');
			$customer = $this->CI->customer_model->GetCustomer($customer_id);
			$customer['customer_id'] = $customer['id'];
			$created_customer = FALSE;
		}
		elseif (!empty($customer)) {
			$this->CI->load->model('billing/customer_model');
			// create customer record from attached information
			// by Getting the customer after it's creation, we get a nice clean ISO2 code for the country
			if (!isset($customer['first_name'])) {
				$name = explode(' ', $credit_card['name']);
				$customer['first_name'] = $name[0];
			}
			if (!isset($customer['last_name'])) {
				$name = explode(' ', $credit_card['name']);
				$customer['last_name'] = $name[count($name) - 1];
			}
			
			$customer_id = $this->CI->customer_model->NewCustomer($customer);
			$customer = $this->CI->customer_model->GetCustomer($customer_id);
			$customer['customer_id'] = $customer_id;
			unset($customer_id);
			
			$created_customer = TRUE;
		}
		else {
			// no customer_id or customer information - is this a problem?
			// we'll check if this gateway required customer information
			if ($gateway_settings['requires_customer_information'] == 1) {
				die($this->response->Error(5018));
			}
			
			$customer = array();
		}
		
		// if we have an IP, we'll populate this field
		// note, if we get an error later: the first thing we check is to see if an IP is required
		// by checking this *after* an error, we give the gateway a chance to be flexible and, if not,
		// we give the end-user the most likely error response
		if (!empty($customer_ip)) {
			// place it in $customer array
			$customer['ip_address'] = $customer_ip;
		}
		else {
			$customer['ip_address'] = '';
		}
		
		// Create a new order
		$this->CI->load->model('billing/charge_model');
		$passed_customer = (isset($customer['customer_id'])) ? $customer['customer_id'] : FALSE;
		$order_id = $this->CI->charge_model->CreateNewOrder($gateway['gateway_id'], $amount, $credit_card, 0, $passed_customer, $customer_ip);
		 
		// if amount is greater than $0, we require a gateway
		if ($amount > 0) {
			// make the charge
			$response = $this->$gateway_name->Charge($order_id, $gateway, $customer, $amount, $credit_card, $return_url, $cancel_url);	
		}
		else {
			// it's a free charge of $0, it's ok
			$response_array = array('charge_id' => $order_id);
			$response = $this->CI->response->TransactionResponse(1, $response_array);
		}
		
		if (isset($created_customer) and $created_customer == TRUE and ($response['response_code'] != 1)) {
			// the charge failed, so delete the customer we just created
			$this->CI->customer_model->DeleteCustomer($customer['customer_id']);
		}
		elseif (isset($created_customer) and $created_customer == TRUE) {
			// charge is OK and we created a new customer, we'll include it in the response
			$response['customer_id'] = $customer['customer_id'];
		}
		
		// if it was successful, send an email
		if ($response['response_code'] == 1) {
			if (!isset($response['not_completed']) or $response['not_completed'] == FALSE) {
				$this->CI->charge_model->SetStatus($order_id, 1);
			}
			else {
				unset($response['not_completed']); // no need to show this to the end user
			}
		} else {
			$this->CI->charge_model->SetStatus($order_id, 0);
			
			// did we require an IP address?
			if ($gateway_settings['requires_customer_ip'] == 1 and !$customer_ip) {
				die($this->response->Error(5019));
			}
		}
		
		return $response;
	}
		
	/**
	* Create a new recurring subscription.
	*
	* Creates a new recurring subscription and processes a charge for today.
	*
	* @param int $gateway_id The gateway ID to process this charge with
	* @param float $amount The amount to charge (e.g., "50.00")
	* @param array $credit_card The credit card information
	* @param int $credit_card['card_num'] The credit card number
	* @param int $credit_card['exp_month'] The credit card expiration month in 2 digit format (01 - 12)
	* @param int $credit_card['exp_year'] The credit card expiration year (YYYY)
	* @param string $credit_card['name'] The credit card cardholder name.  Required only is customer ID is not supplied.
	* @param int $credit_card['cvv'] The Card Verification Value.  Optional
	* @param int $customer_id The ID of the customer to link the charge to
	* @param array $customer An array of customer data to create a new customer with, if no customer_id
	* @param float $customer_ip The optional IP address of the customer
	* @param array $recur The details for a recurring charge
	* @param int $recur['plan_id'] The ID of the plan to pull recurring details from (Optional)
	* @param string $recur['start_date'] The start date of the subscription
	* @param string $recur['end_date'] The end date of the subscription
	* @param int $recur['free_trial'] The number of days to give a free trial before.  Will combine with start_date if that is also set. (Optional)
	* @param float $recur['amount'] The amount to charge every INTERVAL days.  If not there, the main $amount will be used.
	* @param int $recur['occurrences'] The total number of occurrences (Optional, if end_date doesn't exist).
	* @param string $recur['notification_url'] The URL to send POST updates to for notices re: this subscription.
	* @param string $return_url The URL for external payment processors to return the user to after payment
	* @param string $cancel_url The URL to send if the user cancels an external payment
	* @param int $renew The subscription that is being renewed, if there is one
	* @param int $coupon_id A potential coupon_id
	*
	* @return mixed Array with response_code and response_text
	*/
	
	function Recur($gateway_id, $amount = FALSE, $credit_card = array(), $customer_id = FALSE, $customer = array(), $customer_ip = FALSE, $recur = array(), $return_url = FALSE, $cancel_url = FALSE, $renew = FALSE, $coupon_id = 0)
	{		
		$this->CI->load->library('field_validation');
		
		// Get the gateway info to load the proper library
		$gateway = $this->GetGatewayDetails($gateway_id);
		
		if (!$gateway or $gateway['enabled'] == '0') {
			die($this->response->Error(5017));
		}
		
		// load the gateway
		$gateway_name = $gateway['name'];
		$this->CI->load->library('billing/payment/'.$gateway_name);
		$gateway_settings = $this->$gateway_name->Settings();
		
		$amount = (float)$amount;
		
		// validate function arguments
		if (!empty($amount) and empty($credit_card) and $gateway_settings['external'] == FALSE and $gateway_settings['no_credit_card'] == FALSE) {
			die($this->CI->response->Error(1004));
		}
		
		$this->load->library('field_validation');
		
		if (!empty($credit_card)) {
			// Validate the Credit Card number
			$credit_card['card_num'] = trim(str_replace(array(' ','-'),'',$credit_card['card_num']));
			$credit_card['card_type'] = $this->field_validation->ValidateCreditCard($credit_card['card_num'], $gateway);
			
			if (!$credit_card['card_type']) {
				die($this->response->Error(5008));
			}
			
			if (!isset($credit_card['exp_month']) or empty($credit_card['exp_month'])) {
				die($this->response->Error(5008));
			}
			
			if (!isset($credit_card['exp_year']) or empty($credit_card['exp_year'])) {
				die($this->response->Error(5008));
			}
		}
		
		// are we linking this to another sub via renewal?
		if (!empty($renew)) {
			$this->CI->load->model('billing/recurring_model');
			$renewed_subscription = $this->CI->recurring_model->GetSubscriptionDetails($renew);
			
			if (!empty($renewed_subscription)) {
				$mark_as_renewed = $renewed_subscription['subscription_id'];
				
				/**
				* this would automate the renewal subscription starting
				* after the renewed subscription
				*
				if (strtotime($renewed_subscription['next_charge_date']) > time()) {
					$recur['start_date'] = $renewed_subscription['next_charge_date'];
				}
				else {
					$recur['start_date'] = date('Y-m-d');
				}
				
				$recur['free_trial'] = 0;*/
			}
			else {
				$mark_as_renewed = FALSE;
			}
		}
		else {
			$mark_as_renewed = FALSE;
		}
		
		// Get the customer details if a customer id was included
		$this->load->model('billing/customer_model');
		
		if (!empty($customer_id)) {
			$customer = $this->CI->customer_model->GetCustomer($customer_id);
			$customer['customer_id'] = $customer['id'];
			$created_customer = FALSE;
		}
		elseif (isset($customer) and !empty($customer)) {
			// look for embedded customer information
			// by Getting the customer after it's creation, we get a nice clean ISO2 code for the country
			$customer_id = $this->CI->customer_model->NewCustomer($customer);
			$customer = $this->CI->customer_model->GetCustomer($customer_id);
			$customer['customer_id'] = $customer_id;
			unset($customer_id);
			$created_customer = TRUE;
		}
		else {
			// no customer_id or customer information - is this a problem?
			// we'll check if this gateway required customer information
			if ($gateway_settings['requires_customer_information'] == 1) {
				die($this->response->Error(5018));
			}
			
			// no customer information was passed but this gateway is OK with that, let's just get the customer first/last name
			// from the credit card name for our records
			if (!isset($credit_card['name'])) {
				die($this->response->Error(5004));
			} else {
				$name = explode(' ', $credit_card['name']);
				$customer['first_name'] = $name[0];
				$customer['last_name'] = $name[count($name) - 1];
				$customer['customer_id'] = $this->CI->customer_model->SaveNewCustomer($customer['first_name'], $customer['last_name']);
				$created_customer = TRUE;
			}
		}
		
		// if we have an IP, we'll populate this field
		// note, if we get an error later: the first thing we check is to see if an IP is required
		// by checking this *after* an error, we give the gateway a chance to be flexible and, if not,
		// we give the end-user the most likely error response
		if (!empty($customer_ip)) {
			// place it in $customer array
			$customer['ip_address'] = $customer_ip;
		}
		else {
			$customer['ip_address'] = '';
		}
		
		if (isset($recur['plan_id'])) {
			// we have a linked plan, let's load that information
			$this->CI->load->model('billing/plan_model');
			$plan_details = $this->CI->plan_model->GetPlanDetails($recur['plan_id']);
			
			$interval 			= (isset($recur['interval'])) ? $recur['interval'] : $plan_details->interval;
			$notification_url 	= (isset($recur['notification_url'])) ? $recur['notification_url'] : $plan_details->notification_url;
			$free_trial 		= (isset($recur['free_trial'])) ? $recur['free_trial'] : $plan_details->free_trial;
			$occurrences		= (isset($recur['occurrences'])) ? $recur['occurrences'] : $plan_details->occurrences;
			
			// calculate first charge amount:
			//	  1) First charge is main $amount if given
			//	  2) If no $recur['amount'], use plan amount
			//	  3) Else use $recur['amount']
			if (isset($amount)) {
				$amount = $amount;
			}
			elseif (isset($recur['amount'])) {
				$amount = $recur['amount'];
			}
			elseif (isset($plan_details->amount)) {
				$amount = $plan_details->amount;
			}
			
			$amount = $this->field_validation->ValidateAmount($amount);
			
			if ($amount === FALSE) {
				die($this->response->Error(5009));
			}
			
			// store plan ID
			$plan_id = $plan_details->plan_id;	
		} else {	
			if (!isset($recur['interval']) or !is_numeric($recur['interval'])) {
				die($this->response->Error(5011));
			}
			else {
				$interval = $recur['interval'];
			}
			
			// Check for a notification URL
			$notification_url = (isset($recur['notification_url'])) ? $recur['notification_url'] : '';
			
			// Validate the amount
			if ($this->field_validation->ValidateAmount($amount) === FALSE) {
				die($this->response->Error(5009));
			}
			
			$plan_id = 0;
			$free_trial = (isset($recur['free_trial']) and is_numeric($recur['free_trial'])) ? $recur['free_trial'] : FALSE;
		}
		
		// Validate the start date to make sure it is in the future
		if (isset($recur['start_date'])) {
			// adjust to server time
			$recur['start_date'] = server_time($recur['start_date'], 'Y-m-d', true);
		
			if (!$this->field_validation->ValidateDate($recur['start_date']) or $recur['start_date'] < date('Y-m-d')) {
				die($this->response->Error(5001));
			} else {
				$start_date = date('Y-m-d', strtotime($recur['start_date']));
			}
		} else {
			$start_date = date('Y-m-d');
		}
		
		// do we have to adjust the start_date for a free trial?
		if ($free_trial) {
			$start_date = date('Y-m-d', strtotime($start_date) + ($free_trial * 86400));
		}
		
		// get the next payment date
		if (date('Y-m-d', strtotime($start_date)) == date('Y-m-d')) {
			$next_charge_date = date('Y-m-d', strtotime($start_date) + ($interval * 86400));
		}
		else {
			$next_charge_date = date('Y-m-d', strtotime($start_date));
		}
		
		// if an end date was passed, make sure it's valid
		if (isset($recur['end_date'])) {
			// adjust to server time
			$recur['end_date'] = (!isset($end_date_set_by_server)) ? server_time($recur['end_date']) : $recur['end_date'];
			
			if (strtotime($recur['end_date']) < time()) {
				// end_date is in the past
				die($this->response->Error(5002));
			} elseif(strtotime($recur['end_date']) < strtotime($start_date)) {
				// end_date is before start_date
				die($this->response->Error(5003));
			} else {
				// date is good
				$end_date = date('Y-m-d', strtotime($recur['end_date']));
			}
		} elseif (isset($occurrences) and !empty($occurrences)) {
			// calculate end_date from # of occurrences as defined by plan
			$end_date = date('Y-m-d', strtotime($start_date) + ($interval * 86400 * $occurrences));
		} elseif (isset($recur['occurrences']) and !empty($recur['occurrences'])) {
			// calculate end_date from # of occurrences from recur node
			$end_date = date('Y-m-d', strtotime($start_date) + ($interval * 86400 * $recur['occurrences']));
		} else {
			// calculate the end_date based on the max end date setting
			$end_date = date('Y-m-d', strtotime($start_date) + ($this->config->item('max_recurring_days_from_today') * 86400));
		}
		
		if (!empty($credit_card) and isset($credit_card['exp_year']) and !empty($credit_card['exp_year'])) {
			// if the credit card expiration date is before the end date, we need to set the end date to one day before the expiration
			$check_year = ($credit_card['exp_year'] > 2000) ? $credit_card['exp_year'] : '20' . $credit_card['exp_year'];
			$expiry = mktime(0,0,0, $credit_card['exp_month'], days_in_month($credit_card['exp_month'], $credit_card['exp_year']), $check_year);
			
			$date = strtotime($next_charge_date);
			while ($date < strtotime($end_date)) {
				if ($expiry < $date) {
					$end_date = date('Y-m-d', $date);
					break;
				}
				
				$date = $date + ($interval * 86400);
			}
		}
		
		// adjust end date if it's less than next charge
		if (strtotime($end_date) <= strtotime($next_charge_date)) {
			// set end date to next charge
			$end_date = $next_charge_date;
			$total_occurrences = 1;
		}
		
		// figure the total number of occurrences
		$total_occurrences = round((strtotime($end_date) - strtotime($start_date)) / ($interval * 86400), 0);
		if ($total_occurrences < 1) {
			// the CC expiry date is only going to allow 1 charge
			$total_occurrences = 1;
		}
		
		// if they sent an $amount with their charge, this means that their first charge is different
		// so now we need to grab the true recurring amount, unless they overrode it
		if (isset($recur['amount'])) {
			$recur['amount'] = $recur['amount'];
		}
		elseif (is_object($plan_details) and isset($plan_details->amount)) {
			$recur['amount'] = $plan_details->amount;
		}
		else {
			$recur['amount'] = $amount;
		}
		
		// Save the subscription info
		$this->CI->load->model('billing/recurring_model');
		$card_last_four = (isset($credit_card['card_num'])) ? substr($credit_card['card_num'],-4,4) : '0';
		$subscription_id = $this->CI->recurring_model->SaveRecurring($gateway['gateway_id'], $customer['customer_id'], $interval, $start_date, $end_date, $next_charge_date, $total_occurrences, $notification_url, $recur['amount'], $plan_id, $card_last_four, $coupon_id);
		
		// get subscription
		$subscription = $this->CI->recurring_model->GetRecurring($subscription_id);
		// is there a charge for today?
		$charge_today = (date('Y-m-d', strtotime($subscription['date_created'])) == date('Y-m-d', strtotime($subscription['start_date']))) ? TRUE : FALSE;
		
		// set last_charge as today, if today was a charge
		if ($charge_today === TRUE) {
			$this->CI->recurring_model->SetChargeDates($subscription_id, date('Y-m-d'), $next_charge_date);
		}
		
		// if amount is greater than 0, we require a gateway to process
		if ($recur['amount'] > 0) {
			// recurring charges are not free
			$response = $this->CI->$gateway_name->Recur($gateway, $customer, $amount, $charge_today, $start_date, $end_date, $interval, $credit_card, $subscription_id, $total_occurrences, $return_url, $cancel_url);
		}
		elseif ($recur['amount'] <= 0 and $amount > 0) {
			// recurring charges are free, but there is an initial charge
			
			// can't be an external gateway
			if ($gateway_settings['external'] == TRUE) {
				die($this->response->Error(5024));
			}
			
			// must have a start date of today
			if ($charge_today !== TRUE) {
				die($this->response->Error(5025));
			}
			
			$this->CI->load->model('billing/charge_model');
			$customer['customer_id'] = (isset($customer['customer_id'])) ? $customer['customer_id'] : FALSE;
			$order_id = $this->CI->charge_model->CreateNewOrder($gateway['gateway_id'], $amount, $credit_card, $subscription_id, $customer['customer_id'], $customer_ip);
			$response = $this->CI->$gateway_name->Charge($order_id, $gateway, $customer, $amount, $credit_card, $return_url, $cancel_url);	
			
			// translate response codes into proper recurring terms
			if ($response['response_code'] == 1) {
				// set order OK
				$this->CI->charge_model->SetStatus($order_id, 1);
				
				$response['response_code'] = 100;
				$response['recurring_id'] = $subscription_id;
			}
		}
		else {
			// this is a free subscription
			if ($charge_today === TRUE) {
				// create a $0 order for today's payment
				$this->CI->load->model('billing/charge_model');
				$customer['customer_id'] = (isset($customer['customer_id'])) ? $customer['customer_id'] : FALSE;
				$order_id = $this->CI->charge_model->CreateNewOrder($gateway['gateway_id'], 0, $credit_card, $subscription_id, $customer['customer_id'], $customer_ip);
				$this->CI->charge_model->SetStatus($order_id, 1);
				$response_array = array('charge_id' => $order_id, 'recurring_id' => $subscription_id);
			}
			else {
				$response_array = array('recurring_id' => $subscription_id);
			}
			
			$response = $this->CI->response->TransactionResponse(100, $response_array);
		}
		
		if (isset($created_customer) and $created_customer == TRUE and $response['response_code'] != 100) {
			// charge was rejected, so let's delete the customer record we just created
			$this->CI->customer_model->DeleteCustomer($customer['customer_id']);
		}
		elseif (isset($created_customer) and $created_customer == TRUE) {
			$response['customer_id'] = $customer['customer_id'];
		}
		
		if ($response['response_code'] != 100) {
			// clear it out completely
			$this->CI->recurring_model->DeleteRecurring($subscription_id);
		}
		
		if ($response['response_code'] == 100) {
			// save the "mark_as_renewed" subscription as charge data so we can do this maintenance later
			// we no longer do this maintenance here because for external gateways, we need to wait
			// for the user to complete their payment
			if (!empty($mark_as_renewed)) {
				$this->CI->load->model('billing/charge_data_model');
				$this->CI->charge_data_model->Save('r' . $subscription_id, 'mark_as_renewed', $mark_as_renewed);
			}
				
			if (!isset($response['not_completed']) or $response['not_completed'] == FALSE) {
				$this->CI->recurring_model->SetActive($subscription_id);
		
				// delayed recurrings don't have a charge ID
				$response['charge_id'] = (isset($response['charge_id'])) ? $response['charge_id'] : FALSE;
				
				// hook call
				$this->app_hooks->data('subscription', $response['recurring_id']);
				
				if (!empty($mark_as_renewed)) {
					$this->app_hooks->trigger('subscription_renew', $response['recurring_id']);
				}
				else {
					$this->app_hooks->trigger('subscription_new', $response['recurring_id']);
				}
				
				// trip a recurring charge?
				if (!empty($response['charge_id'])) {
					// hook
					$this->app_hooks->data('invoice', $response['charge_id']);
					$this->app_hooks->trigger('subscription_charge', $response['charge_id'], $response['recurring_id']);
				}
			}
			else {
				unset($response['not_completed']);
			}
		}
		else {
			// did we require an IP address?
			if ($gateway_settings['requires_customer_ip'] == 1 and !$customer_ip) {
				die($this->response->Error(5019));
			}
		}
		
		$this->app_hooks->reset();
		
		return $response;
	}
	
	/**
	* Refund
	*
	* Refund a charge via the gateway
	*
	* @param $charge_id The Charge ID to refund
	*
	* @return boolean TRUE upon success
	*/
	function Refund ($charge_id)
	{
		$this->CI->transaction_log->log_event($charge_id, FALSE, 'refund_requested', FALSE, __FILE__, __LINE__);
		
		// Get the order details
		$this->CI->load->model('billing/charge_model');
		$charge = $this->CI->charge_model->GetCharge($charge_id);
		
		// does the order exist?
		if (!$charge) {
			die($this->response->Error(4001));
		}
		
		// Get the gateway info to load the proper library
		$this->CI->load->model('billing/gateway_model');
		$gateway = $this->CI->gateway_model->GetGatewayDetails($charge['gateway_id']);
		
		// does the gateway exist?
		if (!$gateway or $gateway['enabled'] == '0') {
			die($this->response->Error(5017));
		}
		
		// load the gateway
		$gateway_name = $gateway['name'];
		$this->load->library('billing/payment/'.$gateway_name);
		$gateway_settings = $this->$gateway_name->Settings();
		
		$this->CI->transaction_log->log_event($charge_id, FALSE, 'refund_gateway_loaded', array('gateway' => $gateway_name), __FILE__, __LINE__);
		
		// does the gateway allow refunds?
		if ($gateway_settings['allows_refunds'] == 0) {
			return FALSE;
		}
		
		// Get the order authorization
		$this->CI->load->model('billing/order_authorization_model');
		$authorization = $this->CI->order_authorization_model->GetAuthorization($charge['id']);
		
		// Pass to Gateway
		$response = $this->$gateway_name->Refund($gateway, $charge, $authorization);
		
		$this->CI->transaction_log->log_event($charge_id, FALSE, 'refund_response', array('response' => $response), __FILE__, __LINE__);
		
		if ($response === TRUE) {
			// update charge as being refunded
			$this->CI->charge_model->MarkRefunded($charge_id);
		}
		
		return $response; // either TRUE or FALSE
	}
	
	/**
	* Update Credit Card
	*
	* Updates the credit card on a subscription.  In actuality, it cancels the current subscription and creates a new one.
	*
	* @param int $recurring_id
	* @param array $credit_card The credit card information
	* @param int $credit_card['card_num'] The credit card number
	* @param int $credit_card['exp_month'] The credit card expiration month in 2 digit format (01 - 12)
	* @param int $credit_card['exp_year'] The credit card expiration year (YYYY)
	* @param string $credit_card['name'] The credit card cardholder name.  Required only is customer ID is not supplied.
	* @param int $credit_card['cvv'] The Card Verification Value.  Optional
	* @param int $gateway_id Set to a gateway_id to use a new gateway for this charge
	* @param int $new_plan_id Set to a new plan_id if you want to change plans
	*
	* @return array With recurring_id, response_code and response_text
	*/
	function UpdateCreditCard ($recurring_id, $credit_card = array(), $gateway_id = FALSE, $new_plan_id = FALSE) {
		$this->load->library('field_validation');
		
		$this->CI->transaction_log->log_event(FALSE, $recurring_id, 'update_requested', FALSE, __FILE__, __LINE__);
		
		// validate credit card
		if (!empty($credit_card)) {
			$credit_card['card_num'] = trim(str_replace(array(' ','-'),'',$credit_card['card_num']));
			$credit_card['card_type'] = $this->field_validation->ValidateCreditCard($credit_card['card_num']);
			
			if (!isset($credit_card['card_type']) or empty($credit_card['card_type'])) {
				die($this->response->Error(5008));
			}
			
			if (!isset($credit_card['exp_month']) or empty($credit_card['exp_month'])) {
				die($this->response->Error(5008));
			}
			
			if (!isset($credit_card['exp_year']) or empty($credit_card['exp_year'])) {
				die($this->response->Error(5008));
			}
		}
		else {
			die($this->response->Error(5008));
		}
		
		// make sure subscription is owned by client
		// get subscription information
		$this->CI->load->model('recurring_model');
		$recurring = $this->CI->recurring_model->GetRecurring($recurring_id);		
		
		$this->CI->transaction_log->log_event(FALSE, $recurring_id, 'update_recurring_retrieved', $recurring, __FILE__, __LINE__);
		
		// make sure subscription is active
		if ($recurring['status'] != 'active') {
			die($this->response->Error(5000));
		}
		
		// make sure the subscription isn't free (i.e. that it requires info)
		if ((float)$recurring['amount'] == 0) {
			die($this->response->Error(5022));
		}
		
		// get the gateway info to load the proper library
		$gateway = $this->GetGatewayDetails($recurring['gateway_id']);
		
		if (!$gateway or $gateway['enabled'] == '0') {
			die($this->response->Error(5017));
		}
		
		// load the gateway
		$gateway_name = $gateway['name'];
		$this->CI->load->library('payment/'.$gateway_name);
		$gateway_settings = $this->$gateway_name->Settings();
		
		$gateway_old = $gateway;
		
		// calculate end date from CC expiry date and setting for maximum subscription length
		// calculate the end_date based on the max end date setting
		$end_date = date('Y-m-d', time() + ($this->config->item('max_recurring_days_from_today') * 86400));
		
		// if the credit card expiration date is before the end date, we need to set the end date to one day before the expiration
		$check_year = ($credit_card['exp_year'] > 2000) ? $credit_card['exp_year'] : '20' . $credit_card['exp_year'];
		$expiry = mktime(0,0,0, $credit_card['exp_month'], days_in_month($credit_card['exp_month'], $credit_card['exp_year']), $check_year);
		
		if ($expiry < strtotime($end_date)) {
			// make the adjustment, this card will expire
			$end_date = mktime(0,0,0, $credit_card['exp_month'], (days_in_month($credit_card['exp_month'], $credit_card['exp_year']) - 1), $credit_card['exp_year']);
			$end_date = date('Y-m-d', $end_date);
		}

		// are we using a new gateway?
		if ($gateway_id != FALSE) {
			$gateway_new = $this->GetGatewayDetails($gateway_id);
		
			if (!$gateway_new or $gateway_new['enabled'] == '0') {
				die($this->response->Error(5017));
			}
			
			// load the gateway
			$gateway_name = $gateway_new['name'];
			$this->CI->load->library('payment/'.$gateway_name);
			$gateway_settings = $this->$gateway_name->Settings();
			
			// does this gateway require customer info we don't have?
			if ($gateway_settings['requires_customer_information'] == 1 and (!isset($recurring['customer']) or empty($recurring['customer']['address_1']))) {
				die($this->response->Error(5023));
			}
			
			$gateway = $gateway_new;
		}
		
		// get new sub start date from $next_charge_date
		$start_date = date('Y-m-d', strtotime($recurring['next_charge_date']));
		
		// is this for a plan?
		$plan_id = (isset($recurring['plan']['id'])) ? $recurring['plan']['id'] : FALSE;
		
		// save new subscription record
		$card_last_four = (isset($credit_card['card_num'])) ? substr($credit_card['card_num'],-4,4) : '0';
		
		// should we modify recurring info based on a new plan?  or use the old info?
		if (!empty($new_plan_id) and $new_plan_id != $recurring['plan']['id']) {
			$this->CI->load->model('plan_model');
			$plan_details = $this->CI->plan_model->GetPlanDetails($new_plan_id);
		}
		else {
			$plan_details = FALSE;
		}
		
		$recur_amount = (!empty($plan_details)) ? $plan_details->amount : $recurring['amount'];
		$recur_interval = (!empty($plan_details)) ? $plan_details->interval : $recurring['interval'];
		$recur_occurrences = (!empty($plan_details)) ? $plan_details->occurrences : $recurring['number_occurrences'];		
		$recur_notification_url = (!empty($plan_details)) ? $plan_details->notification_url : $recurring['notification_url'];
		$recur_plan_id = (!empty($plan_details)) ? $plan_details->plan_id : $plan_id;
		
		$subscription_id = $this->CI->recurring_model->SaveRecurring($gateway['gateway_id'], $recurring['customer']['id'], $recur_interval, $start_date, $end_date, $start_date, $recur_occurrences, $recur_notification_url, $recur_amount, $recur_plan_id, $card_last_four);
		
		// get subscription
		$subscription = $this->CI->recurring_model->GetRecurring($subscription_id);
		// is there a charge for today?
		$charge_today = (date('Y-m-d', strtotime($subscription['date_created'])) == date('Y-m-d', strtotime($subscription['start_date']))) ? TRUE : FALSE;
		
		// try creating a new subscription
		$response = $this->CI->$gateway_name->Recur($gateway, $recurring['customer'], $recur_amount, $charge_today, $start_date, $end_date, $recur_interval, $credit_card, $subscription_id, $recur_occurrences, FALSE, FALSE);
		
		$this->CI->transaction_log->log_event(FALSE, $subscription_id, 'update_new_subscription_response', array('response' => $response, 'updating_subscription' => $recurring_id), __FILE__, __LINE__);
		
		if ($response['response_code'] != 100) {
			// clear it out completely
			$this->CI->recurring_model->DeleteRecurring($subscription_id);
			
			// set response code to update CC error
			$response['response_code'] = '105';
			return $response;
		}
		else {		
			// set active
			$this->CI->recurring_model->SetActive($subscription_id);
			
			// mark the old subscription as updated
			// old ID, new ID
			$this->CI->recurring_model->SetUpdated($recurring_id, $subscription_id);
			
			// cancel the old subscription
			// use $gateway_old for gateway array if we need it
			
			// by setting $expiring to TRUE, we don't trigger any email triggers
			$this->CI->recurring_model->CancelRecurring($recurring_id, TRUE);
			
			// prep the response back
			$response['recurring_id'] = $subscription_id;
			
			// set response code to update CC success
			$response['response_code'] = '104';
			return $response;
		}
	}
	
	/**
	* Process a credit card recurring charge
	*
	* Processes a credit card CHARGE transaction for a recurring subscription using the gateway_id to use the proper client gateway.
	* Returns an array response from the appropriate payment library
	*
	* @param array $params The subscription array, from GetSubscription, for the recurring charge
	* 
	* @return mixed Array with response_code and response_text
	*/
	
	function ChargeRecurring($params)
	{		
		$gateway_id = $params['gateway_id'];
		
		$this->CI->transaction_log->log_event(FALSE, $params['subscription_id'], 'recurring_charge_requested', FALSE, __FILE__, __LINE__);
		
		// Get the gateway info to load the proper library
		$gateway = $this->GetGatewayDetails($gateway_id);
		
		if (!$gateway or $gateway['enabled'] == '0') {
			// we'll cancel the subscription, now
			$this->CI->load->model('billing/subscription_model');
			$this->CI->subscription_model->cancel_subscription($params['subscription_id']);
			return FALSE;
		}
		
		// get the credit card last four digits
		$params['credit_card']['card_num'] = $params['card_last_four'];
		
		// Create a new order
		$this->CI->load->model('billing/charge_model');
		$order_id = $this->CI->charge_model->CreateNewOrder($params['gateway_id'], $params['amount'], $params['credit_card'], $params['subscription_id'], $params['customer_id']);
		
		$this->CI->transaction_log->log_event(FALSE, $params['subscription_id'], 'recurring_charge_order_created', array('order_id' => $order_id), __FILE__, __LINE__);
		
		if ((float)$params['amount'] > 0) {
			// Load the proper library
			$gateway_name = $gateway['name'];
			$this->load->library('billing/payment/'.$gateway_name);
			
			// send to gateway for charging
			// gateway responds with:
			// 	success as TRUE or FALSE
			//	reason (error if success == FALSE)
			//	next_charge (if standard next_charge won't apply)
			$response = $this->$gateway_name->AutoRecurringCharge($order_id, $gateway, $params);
		}
		else {
			$response = array();
			$response['success'] = TRUE;
		}	
		
		$this->CI->transaction_log->log_event($order_id, $params['subscription_id'], 'recurring_charge_response', $response, __FILE__, __LINE__);

		$this->CI->load->model('billing/recurring_model');
		if ($response['success'] == TRUE) {
			// Save the last_charge and next_charge
			$last_charge = date('Y-m-d');
			
			if (!isset($response['next_charge'])) {
				$next_charge = $this->CI->recurring_model->GetNextChargeDate($params['subscription_id'], $params['next_charge']);
			}
			else {
				$next_charge = $response['next_charge'];
			}
			
			$this->CI->recurring_model->SetChargeDates($params['subscription_id'], $last_charge, $next_charge);
			
			$this->CI->charge_model->SetStatus($order_id, 1);
			
			$this->app_hooks->data('invoice', $order_id);
			$this->app_hooks->data('subscription', $params['subscription_id']);
			$this->app_hooks->trigger('subscription_charge', $order_id, $params['subscription_id']);
			$this->app_hooks->reset();
		} else {
			$response = FALSE;
			
			// Check the number of failures allowed
			$num_allowed = $this->config->item('recurring_charge_failures_allowed');
			$failures = $params['number_charge_failures'];
			
			$this->CI->charge_model->SetStatus($order_id, 0);
			
			$this->app_hooks->data('subscription', $params['subscription_id']);
			$this->app_hooks->trigger('subscription_renewal_failure', $params['subscription_id']);
			$this->app_hooks->reset();
			
			$failures++;
			$this->CI->recurring_model->AddFailure($params['subscription_id'], $failures);
			
			if ($failures >= $num_allowed) {	
				$this->CI->load->model('billing/subscription_model');
				$this->CI->subscription_model->cancel_subscription($params['subscription_id']);
			}
		}
		
		return $response;
	}
	
	
	/**
	* Set a default gateway.
	*
	* Sets a provided gateway_id as the default gateway for that client.
	*
	* @param int $gateway_id The gateway_id to be set as default.
	* 
	* @return bool True on success, FALSE on failure
	*/
	function MakeDefaultGateway($gateway_id)
	{		
		$this->settings_model->update_setting('default_gateway',$gateway_id);
	}
	
	/**
	* Update the Gateway
	*
	* Updates the gateway_params with supplied details
	*
	* @param int $params['gateway_id'] The gateway ID to update
	* @param int $params['accept_mc'] Whether the gateway will accept Mastercard
	* @param int $params['accept_visa'] Whether the gateway will accept Visa
	* @param int $params['accept_amex'] Whether the gateway will accept American Express
	* @param int $params['accept_discover'] Whether the gateway will accept Discover
	* @param int $params['accept_dc'] Whether the gateway will accept Diner's Club
	* @param int $params['enabled'] Whether the gateway is enabled or disabled
	* 
	* @return bool TRUE on success, FALSE on fail.
	*/
	
	function UpdateGateway($params)
	{
		// get gateway details
		$gateway = $this->GetGatewayDetails($params['gateway_id']);
		
		if(!$gateway) {
			die($this->response->Error(3000));
		}
		
		// Validate the required fields
		$this->load->library('billing/payment/'.$gateway['name'], $gateway['name']);
		$settings = $this->$gateway['name']->Settings();
		$required_fields = $settings['required_fields'];
		$this->load->library('field_validation');
		$validate = $this->field_validation->ValidateRequiredGatewayFields($required_fields, $params);
				
		$this->load->library('encrypt');
		
		// manually handle "enabled" and "alias"
		if (isset($params['enabled']) and ($params['enabled'] == '0' or $params['enabled'] == '1')) {
			$update_data['enabled'] = $params['enabled'];
			$this->db->where('gateway_id', $params['gateway_id']);
			$this->db->update('gateways', $update_data);
			unset($update_data);
		}
		
		if (isset($params['alias']) and !empty($params['alias'])) {
			$update_data['alias'] = $params['alias'];
			$this->db->where('gateway_id', $params['gateway_id']);
			$this->db->update('gateways', $update_data);
			unset($update_data);
		}
				
		$i = 0;
		foreach($required_fields as $field)
		{
			if (isset($params[$field]) and $params[$field] != '') {
				$update_data['value'] = $this->encrypt->encode($params[$field]);
				$this->db->where('gateway_id', $params['gateway_id']);
				$this->db->where('field', $field);
				$this->db->update('gateway_params', $update_data);
				$i++;
			}
		}
		
		if ($i === 0) {
			die($this->response->Error(6003));
		}
		
		return TRUE;
	}
	
	/**
	* Delete a gateway
	*
	* Marks a gateway as deleted and removes the authentication information from the gateway_params table.
	* Does not actually deleted the gateway, but sets deleted to 1 in the gateways table.
	*
	* @param int $gateway_id The gateway_id to be set deleted.
	* 
	* @return bool TRUE on success
	*/
	
	function DeleteGateway($gateway_id, $completely = FALSE)
	{
		// get gateway details
		$gateway = $this->GetGatewayDetails($gateway_id);
		
		if(!$gateway) {
			die($this->response->Error(3000));
		}
		
		// cancel all subscriptions related to it
		$this->CI->load->model('billing/recurring_model');
		$subscriptions = $this->CI->recurring_model->GetAllSubscriptionsByGatewayID($gateway_id);
		if (is_array($subscriptions)) {
			foreach ($subscriptions as $subscription) {
				$this->CI->recurring_model->CancelRecurring($subscription['subscription_id']);
			}
		}
		
		// Mark as deleted
		if ($completely == FALSE) {
			$update_data['deleted'] = 1;
			$this->db->where('gateway_id', $gateway_id);
			$this->db->update('gateways', $update_data);
		}
		else {
			// remove from database completely
			$this->db->delete('gateways',array('gateway_id' => $gateway_id));
		}
		
		// Delete the client gateway params
		$this->db->where('gateway_id', $gateway_id);
		$this->db->delete('gateway_params');
		
		return TRUE;
	}
	
	/**
	* Get the External API ID
	*
	* Gets the External API ID from the external_apis table based on the gateway type ('authnet', 'exact' etc.)
	*
	* @param string $gateway_name The name to match with External API ID
	* 
	* @return int External API ID
	*/
	
	// Get the gateway id
	function GetExternalApiId($gateway_name = FALSE)
	{
		if($gateway_name) {
			$this->db->where('name', $gateway_name);
			$query = $this->db->get('external_apis');
			if($query->num_rows > 0) {
				return $query->row()->external_api_id;
			} else {
				die($this->response->Error(2001));
			}
			
		}
	}
	
	/**
	* Get list of current gateways
	*
	* Returns details of all gateways for a client.
	*
	* @param int $params['deleted'] Whether or not the gateway is deleted.  Possible values are 1 for deleted and 0 for active
	* @param int $params['id'] The email ID.  GetGateway could also be used. Optional.
	* @param int $params['offset']
	* @param int $params['limit'] The number of records to return. Optional.
	* 
	* @return mixed Array containg all gateways meeting criteria
	*/
	
	function GetGateways ($params = array())
	{		
		if(isset($params['deleted']) and $params['deleted'] == '1') {
			$this->db->where('gateways.deleted', '1');
		}
		else {
			$this->db->where('gateways.deleted', '0');
		}
		
		if (isset($params['offset'])) {
			$offset = $params['offset'];
		}
		else {
			$offset = 0;
		}
		
		if(isset($params['limit'])) {
			$this->db->limit($params['limit'], $offset);
		}
		
		$this->db->join('external_apis', 'external_apis.external_api_id = gateways.external_api_id', 'left');
		
		$this->db->select('gateways.*');
		$this->db->select('external_apis.*');
		
		$query = $this->db->get('gateways');
		$data = array();
		if($query->num_rows() > 0) {
			foreach($query->result_array() as $row)
			{
				$array = array(
								'id' => $row['gateway_id'],
								'gateway' => (!empty($row['alias'])) ? $row['alias'] : $row['display_name'],
								'name' => $row['name'],
								'enabled' => ($row['enabled'] == '1') ? TRUE : FALSE,
								'date_created' => $row['create_date']
								);
								
				$data[] = $array;
			}
			
		} else {
			return FALSE;
		}
		
		return $data;
	}
	
	/**
	* Get the gateway details.
	*
	* Returns an array containg all the details for the Client Gateway
	*
	* @param int $gateway_id The gateway_id
	* 
	* @return array All gateway details
	*/
	
	function GetGatewayDetails($gateway_id = FALSE, $deleted_ok = FALSE)
	{
		// If they have not passed a gateway ID, we will choose the first one created.
		if ($gateway_id) {
			$this->db->where('gateways.gateway_id', $gateway_id);
		} elseif (!empty($client->default_gateway_id)) {
			$this->db->where('gateways.gateway_id', $client->default_gateway_id);
		} else {
			$this->db->order_by('create_date', 'ASC');
		}
		
		$this->db->join('external_apis', 'gateways.external_api_id = external_apis.external_api_id', 'inner');
		if ($deleted_ok == FALSE) {
			$this->db->where('deleted', 0);
		}
		$this->db->limit(1);
		$query = $this->db->get('gateways');
		if($query->num_rows > 0) {
			
			$row = $query->row();
			$data = array();
			$data['url_live'] = $row->prod_url;
			$data['url_test'] = $row->test_url;
			$data['url_dev'] = $row->dev_url;
			$data['arb_url_live'] = $row->arb_prod_url;
			$data['arb_url_test'] = $row->arb_test_url;
			$data['arb_url_dev'] = $row->arb_dev_url;
			$data['name'] = $row->name;
			$data['alias'] = $row->alias;
			$data['enabled'] = $row->enabled;
			$data['gateway_id'] = $row->gateway_id;
			
			// Get the params
			$this->load->library('encrypt');
			$this->db->where('gateway_id', $row->gateway_id);
			$query = $this->db->get('gateway_params');
			if($query->num_rows() > 0) {
				foreach($query->result() as $row) {
					$data[$row->field] = $this->encrypt->decode($row->value);
				}
			}
			
			return $data;
		} else {
			die($this->response->Error(3000));
		}		
	}
	
	/**
	* Get available Gateway External API's
	*
	* Loads a list of all possible gateway types, as well as their required fields
	* 
	* @return array|bool Returns an array containing all of the fields required for that gateway type or FALSE upon failure
	*/
	function GetExternalAPIs()
	{
		$this->db->order_by('display_name');
		
		$query = $this->db->get('external_apis');
		if($query->num_rows() > 0) {
			$gateways = array();
			
			foreach ($query->result_array() as $row) {
				$this->load->library('billing/payment/' . strtolower($row['name']),$row['name']);
				
				$settings = $this->$row['name']->Settings();
			
				$gateways[] = $settings;
			}	
			
			return $gateways;
		} else {
			return FALSE;
		}
	}
}