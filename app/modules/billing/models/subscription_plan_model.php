<?php

/**
* Subscription Plan Model 
*
* This is an extension of the plans table, essentially.  It is used to bridge the gap
* between OpenGateway's recurring plans and the needs of a membership website.
* It should be edited before the plans model.
*
* @author Electric Function, Inc.
* @package Hero Framework

*/

class Subscription_plan_model extends CI_Model
{
	private $cache;
	
	function __construct()
	{
		parent::__construct();
	}
	
	/**
	* Create New Plan
	*
	* @param string $name Plan name
	* @param float $amount Amount to charge on a recurring basis
	* @param boolean|float $initial_charge Initial charge to bill, or FALSE if same as recurring charge.  Cannot be 0 (use a free trial instead)
	* @param boolean $is_taxable Apply tax rules?
	* @param int $interval Number of days between charges, must be greater than 0
	* @param int $free_trial Number of days to give a free trial
	* @param boolean $require_billing_for_trial Require billing information for a trial?
	* @param boolean|int $occurrences Number of occurrences, or FALSE/0 if unlimited.
	* @param int $promotion Group to promote a user to upon subscription
	* @param int $demotion Group to demota a user to upon expiration
	* @param string $description Plan text description
	*
	* @return int $plan_id
	*/
	function new_plan ($name, $amount, $initial_charge, $is_taxable, $interval, $free_trial, $require_billing_for_trial, $occurrences, $promotion, $demotion, $description) {
		$CI =& get_instance();
		
		$CI->load->model('billing/plan_model');
		
		if (empty($amount) and $initial_charge > 0) {
			die(show_error('You can\'t create a plan that is free but only contains an initial charge.  Use a product for that.'));
		}
		
		$params = array(
						'name' => $name,
						'plan_type' => (empty($amount)) ? 'free' : 'paid',
						'amount' => $amount,
						'interval' => $interval,
						'notification_url' => '',
						'occurrences' => $occurrences,
						'free_trial' => $free_trial
						);
		
		$plan_id = $CI->plan_model->NewPlan($params);
		
		if (!$plan_id) {
			die(show_error('Error creating plan record in `plans`.'));
		}
		
		$insert_fields = array(
							'plan_id' => $plan_id,
							'subscription_plan_initial_charge' => ($initial_charge == $amount or empty($initial_charge)) ? $amount : $initial_charge,
							'subscription_plan_is_taxable' => ($is_taxable == TRUE) ? '1' : '0',
							'subscription_plan_promotion' => $promotion,
							'subscription_plan_demotion' => $demotion,
							'subscription_plan_description' => $description,
							'subscription_plan_require_billing_trial' => ($require_billing_for_trial == FALSE) ? 0 : 1
						);
						
		$this->db->insert('subscription_plans',$insert_fields);
		
		$subscription_plan_id = $this->db->insert_id();
		
		return $subscription_plan_id;
	}
	
	/**
	* Update Plan
	*
	* @param int $subscription_plan_id The subscription plan ID
	* @param string $name Plan name
	* @param float $amount Amount to charge on a recurring basis
	* @param boolean|float $initial_charge Initial charge to bill, or FALSE if same as recurring charge.  Cannot be 0 (use a free trial instead)
	* @param boolean $is_taxable Apply tax rules?
	* @param int $interval Number of days between charges, must be greater than 0
	* @param int $free_trial Number of days to give a free trial
	* @param boolean $require_billing_for_trial Require billing information for a trial?
	* @param boolean|int $occurrences Number of occurrences, or FALSE/0 if unlimited.
	* @param int $promotion Group to promote a user to upon subscription
	* @param int $demotion Group to demota a user to upon expiration
	* @param string $description Plan text description
	*
	* @return void 
	*/
	function update_plan ($subscription_plan_id, $name, $amount, $initial_charge, $is_taxable, $interval, $free_trial, $require_billing_for_trial, $occurrences, $promotion, $demotion, $description) {
		$CI =& get_instance();
		
		$CI->load->model('billing/plan_model');
		
		if (empty($amount) and $initial_charge > 0) {
			die(show_error('You can\'t create a plan that is free but only contains an initial charge.  Use a product for that.'));
		}
		
		$params = array(
						'name' => $name,
						'plan_type' => (empty($amount)) ? 'free' : 'paid',
						'amount' => $amount,
						'interval' => $interval,
						'notification_url' => '',
						'occurrences' => $occurrences,
						'free_trial' => $free_trial
						);
						
		// get plan ID
		$plan = $this->get_plan($subscription_plan_id);
		
		if (!$CI->plan_model->UpdatePlan($plan['plan_id'], $params)) {
			die(show_error('Unable to update plan record in `plans`.'));
		}
		
		$update_fields = array(
							'subscription_plan_initial_charge' => ($initial_charge == $amount or empty($initial_charge)) ? $amount : $initial_charge,
							'subscription_plan_is_taxable' => ($is_taxable == TRUE) ? '1' : '0',
							'subscription_plan_promotion' => $promotion,
							'subscription_plan_demotion' => $demotion,
							'subscription_plan_description' => $description,
							'subscription_plan_require_billing_trial' => ($require_billing_for_trial == FALSE) ? 0 : 1
						);
						
		$this->db->update('subscription_plans',$update_fields, array('subscription_plan_id' => $subscription_plan_id));
		
		return TRUE;
	}
	
