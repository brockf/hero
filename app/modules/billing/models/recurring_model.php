<?php

/**
* Recurring Model 
*
* Contains all the methods used to create, update, and search recurring charges.
*
* @copyright Electric Function, Inc.
* @author Electric Function, Inc.
* @package Hero Framework

*/

class Recurring_model extends CI_Model
{
	private $CI;
	
	function Recurring_model()
	{
		parent::__construct();
		
		$this->CI =& get_instance();
		$this->CI->load->library('billing/transaction_log');
	}
	
	/**
	* Create a new recurring subscription.
	*
	* Creates a new recurring subscription and returns the subscription ID.
	*
	* @param int $gateway_id The gateway ID
 	* @param int $customer_id The customer ID
	* @param date $start_date The date the subscription should begin
	* @param date $end_date The date the subscription should end
	* @param date $next_charge_data The date that the subscription should next be charged
	* @param int $total_occurrences The total number of charges for this subscription.
	* @param string $notification_url The notification URL
	* @param int $amount The amount to be charged
	* @param int $plan_id A link to a plan.  Optional.
	* @param int $card_last_four Last 4 digits of CC (default: 0)
	* @param int $coupon_id
	* 
	* @return int The new subscription ID
	*/
	
	function SaveRecurring($gateway_id, $customer_id, $interval, $start_date, $end_date, $next_charge_date, $total_occurrences, $notification_url, $amount, $plan_id = 0, $card_last_four = 0, $coupon_id = 0)
	{
		$timestamp = date('Y-m-d H:i:s');
		$insert_data = array(
							'gateway_id' 		=> $gateway_id,
							'customer_id' 		=> $customer_id,
							'plan_id'			=> $plan_id,
							'notification_url'	=> stripslashes($notification_url),
							'charge_interval' 	=> $interval,
							'start_date' 		=> $start_date,
							'end_date'			=> $end_date,
							'next_charge'		=> $next_charge_date,
							'number_occurrences'=> $total_occurrences,
							'amount'			=> $amount,
							'card_last_four'	=> (!empty($card_last_four)) ? $card_last_four : '0',
							'active'			=> '0',
							'renewed'			=> '0',
							'updated'			=> '0',
							'expiry_processed'	=> '0',
							'cancel_date'		=> '0000-00-00 00:00:00',
							'timestamp'			=> $timestamp,
							'coupon_id'			=> $coupon_id
			  				);  					  				
			  				
		$this->db->insert('subscriptions', $insert_data);
		
		$subscription_id = $this->db->insert_id();
		
		$this->CI->transaction_log->log_event(FALSE, $subscription_id, 'subscription_created', $insert_data, __FILE__, __LINE__);
		
		return $subscription_id;
	}
	
	/**
	* Set Active
	*
	* Makes a new subscription active
	*
	* @param $subscription_id The recurring ID
	*
	* @return boolean TRUE
	*/
	function SetActive ($recurring_id) {
		$this->db->update('subscriptions',array('active' => '1'),array('subscription_id' => $recurring_id));
		
		$this->CI->transaction_log->log_event(FALSE, $recurring_id, 'set_active', FALSE, __FILE__, __LINE__);
		
		return TRUE;
	}
	
	/**
	* Set Renewed
	*
	* Sets the "renew" field for a subscription as the subscription ID of the sub that renewed it.
	*
	* @param int $old_subscription_id
	* @param int $new_subscription_id
	*
	* @return boolean TRUE
	*/
	function SetRenew ($old_subscription_id, $new_subscription_id) {
		$this->db->update('subscriptions',array('renewed' => $new_subscription_id),array('subscription_id' => $old_subscription_id));
		
		$this->CI->transaction_log->log_event(FALSE, $old_subscription_id, 'mark_as_renewed', array('renewing_subscription' => $new_subscription_id), __FILE__, __LINE__);
		
		return TRUE;
	}
	
	/**
	* Set Updated
	*
	* Sets the "updated" field to the new subscription ID, if the Credit Card was updated
	*
	* @param int $old_subscription_id
	* @param int $new_subscription_id
	*
	* @return boolean TRUE
	*/
	function SetUpdated ($old_subscription_id, $new_subscription_id) {
		$this->db->update('subscriptions',array('updated' => $new_subscription_id),array('subscription_id' => $old_subscription_id));
		
		$this->CI->transaction_log->log_event(FALSE, $old_subscription_id, 'mark_as_updated', array('renewing_subscription' => $new_subscription_id), __FILE__, __LINE__);

		return TRUE;
	}
	
	/**
	* Add a customer profile ID.
	*
	* For API's that require a customer profile
	*
	* @param int $subscription_id The subscription_id
	* @param int $api_customer_reference The customer profile id
	*
	* @return bool TRUE upon success.
	*/
	function SaveApiCustomerReference($subscription_id, $api_customer_reference)
	{
		$update_data = array('api_customer_reference' => $api_customer_reference);
		
		$this->db->where('subscription_id', $subscription_id);
		$this->db->update('subscriptions', $update_data);
		
		$this->CI->transaction_log->log_event(FALSE, $subscription_id, 'customer_reference_saved', $update_data, __FILE__, __LINE__);
		
		return TRUE;
	}
	
	/**
	* Add a customer payment ID.
	*
	* For API's that require a customer payment profile
	*
	* @param int $subscription_id The subscription_id
	* @param int $api_payment_reference The customer payment id
	*
	* @return bool TRUE upon success.
	*/
	function SaveApiPaymentReference($subscription_id, $api_payment_reference)
	{
		$update_data = array('api_payment_reference' => $api_payment_reference);
		
		$this->db->where('subscription_id', $subscription_id);
		$this->db->update('subscriptions', $update_data);
		
		$this->CI->transaction_log->log_event(FALSE, $subscription_id, 'payment_reference_saved', $update_data, __FILE__, __LINE__);
		
		return TRUE;
	}
	
	/**
	* Add a Auth number.
	*
	* For API's that require an Auth code be used for future charges
	*
	* @param int $subscription_id The subscription_id
	* @param int $api_auth_number The API auth code.
	*/
	function SaveApiAuthNumber($subscription_id, $api_auth_number)
	{
		$update_data = array('api_auth_number' => $api_auth_number);
		
		$this->db->where('subscription_id', $subscription_id);
		$this->db->update('subscriptions', $update_data);
		
		$this->CI->transaction_log->log_event(FALSE, $subscription_id, 'auth_number_saved', $update_data, __FILE__, __LINE__);

		return TRUE;
	}
	
	/**
	* Make a subscription inactive
	*
	* Makes a subscription inactive
	*
	* @param int $subscription_id The subscription_id
	*
	* @return bool TRUE upon success, FALSE if already inactive.
	*/
	function MakeInactive($subscription_id)
	{
		$subscription = $this->GetSubscriptionDetails($subscription_id);
		
		$update_data = array('active' => '0', 'cancel_date' => date('Y-m-d H:i:s'));
		
		// set end date
		if ($subscription['next_charge'] != '0000-00-00' and (strtotime($subscription['next_charge']) + (60*60*24)) > time() and strtotime($subscription['next_charge']) < strtotime($subscription['end_date'])) {
			// there's a next charge date which won't be renewed, so we'll end it then
			// we must also account for their signup time
			//$time_created = date('H:i:s',strtotime($subscription['date_created']));
			//$end_date = $subscription['next_charge_date'] . ' ' . $time_created;
			$end_date = $subscription['next_charge'];
		}
		elseif ($subscription['end_date'] != '0000-00-00') {
			// there is a set end_date
			$end_date = $subscription['end_date'];
		}
		else {
			// for some reason, neither a next_charge_date or an end_date exist
			// let's end this now
			//$end_date = date('Y-m-d H:i:s');
			$end_date = date('Y-m-d');
		}
		
		$update_data['end_date'] = $end_date;
		
		$this->db->where('subscription_id', $subscription_id);
		$this->db->update('subscriptions', $update_data);
		
		$this->CI->transaction_log->log_event(FALSE, $subscription_id, 'make_inactive', $update_data, __FILE__, __LINE__);
		
		return TRUE;
	}
	
	/**
	* Delete Recurring Completely (Charge Failed)
	*
	* @param int $subscription_id
	*/
	
	function DeleteRecurring ($subscription_id) {
		$this->db->where('subscription_id',$subscription_id);
		$this->db->delete('subscriptions');
		
		$this->CI->transaction_log->log_event(FALSE, $subscription_id, 'delete_subscription_completely', FALSE, __FILE__, __LINE__);
		
		return TRUE;
	}
	