	/**
	* Delete Plan
	*
	* @param int $id
	*
	* @return boolean 
	*/
	function delete_plan ($id) {
		$update_data['deleted'] = 1;
		
		$plan = $this->get_plan($id);
		
		$this->db->where('plan_id', $plan['plan_id']);
		$this->db->update('plans', $update_data);
		
		return TRUE;
	}
			
	/**
	* Get Plan
	*
	* @param int $id
	*
	* @return array
	*/
	function get_plan ($id) {
		if (isset($this->cache[$id])) {
			return $this->cache[$id];
		}
		
		$result = $this->get_plans(array('id' => $id), TRUE);
		
		if (empty($result)) {
			return FALSE;
		}
		
		$this->cache[$id] = $result[0];
		return $result[0];
	}
	
	/**
	* Get Plan from API Plan ID
	*
	* @param int $api_plan_id
	*
	* @return array
	*/
	function get_plan_from_api_plan_id ($api_plan_id) {
		$result = $this->get_plans(array('api_plan_id' => $api_plan_id));
		
		if (empty($result)) {
			return FALSE;
		}
		else {
			return $result[0];
		}
	}
	
	/**
	* Get Plans
	*
	* @param int $filters['id']
	* @param float $filters['amount']
	* @param int $filters['interval']
	* @param string $filters['name']
	* @param int $filters['api_plan_id']
	* @param $allow_deleted (default: FALSE)
	*
	* @return array 
	*/
	function get_plans ($filters = array(), $allow_deleted = FALSE) {
		if ($allow_deleted == FALSE) {
			if (isset($filters['deleted']) and $filters['deleted'] == '1') {
				$this->db->where('plans.deleted', '1');
			}
			else {
				$this->db->where('plans.deleted', '0');
			}
		}
		
		if (isset($filters['id'])) {
			$this->db->where('subscription_plan_id', $filters['id']);
		}
		
		if (isset($filters['amount'])) {
			$this->db->where('plans.amount', $filters['amount']);
		}
		
		if (isset($filters['interval'])) {
			$this->db->where('interval', $filters['interval']);
		}
		
		if (isset($filters['name'])) {
			$this->db->where('name', $filters['name']);
		}
		
		if (isset($filters['api_plan_id'])) {
			$this->db->where('plans.plan_id',$filters['api_plan_id']);
		}
		
		// standard ordering and limiting
		$order_by = (isset($filters['sort'])) ? $filters['sort'] : 'plans.name';
		$order_dir = (isset($filters['sort_dir'])) ? $filters['sort_dir'] : 'ASC';
		$this->db->order_by($order_by, $order_dir);
		
		if (isset($filters['limit'])) {
			$offset = (isset($filters['offset'])) ? $filters['offset'] : 0;
			$this->db->limit($filters['limit'], $offset);
		}	
		
		$this->db->select('subscription_plans.*');
		$this->db->select('plans.*');
		$this->db->select('SUM(subscriptions.active) as `active_subscribers`',false);
		$this->db->join('plans','subscription_plans.plan_id = plans.plan_id','left');
		$this->db->join('subscriptions','subscriptions.plan_id = plans.plan_id','left');
		$this->db->group_by('subscription_plan_id');
		$query = $this->db->get('subscription_plans');
		
		$data = array();
		if ($query->num_rows() > 0) {
			foreach($query->result_array() as $row)
			{
				$data[] = array(
								'id' => $row['subscription_plan_id'], // for `subscription_plans`
								'plan_id' => $row['plan_id'], // for `plans`
								'name' => $row['name'],
								'type' => ($row['amount'] != '0.00') ? 'paid' : 'free',
								'initial_charge' => money_format("%!^i",$row['subscription_plan_initial_charge']),
								'amount' => money_format("%!^i",$row['amount']),
								'interval' => $row['interval'],
								'free_trial' => $row['free_trial'],
								'occurrences' => $row['occurrences'],
								'is_taxable' => ($row['subscription_plan_is_taxable'] == '1') ? TRUE : FALSE,
								'active_subscribers' => (empty($row['active_subscribers'])) ? '0' : $row['active_subscribers'],
								'deleted' => ($row['deleted'] == 1) ? TRUE : FALSE,
								'require_billing_for_trial' => ($row['subscription_plan_require_billing_trial'] == 1) ? TRUE : FALSE,
								'promotion' => $row['subscription_plan_promotion'],
								'demotion' => $row['subscription_plan_demotion'],
								'description' => $row['subscription_plan_description'],
								'add_to_cart' => site_url('billing/subscriptions/add_to_cart/' . $row['subscription_plan_id'])
							);
			}
			
		} else {
			return FALSE;
		}
		
		return $data;
	}
}