	/**
	* Add a failure to a subscription
	*
	*
	* @param int $subscription_id The subscription_id
	*
	* @return bool TRUE upon success.
	*/
	function AddFailure($subscription_id, $failures = 0)
	{
		$update_data = array('number_charge_failures' => $failures);
		
		$this->db->where('subscription_id', $subscription_id);
		$this->db->update('subscriptions', $update_data);
		
		$this->CI->transaction_log->log_event(FALSE, $subscription_id, 'add_failure', $update_data, __FILE__, __LINE__);
		
		return TRUE;
	}
	
	/**
	* Get subscription details
	*
	* Returns an array of details about the subscription.
	*
	* @param int $subscription_id The subscription_id
	* 
	* @return array Subscription details
	*/
	function GetSubscriptionDetails($subscription_id)
	{
		$this->db->join('gateways', 'gateways.gateway_id = subscriptions.gateway_id', 'inner');
		$this->db->join('external_apis', 'gateways.external_api_id = external_apis.external_api_id', 'inner');
		$this->db->where('subscription_id', $subscription_id);
		$query = $this->db->get('subscriptions');
		if($query->num_rows() > 0) {
			return $query->row_array();
		} else {
			die($this->response->Error(5000));
		}
	}
	
	/**
	* Retrieve details for a specific subscription
	*
	* Returns an array of data for the requested subscription.
	*
	* @param int $recurring_id The ID # of the recurring transaction to pull;
	* 
	* @return array|bool Details for a specific subscription or FALSE upon failure
	*/
	
	function GetRecurring ($recurring_id)
	{
		$params = array('id' => $recurring_id);
		
		$data = $this->GetRecurrings($params, TRUE);
		
		if (!empty($data)) {
			return $data[0];
		}
		else {
			return FALSE;
		}
	}
	
	/**
	* Search subscriptions.
	*
	* Returns an array of results based on submitted search criteria.  All fields are optional.
	*
	* @param int $params['gateway_id'] The gateway ID used for the order. Optional.
	* @param date $params['created_after'] Only subscriptions created after or on this date will be returned. Optional.
	* @param date $params['created_before'] Only subscriptions created before or on this date will be returned. Optional.
	* @param int $params['customer_id'] The customer id associated with the subscription. Optional.
	* @param string $params['customer_internal_id'] The customer's internal id associated with the subscription. Optional.
	* @param int $params['amount'] Only subscriptions for this amount will be returned. Optional.
	* @param boolean $params['active'] Returns only active subscriptions. Optional.
	* @param int $params['plan_id'] Only return subscriptions link to this plan ID
	* @param int $params['offset'] Offsets the database query.
	* @param int $params['limit'] Limits the number of results returned. Optional.
	* @param string $params['sort'] Variable used to sort the results.  Possible values are date, customer_first_name, customer_last_name, amount. Optional
	* @param string $params['sort_dir'] Used when a sort param is supplied.  Possible values are asc and desc. Optional.
	* 
	* @return mixed Array containing results
	*/
	function GetRecurrings ($params, $any_status = FALSE)
	{	
		// Check which search paramaters are set
		
		if(isset($params['id'])) {
			$this->db->where('subscription_id', $params['id']);
		}
		
		if(isset($params['gateway_id'])) {
			$this->db->where('gateway_id', $params['gateway_id']);
		}
		
		if(isset($params['created_after'])) {
			$start_date = date('Y-m-d H:i:s', strtotime($params['created_after']));
			$this->db->where('timestamp >=', $start_date);
		}
		
		if(isset($params['created_before'])) {
			$end_date = date('Y-m-d H:i:s', strtotime($params['created_before']));
			$this->db->where('timestamp <=', $end_date);
		}
		
		if (isset($params['customer_id'])) {
			$this->db->where('subscriptions.customer_id', $params['customer_id']);
		}
		
		if(isset($params['customer_last_name'])) {
			$this->db->where('customers.last_name', $params['customer_last_name']);
		}
		
		if(isset($params['customer_internal_id'])) {
			$this->db->where('customers.internal_id', $params['customer_internal_id']);
		}
		
		if(isset($params['amount'])) {
			$this->db->where('amount', $params['amount']);
		}
		
		if (isset($params['active'])) {
			if($params['active'] == '1' or $params['active'] == '0') {
				$this->db->where('subscriptions.active', $params['active']);
			}
		}
		elseif ($any_status == FALSE) {
			$this->db->where('subscriptions.active','1');
		}
		
		if(isset($params['plan_id'])) {
			$this->db->where('subscriptions.plan_id', $params['plan_id']);
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
		
		if(isset($params['sort_dir']) and ($params['sort_dir'] == 'asc' or $params['sort_dir'] == 'desc' )) {
			$sort_dir = $params['sort_dir'];
		}
		else {
			$sort_dir = 'desc';
		}
		
		$params['sort'] = isset($params['sort']) ? $params['sort'] : '';
		
		switch ($params['sort'])
		{
			case 'date':
				$sort = 'subscription_id';
				break;
			case 'customer_first_name':
				$sort = 'first_name';
				break;
			case 'customer_last_name':
				$sort = 'last_name';
				break;	
			case 'amount':
				$sort = 'amount';
				break;
			default:
				$sort = 'subscription_id';
				break;	
		}
		
		$this->db->order_by($sort, $sort_dir);	
		
		$this->db->join('customers', 'customers.customer_id = subscriptions.customer_id', 'left');
		$this->db->join('countries', 'countries.country_id = customers.country', 'left');
		$this->db->join('plans', 'plans.plan_id = subscriptions.plan_id', 'left');
		$this->db->join('plan_types', 'plan_types.plan_type_id = plans.plan_type_id', 'left');
		$this->db->select('subscriptions.*');
		$this->db->select('subscriptions.active AS sub_active');
		$this->db->select('customers.*');
		$this->db->select('countries.*');
		$this->db->select('plans.name');
		$this->db->select('plan_types.type AS plan_type',false);
		$this->db->select('plans.interval AS plan_interval',false);
		$this->db->select('plans.amount AS plan_amount',false);
		$this->db->select('plans.notification_url AS plan_notification_url',false);
		$query = $this->db->get('subscriptions');
		$data = array();
		if($query->num_rows() > 0) {
			$i=0;
			foreach($query->result() as $row) {
				$data[$i]['id'] = $row->subscription_id;
				$data[$i]['gateway_id'] = $row->gateway_id;
				$data[$i]['date_created'] = local_time($row->timestamp);
				$data[$i]['amount'] = money_format("%!^i",$row->amount);
				$data[$i]['interval'] = $row->charge_interval;
				$data[$i]['start_date'] = local_time($row->start_date);
				$data[$i]['end_date'] = local_time($row->end_date);
				$data[$i]['last_charge_date'] = local_time($row->last_charge);
				$data[$i]['renewed'] = $row->renewed;
				$data[$i]['updated'] = $row->updated;
				$data[$i]['next_charge_date'] = (strtotime($row->next_charge) < strtotime($row->end_date)) ? local_time($row->next_charge) : local_time($row->end_date);
				if ($row->sub_active == '0' and $row->cancel_date == '0000-00-00 00:00:00') {
					// this sub never even started
					$data[$i]['cancel_date'] = local_time($row->start_date);
				}
				elseif ($row->sub_active == '0') {
					$data[$i]['cancel_date'] = local_time($row->cancel_date);
				}
				$data[$i]['number_occurrences'] = $row->number_occurrences;
				$data[$i]['notification_url'] = $row->notification_url;
				$data[$i]['status'] = ($row->sub_active == '1') ? 'active' : 'inactive';
				$data[$i]['card_last_four'] = $row->card_last_four;
				
				if($row->customer_id !== 0) {
					$data[$i]['customer']['customer_id'] = $row->customer_id;
					$data[$i]['customer']['id'] = $row->customer_id;
					$data[$i]['customer']['internal_id'] = $row->internal_id;
					$data[$i]['customer']['first_name'] = $row->first_name;
					$data[$i]['customer']['last_name'] = $row->last_name;
					$data[$i]['customer']['company'] = $row->company;
					$data[$i]['customer']['address_1'] = $row->address_1;
					$data[$i]['customer']['address_2'] = $row->address_2;
					$data[$i]['customer']['city'] = $row->city;
					$data[$i]['customer']['state'] = $row->state;
					$data[$i]['customer']['postal_code'] = $row->postal_code;
					$data[$i]['customer']['country'] = $row->iso2;
					$data[$i]['customer']['email'] = $row->email;
					$data[$i]['customer']['phone'] = $row->phone;
				}
			
				if($row->plan_id != 0) {
					$data[$i]['plan']['id'] = $row->plan_id;
					$data[$i]['plan']['type'] = $row->plan_type;
					$data[$i]['plan']['name'] = $row->name;
					$data[$i]['plan']['amount'] = money_format("%!^i",$row->plan_amount);
					$data[$i]['plan']['interval'] = $row->plan_interval;
					$data[$i]['plan']['notification_url'] = $row->plan_notification_url;
				}
				
				$i++;
			}
		} else {
			return FALSE;
		}
		
		return $data;
	}
	
	/**
	* Updates a subscription based on moving it to a new plan
	*
	* Upgrades or downgrades a subscription to a new plan
	*
	* @param int $recurring_id The ID of the recurring charge
	* @param int $new_plan_id The ID of the new plan
	*
	* @return bool TRUE upon success, FALSE upon failure
	*
	*/
	function ChangeRecurringPlan ($recurring_id, $new_plan_id) {
		$this->CI->load->model('billing/plan_model');
		
		$plan_details = $this->CI->plan_model->GetPlanDetails($new_plan_id);
		
		$update = array(
					'plan_id' => $plan_details->plan_id,
					'amount' => $plan_details->amount,
					'recur' => array('interval' => $plan_details->interval),
					'notification_url' => $plan_details->notification_url,
					'recurring_id' => $recurring_id
					);
					
		$this->CI->transaction_log->log_event(FALSE, $recurring_id, 'change_recurring_plan', $update, __FILE__, __LINE__);					
					
		return $this->UpdateRecurring($update);
	}
	
	/**
	* Update an existing subscription.
	*
	* Updates an existing subscription with new parameters.
	*
	* @param int $params['recurring_id'] The subscription ID to update.
 	* @param string $params['notification_url'] The new notification URL. Optional.
	* @param int $params['customer_id'] The new customer id. Optional.
	* @param int $params['amount'] The new amount to charge. Optional
	* @param int $params['interval'] The new number of days between charges. Optional.
	* @param int $params['plan_id'] The new plan ID. Optional.
	*
	* @return bool TRUE upon success, FALSE upon failure
	* 
	*/
	function UpdateRecurring($params)
	{
		if(!isset($params['recurring_id'])) {
			return FALSE;
		}

		if(isset($params['notification_url'])) {
			$update_data['notification_url'] = $params['notification_url'];
		}
		
		if(isset($params['customer_id'])) {
			$update_data['customer_id'] = $params['customer_id'];
			$this->CI->load->model('billing/customer_model');
			$customer = $this->CI->customer_model->GetCustomerDetails($params['customer_id']);
		} else {
			$customer = FALSE;
		}
		
		if(isset($params['amount'])) {
			$update_data['amount'] = $params['amount'];
		}
		
		if(isset($params['plan_id'])) {
			$update_data['plan_id'] = $params['plan_id'];
		}
		
		if(isset($params['next_charge_date'])) {
			$this->load->library('field_validation');
			if ($this->field_validation->ValidateDate($params['next_charge_date'])) {
				$update_data['next_charge'] = $params['next_charge_date'];			
			}
			else {
				die($this->response->Error(5007));
			}
		}
		
		$subscription = $this->GetSubscriptionDetails($params['recurring_id']);
		//print_r($subscription);
		if(isset($params['recur']['interval'])) {
			$update_data['charge_interval'] = $params['recur']['interval'];
			// Get the subcription details
			
			$start_date = $subscription['start_date'];
			$end_date = $subscription['end_date'];
			// Figure the total number of occurrences
			$update_data['number_occurrences'] = round((strtotime($end_date) - strtotime($start_date)) / ($params['recur']['interval'] * 86400), 0);
		}
		
		if(!isset($update_data)) {
			die($this->response->Error(6003));
		}
		
		// Make sure they update their own subscriptions
		$this->db->where('subscription_id', $params['recurring_id']);
		
		$this->db->update('subscriptions', $update_data);
		
		$this->CI->transaction_log->log_event(FALSE, $params['recurring_id'], 'update_recurring', $update_data, __FILE__, __LINE__);
		
		// Update the subscription with the gateway
		$this->CI->load->model('billing/gateway_model');

		$gateway = $this->CI->gateway_model->GetGatewayDetails($subscription['gateway_id']);
		$gateway_type = $gateway['name'];
		
		$this->load->library('billing/payment/'.$gateway_type);
		
		// get the settings for the gateway
		$settings = $this->$gateway_type->Settings();
		
		if($settings['allows_updates'] === 0) {
			die($this->response->Error(5016));
		}
		
		$update_success = $this->$gateway_type->UpdateRecurring($gateway, $subscription, $customer, $params);
		
		$this->CI->transaction_log->log_event(FALSE, $params['recurring_id'], 'update_recurring_gateway', array('result' => $update_success), __FILE__, __LINE__);
		
		if (!$update_success) {
			return FALSE;
		}
		
		return TRUE;
	}
	
	/*
	* Cancels the recurring billing
	*
	* @param int $recurring_id The recurring charge ID
	*
	* @return bool TRUE upon success, FALSE upon fail
	*
	*/
	
	function CancelRecurring($recurring_id, $expiring = FALSE)
	{
		$this->CI->transaction_log->log_event(FALSE, $recurring_id, 'cancel_requested', array('expiring' => $expiring), __FILE__, __LINE__);

		// Get the subscription information
		$subscription = $this->GetSubscriptionDetails($recurring_id);
		
		// Get the gateway info to load the proper library
		$this->CI->load->model('billing/gateway_model');
		$gateway = $this->CI->gateway_model->GetGatewayDetails($subscription['gateway_id']);
		
		$gateway_name = $subscription['name'];
		$this->load->library('billing/payment/'.$gateway_name);
		$cancelled = $this->$gateway_name->CancelRecurring($subscription, $gateway);
		
		$this->CI->transaction_log->log_event(FALSE, $recurring_id, 'cancel_gateway', array('result' => $cancelled), __FILE__, __LINE__);
		
		$this->MakeInactive($recurring_id);
		
		if ($cancelled) {
			return TRUE;
		} else {
			return FALSE;
		}
	}	
	
	function GetPlansByCustomer($customer_id)
	{
		$this->db->join('plans', 'plans.plan_id = subscriptions.plan_id', 'inner');
		$this->db->join('plan_types', 'plan_types.plan_type_id = plans.plan_type_id', 'inner');
		$this->db->where('customer_id', $customer_id);
		$this->db->where('subscriptions.plan_id <>', 0);
		$query = $this->db->get('subscriptions');
		if($query->num_rows() > 0) {
			return $query->result();
		} else {
			return FALSE;
		}
	}
	
	function GetAllSubscriptionsByGatewayID($gateway_id)
	{
		$this->db->join('gateways', 'subscriptions.gateway_id = gateways.gateway_id', 'inner');
		$this->db->join('external_apis', 'gateways.external_api_id = external_apis.external_api_id', 'inner');
		$this->db->where('subscriptions.gateway_id', $gateway_id);
		$this->db->where('subscriptions.active', 1);
		$query = $this->db->get('subscriptions');
		
		if($query->num_rows > 0) {
			return $query->result_array();
		} else {
			return FALSE;
		}
		
	}
	
	function GetAllSubscriptionsByChargeDate($date = FALSE)
	{
		if(!$date) {
			$date = date('Y-m-d');
		}
		
		$this->db->join('gateways', 'subscriptions.gateway_id = gateways.gateway_id', 'inner');
		$this->db->join('external_apis', 'gateways.external_api_id = external_apis.external_api_id', 'inner');
		$this->db->where('next_charge', $date);
		$this->db->where('subscriptions.active', 1);
		$query = $this->db->get('subscriptions');
		
		if($query->num_rows > 0) {
			return $query->result_array();
		} else {
			return FALSE;
		}
		
	}
	
	function GetAllSubscriptionsForExpiring() {
		$this->db->join('gateways', 'subscriptions.gateway_id = gateways.gateway_id', 'inner');
		$this->db->join('external_apis', 'gateways.external_api_id = external_apis.external_api_id', 'inner');
		$this->db->where('end_date <= NOW()');
		$this->db->where('subscriptions.active', 1);
		$query = $this->db->get('subscriptions');
		
		if($query->num_rows > 0) {
			return $query->result_array();
		} else {
			return FALSE;
		}
	}
	
	function GetAllSubscriptionsForCharging($date = FALSE)
	{
		if (!$date) {
			$date = date('Y-m-d');
		}
		
		$this->db->join('gateways', 'subscriptions.gateway_id = gateways.gateway_id', 'inner');
		$this->db->join('external_apis', 'gateways.external_api_id = external_apis.external_api_id', 'inner');
		$this->db->where('next_charge <=', $date);
		$this->db->where('subscriptions.active', '1');
		$query = $this->db->get('subscriptions');
		
		if ($query->num_rows > 0) {
			return $query->result_array();
		} else {
			return FALSE;
		}		
	}
	
	function GetAllSubscriptionsByDate($date_type = FALSE, $date = FALSE)
	{
		if(!$date) {
			$date = date('Y-m-d');
		}
		
		if(!$date_type) {
			$date_type = 'next_charge';
		}
		
		$this->db->join('gateways', 'subscriptions.gateway_id = gateways.gateway_id', 'inner');
		$this->db->join('external_apis', 'gateways.external_api_id = external_apis.external_api_id', 'inner');
		$this->db->where($date_type, $date);
		$this->db->where('subscriptions.active', 1);
		$query = $this->db->get('subscriptions');
		
		if($query->num_rows > 0) {
			return $query->result_array();
		} else {
			return FALSE;
		}		
	}
	
	function GetNextChargeDate($subscription_id, $from_date = FALSE)
	{
		if(!$from_date) {
			$from_date = date('Y-m-d');
		}
		
		$this->db->where('subscription_id', $subscription_id);
		$query = $this->db->get('subscriptions');
		if($query->num_rows() > 0) {
			$row = $query->row();
			
			$from_date = (empty($from_date)) ? $row->next_charge : $from_date;
			
			$next_charge = strtotime($from_date) + ($row->charge_interval * 86400);
			return date('Y-m-d', $next_charge);
		}
		
		return FALSE;	
	}
	
	function SetChargeDates($subscription_id, $last_charge, $next_charge)
	{
		$update_data = array('last_charge' => $last_charge, 'next_charge' => $next_charge);
		
		$this->db->where('subscription_id', $subscription_id);
		$this->db->update('subscriptions', $update_data);
	}
	
	/**
	* Get Details of the last order for a customer.
	*
	* Returns array of order details for a specific order_id.
	*
	* @param int $customer_id The customer ID.
	* 
	* @return array|bool Array with charge details or FALSE upon failure
	*/
	
	function GetChargesByDate($date)
	{	
		$date = date('Y-m-d', $date);
		
		$this->db->join('customers', 'customers.customer_id = subscriptions.customer_id', 'left');
		$this->db->join('countries', 'countries.country_id = customers.country', 'left');
		$this->db->where('subscriptions.active', 1);
		$this->db->where('next_charge', $date);
		$this->db->where('end_date >', $date);
		$query = $this->db->get('subscriptions');
		if($query->num_rows() > 0) {
			return $query->result_array();
		} else {
			return FALSE;
		}
	}


	/**
	* Get Details of the last order for a customer.
	*
	* Returns array of order details for a specific order_id.
	*
	* @param int $customer_id The customer ID.
	* 
	* @return array|bool Array with charge details or FALSE upon failure
	*/
	
	function GetChargesByExpiryDate($date)
	{	
		$this->db->join('customers', 'customers.customer_id = subscriptions.customer_id', 'left');
		$this->db->join('countries', 'countries.country_id = customers.country', 'left');
		$this->db->where('subscriptions.active', 1);
		$this->db->where('end_date', date('Y-m-d', $date));
		$query = $this->db->get('subscriptions');
		if($query->num_rows() > 0) {
			return $query->result_array();
		} else {
			return FALSE;
		}
	}
	
	/**
	* Get Details of the last order for a customer.
	*
	* Returns array of order details for a specific order_id.
	*
	* @param int $customer_id The customer ID.
	* 
	* @return array|bool Array with charge details or FALSE upon failure
	*/
	
	function CancelRecurringByGateway($gateway_id)
	{	
		$this->db->select('subscription_id');
		$this->db->where('gateway_id', $gateway_id);
		$query = $this->db->get('subscriptions');
		if($query->num_rows() > 0) {
			foreach($query->result() as $row) {
				$this->CancelRecurring($row->subscription_id);
			}
			
			return TRUE;
			
		} else {
			return FALSE;
		}
	}
}