<?php

/**
* Subscription Model
*
* This is a modification of the recurring_model, a wrapper for a cleaner API for Hero
* It should be edited before the recurring model.
*
* @author Electric Function, Inc.
* @package Hero Framework

*/

class Subscription_model extends CI_Model
{
	private $cache;

	function __construct()
	{
		parent::__construct();
	}

	/**
	* Hook: Subscription Charge
	*
	* Called by the subscription_charge hook
	*
	* @param int $subscription_id
	*
	* @return void
	*/
	function hook_subscription_charge ($charge_id, $subscription_id) {
		$CI =& get_instance();

		$CI->load->model('store/taxes_model');

		if ($tax = $CI->taxes_model->get_tax_for_subscription($subscription_id)) {
			$CI->taxes_model->record_tax($tax['tax_id'], $charge_id, 0, $tax['tax_amount']);
		}
	}

	/**
	* Hook: Subscription New
	*
	* Called by the subscription_new hook
	*
	* @param int $subscription_id
	*/
	function hook_subscription_new ($subscription_id) {
		$subscription = $this->get_subscription($subscription_id);

		$CI =& get_instance();
		$CI->load->model('billing/subscription_plan_model');

		$plan = $CI->subscription_plan_model->get_plan($subscription['plan_id']);

		if (!empty($plan['promotion'])) {
    		$CI->user_model->add_group($subscription['user_id'], $plan['promotion']);
    	}
    	
    	// mark the subscription as setup
    	$CI->db->update('subscriptions', array('completed' => '1'), array('subscription_id' => $subscription_id));
    	
    	// any renewal maintenance?
    	$CI->load->model('billing/charge_data_model');
    	$data = $CI->charge_data_model->Get('r' . $subscription_id);
    	
    	if (isset($data['mark_as_renewed'])) {
    		$CI->load->model('billing/recurring_model');
	    	$CI->recurring_model->SetRenew($data['mark_as_renewed'], $subscription_id);
			$CI->recurring_model->CancelRecurring($data['mark_as_renewed']);
		}
	}

	/**
	* Hook: Subscription Renew
	*
	* Called by the subscription_renew hook
	*
	* @param int $subscription_id
	*/
	function hook_subscription_renew ($subscription_id) {
		$subscription = $this->get_subscription($subscription_id);

		$CI =& get_instance();
		$CI->load->model('billing/subscription_plan_model');

		$plan = $CI->subscription_plan_model->get_plan($subscription['plan_id']);

		if (!empty($plan['promotion'])) {
    		$CI->user_model->add_group($subscription['user_id'], $plan['promotion']);
    	}
    	
    	// mark the subscription as setup
    	$CI->db->update('subscriptions', array('completed' => '1'), array('subscription_id' => $subscription_id));
    	
    	// any renewal maintenance?
    	$CI->load->model('billing/charge_data_model');
    	$data = $CI->charge_data_model->Get('r' . $subscription_id);
    	
    	if (isset($data['mark_as_renewed'])) {
    		$CI->load->model('billing/recurring_model');
	    	$CI->recurring_model->SetRenew($data['mark_as_renewed'], $subscription_id);
			$CI->recurring_model->CancelRecurring($data['mark_as_renewed']);
		}
	}

	/**
	* Hook: Subscription Expire
	*
	* Called by the subscription_expire hook
	*
	* @param int $subscription_id
	*/
	function hook_subscription_expire ($subscription_id) {
		$subscription = $this->get_subscription($subscription_id);

		$CI =& get_instance();
		$CI->load->model('billing/subscription_plan_model');

		$plan = $CI->subscription_plan_model->get_plan($subscription['plan_id']);

		if (!empty($plan['demotion'])) {
			$CI->user_model->add_group($subscription['user_id'], $plan['demotion']);
    	}

    	// remove from promotion?
    	if (!empty($plan['promotion'])) {
    		$remove_from_group = TRUE;

    		// if there is an active group that promotes to this group, however, we won't remove them...
    		$subscriptions = $this->get_subscriptions(
    						array(
    							'user_id' => $subscription['user_id'],
	    						'end_date_after' => date('Y-m-d',strtotime('now + 2 days')),
	    						'promotion' => $plan['promotion']
	    						)
	    					);

	    	if (!empty($subscriptions)) {
	    		$remove_from_group = FALSE;
	    	}

	    	if ($remove_from_group == TRUE) {
	    		$CI->user_model->remove_group($subscription['user_id'], $plan['promotion']);
	    	}
    	}
	}
	
	/**
	* Hook: Member Delete
	*
	* Called by the member_delete hook
	*
	* @param int $member_id
	*/
	function hook_member_delete ($member_id) {
		$subscriptions = $this->get_subscriptions_friendly(array('active' => TRUE), $member_id);
		
		if (!empty($subscriptions)) {
			foreach ($subscriptions as $subscription) {
				$this->cancel_subscription($subscription['id']);
			}
		}
		
		return TRUE;
	}

	/**
	* Cancel Subscription
	*
	* @param int $subscription_id
	*
	* @return boolean
	*/
	public function cancel_subscription ($subscription_id) {
		$CI =& get_instance();
		$CI->load->model('billing/recurring_model');
		$return = $CI->recurring_model->CancelRecurring($subscription_id);

		if ($return == TRUE) {
			// hook
			$this->app_hooks->data('subscription',$subscription_id);
			$this->app_hooks->trigger('subscription_cancel');
			$this->app_hooks->reset();
		}

		return $return;
	}

	/**
	* Expire Subscription
	*
	* @param int $subscription_id
	*
	* @return boolean
	*/
	public function expire_subscription ($subscription_id) {
		$this->db->update('subscriptions', array('expiry_processed' => '1'), array('subscription_id' => $subscription_id));

		return TRUE;
	}

	/**
	* Member Has Subscriptions?
	*
	* @param int $member_id (default: active user)
	*
	* @return boolean
	*/
	function has_subscriptions ($member_id = FALSE) {
		if ($member_id === FALSE) {
			$CI =& get_instance();
			if ($CI->user_model->logged_in()) {
				$member_id = $CI->user_model->get('id');
			}
			else {
				return FALSE;
			}
		}

		if ($this->get_subscriptions(array('user_id' => $member_id, 'end_date_after' => date('Y-m-d H:i:s'))) !== FALSE) {
			return TRUE;
		}
		else {
			return FALSE;
		}
	}
	
	/**
	* Get Log
	*
	* Get the transaction log for a given subscription
	*
	* @param int $subscription_id
	*
	* @return array
	*/
	function get_log ($filters) {
		$result = $this->db->where('subscription_id', $filters['id'])
						   ->get('transaction_log');
						   
		$log = array();				   
		foreach ($result->result_array() as $logged) {
			$log[] = array(
							'ip' => $logged['log_ip'],
							'date' => $logged['log_date'],
							'browser' => $logged['log_browser'],
							'event' => $logged['log_event'],
							'data' => unserialize($logged['log_data']),
							'file' => $logged['log_file'],
							'line' => $logged['log_line']
						);
		}
		
		return $log;
	}

	/**
	* Get Subscriptions Friendly
	*
	* A friendly wrapper for $this->get_subscriptions();
	*
	* @param boolean $filters['active'] Is the subscription still active on the account (i.e., end_date < now)?
	* @param boolean $filters['recurring'] Is the subscription still actively recurring?
	* @param int $filters['id'] The subscription ID
	* @param int $filters['plan_id'] The subscription plan ID
	* @param int $member_id The member ID to tie the subscription to (default: active user).
	*
	* @return array subscriptions
	*/
	public function get_subscriptions_friendly ($filters = array(), $member_id = FALSE) {
		if ($member_id === FALSE) {
			$CI =& get_instance();
			if ($CI->user_model->logged_in()) {
				$member_id = $CI->user_model->get('id');
			}
			else {
				return FALSE;
			}
		}

		// prep filters for the next call
		$new_filters = array();

		$new_filters['user_id'] = $member_id;

		if (isset($filters['plan_id'])) {
			// `subscription_plans` table
			$new_filters['plan_id'] = $filters['plan_id'];
		}

		if (isset($filters['id'])) {
			$new_filters['id'] = $filters['id'];
		}

		if (isset($filters['active'])) {
			$new_filters['end_date_after'] = date('Y-m-d H:i:s');
		}

		if (isset($filters['recurring'])) {
			$new_filters['active'] = '1';
		}

		$subscriptions = $this->get_subscriptions($new_filters);

		return $subscriptions;
	}

	/**
	* Get Subscription
	*
	* @param int $subscription_id
	*
	* @return array Subscription details, else FALSE
	*/
	public function get_subscription ($subscription_id) {
		if (isset($this->cache[$subscription_id])) {
			return $this->cache[$subscription_id];
		}

		$sub = $this->get_subscriptions(array('id' => $subscription_id));

		if (empty($sub)) {
			return FALSE;
		}

		$this->cache[$subscription_id] = $sub[0];
		return $sub[0];
	}

	/**
	* Count Subscriptions
	*/
	function count_subscriptions ($filters) {
		return $this->get_subscriptions($filters, TRUE);
	}

	/**
	* Get Subscriptions
	*
	* Returns an array of results based on submitted search criteria.
	*
	* @param int $filters['id'] The subscription ID
	* @param string $filters['status'] One of "recurring", "will_expire", "expired", "renewed", "updated"
	* @param int $filters['gateway_id'] The gateway ID used for the order. Optional.
	* @param date $filters['created_after'] Only subscriptions created after or on this date will be returned. Optional.
	* @param date $filters['created_before'] Only subscriptions created before or on this date will be returned. Optional.
	* @param date $filters['end_date_after'] Only subscriptions ending after or on this date will be returned. Optional.
	* @param date $filters['end_date_before'] Only subscriptions ending before or on this date will be returned. Optional.
	* @param int $filters['user_id'] The customer id associated with the subscription. Optional.
	* @param int $filters['amount'] Only subscriptions for this amount will be returned. Optional.
	* @param boolean $filters['active'] Returns only active subscriptions. Optional.
	* @param int $filters['plan_id'] Only return subscriptions link to this subscription_plan_id. Optional.
	* @param int $filters['offset'] Offsets the database query.
	* @param int $filters['limit'] Limits the number of results returned. Optional.
	* @param string $filters['sort'] Variable used to sort the results.  Possible values are date, customer_first_name, customer_last_name, amount. Optional
	* @param string $filters['sort_dir'] Used when a sort param is supplied.  Possible values are asc and desc. Optional.
	*
	* @return mixed Array containing results
	*/
	public function get_subscriptions ($filters, $counting = FALSE)
	{
		if (isset($filters['id'])) {
			$this->db->where('subscription_id', $filters['id']);
		}

		if(isset($filters['gateway_id'])) {
			$this->db->where('gateway_id', $filters['gateway_id']);
		}

		if (isset($filters['created_after'])) {
			$start_date = date('Y-m-d', strtotime($filters['created_after']));
			$this->db->where('timestamp >=', $start_date);
		}

		if (isset($filters['created_before'])) {
			$end_date = date('Y-m-d', strtotime($filters['created_before']));
			$this->db->where('timestamp <=', $end_date);
		}

		if (isset($filters['end_date_after'])) {
			$end_date = date('Y-m-d H:i:s', strtotime($filters['end_date_after']));
			$this->db->where('end_date >=', $end_date);
			$this->db->where('end_date !=','0000-00-00');
		}

		if (isset($filters['end_date_before'])) {
			$end_date = date('Y-m-d H:i:s', strtotime($filters['end_date_before']));
			$this->db->where('end_date <=', $end_date);
			$this->db->where('end_date !=','0000-00-00');
		}

		if (isset($filters['cancel_date_after'])) {
			$end_date = date('Y-m-d H:i:s', strtotime($filters['cancel_date_after']));
			$this->db->where('cancel_date >=', $end_date);
			$this->db->where('cancel_date !=','0000-00-00');
		}

		if (isset($filters['cancel_date_before'])) {
			$end_date = date('Y-m-d H:i:s', strtotime($filters['cancel_date_before']));
			$this->db->where('cancel_date <=', $end_date);
			$this->db->where('cancel_date !=','0000-00-00');
		}

		if (isset($filters['user_id'])) {
			$this->db->where('users.user_id', $filters['user_id']);
			
			// if we are loading subs for a user, we assume we only want subs that are "completed" (i.e., fully set up)
			$this->db->where('completed','1');
		}

		if (isset($filters['member_name'])) {
			if (is_numeric($filters['member_name'])) {
				// we are passed a member id
				$this->db->where('users.user_id',$filters['member_name']);
			} else {
				$this->db->like('users.user_last_name', $filters['member_name']);
			}
		}

		if(isset($filters['amount'])) {
			$this->db->where('subscriptions.amount', $filters['amount']);
		}

		if (isset($filters['active'])) {
			$this->db->where('subscriptions.active', $filters['active']);
		}

		if (isset($filters['plan_id'])) {
			$this->db->where('subscription_plans.subscription_plan_id', $filters['plan_id']);
		}

		if (isset($filters['promotion'])) {
			$this->db->where('subscription_plans.subscription_plan_promotion', $filters['promotion']);
		}

		if (isset($filters['expiry_processed'])) {
			$this->db->where('subscriptions.expiry_processed', $filters['expiry_processed']);
		}

		if (isset($filters['status'])) {
			if ($filters['status'] == 'recurring') {
				$this->db->where('subscriptions.active','1');
				$this->db->where('subscriptions.end_date >',date('Y-m-d'));
			}
			elseif ($filters['status'] == 'will_expire') {
				$this->db->where('subscriptions.active','0');
				$this->db->where('subscriptions.end_date >',date('Y-m-d'));
			}
			elseif ($filters['status'] == 'expired') {
				$this->db->where('subscriptions.end_date <',date('Y-m-d'));
				$this->db->where('subscriptions.renewed','0');
				$this->db->where('subscriptions.updated','0');
			}
			elseif ($filters['status'] == 'renewed') {
				$this->db->where('subscriptions.renewed !=','0');
			}
			elseif ($filters['status'] == 'updated') {
				$this->db->where('subscriptions.updated !=','0');
			}
		}

		// standard ordering and limiting
		$order_by = (isset($filters['sort'])) ? $filters['sort'] : 'subscriptions.subscription_id';
		$order_dir = (isset($filters['sort_dir'])) ? $filters['sort_dir'] : 'DESC';
		$this->db->order_by($order_by, $order_dir);

		if (isset($filters['limit'])) {
			$offset = (isset($filters['offset'])) ? $filters['offset'] : 0;
			$this->db->limit($filters['limit'], $offset);
		}

		$this->db->join('customers', 'customers.customer_id = subscriptions.customer_id', 'inner');
		$this->db->join('users', 'users.user_id = customers.internal_id','inner');
		$this->db->join('plans', 'plans.plan_id = subscriptions.plan_id', 'inner');
		$this->db->join('subscription_plans','subscription_plans.plan_id = plans.plan_id');
		$this->db->join('plan_types', 'plan_types.plan_type_id = plans.plan_type_id', 'inner');

		if ($counting == FALSE) {
			$this->db->select('subscriptions.*');
			$this->db->select('subscriptions.active AS sub_active');
			$this->db->select('subscription_plans.subscription_plan_id');
			$this->db->select('users.*');
			$this->db->select('plans.name');
			$this->db->select('plan_types.type AS plan_type',false);
			$this->db->select('plans.interval AS plan_interval',false);
			$this->db->select('plans.amount AS plan_amount',false);

			$result = $this->db->get('subscriptions');
		}
		else {
			$this->db->select('COUNT(`subscriptions`.`subscription_id`) AS `counted`',FALSE);
			$result = $this->db->get('subscriptions');
			$count = $result->row_array();
			return $count['counted'];
		}

		if ($result->num_rows() == 0) {
			return FALSE;
		}

		$subscriptions = array();

		foreach($result->result_array() as $subscription) {
			$this_subscription = array(
					'id' => $subscription['subscription_id'],
					'user_id' => $subscription['user_id'],
					'user_username' => $subscription['user_username'],
					'user_first_name' => $subscription['user_first_name'],
					'user_last_name' => $subscription['user_last_name'],
					'user_email' => $subscription['user_email'],
					'gateway_id' => $subscription['gateway_id'],
					'date_created' => local_time($subscription['timestamp']),
					'amount' => money_format("%!^i",$subscription['amount']),
					'interval' => $subscription['charge_interval'],
					'start_date' => local_time($subscription['start_date']),
					'end_date' => ($subscription['end_date'] != '0000-00-00') ? local_time($subscription['end_date']) : FALSE,
					'last_charge_date' => ($subscription['last_charge'] != '0000-00-00') ? local_time($subscription['last_charge']) : FALSE,
					'next_charge_date' => (strtotime($subscription['next_charge']) < strtotime($subscription['end_date']) and $subscription['next_charge'] != '0000-00-00') ? local_time($subscription['next_charge']) : FALSE,
					'cancel_date' => ($subscription['cancel_date'] != '0000-00-00') ? local_time($subscription['cancel_date']) : FALSE,
					'number_occurrences' => $subscription['number_occurrences'],
					'active' => ($subscription['sub_active'] == '1') ? TRUE : FALSE,
					'coupon_id' => $subscription['coupon_id'],
					'renewing_subscription_id' => $subscription['renewed'],
					'updating_subscription_id' => $subscription['updated'],
					'card_last_four' => (!empty($subscription['card_last_four'])) ? $subscription['card_last_four'] : FALSE,
					'plan_id' => $subscription['subscription_plan_id'], // for `subscription_plans`
					'renew_link' => site_url('billing/subscriptions/renew/' . $subscription['subscription_id']),
					'cancel_link' => site_url('users/cancel/' . $subscription['subscription_id']),
					'update_cc_link' => (!empty($subscription['card_last_four'])) ? site_url('users/update_cc/' . $subscription['subscription_id']) : FALSE,
					// status fields for this abstraction
					'is_recurring' => ($subscription['sub_active'] == '1' and (strtotime($subscription['end_date']) > strtotime($subscription['next_charge']))) ? TRUE : FALSE,
					'is_active' => (strpos($subscription['end_date'],'0000-00-00') === TRUE or strtotime($subscription['end_date']) > time()) ? TRUE : FALSE,
					'is_renewed' => empty($subscription['renewed']) ? FALSE : TRUE,
					'is_updated' => empty($subscription['updated']) ? FALSE : TRUE
				);

			if ($subscription['sub_active'] == '0' and $subscription['cancel_date'] == '0000-00-00 00:00:00') {
				// this sub never even started
				$this_subscription['cancel_date'] = local_time($subscription['start_date']);
			}
			elseif (empty($subscription['sub_active'])) {
				$this_subscription['cancel_date'] = local_time($subscription['cancel_date']);
			}

			if (!empty($subscription['plan_id'])) {
				$this_subscription['plan']['id'] = $subscription['plan_id'];
				$this_subscription['plan']['type'] = $subscription['plan_type'];
				$this_subscription['plan']['name'] = $subscription['name'];
				$this_subscription['plan']['amount'] = money_format("%!^i",$subscription['plan_amount']);
				$this_subscription['plan']['interval'] = $subscription['plan_interval'];
			}

			$subscriptions[] = $this_subscription;
		}

		return $subscriptions;
	}

	function hook_cron () {
		$CI =& get_instance();

		cron_log('Beginning billing cronjob.');
		
		$run_cron = TRUE;

		// cron run time?
		if ($CI->config->item('billing_cron_time')) {
			$run_time = (int)$CI->config->item('billing_cron_time');
		}
		else {
			$run_time = 11;
		}

		// we only need this to run once per day
		if (setting('cron_billing_last_update') === FALSE) {
			cron_log('Created billing last update setting to track billing cron runs and limit them to once per day.');

			$this->settings_model->new_setting(1, 'cron_billing_last_update', date('Y-m-d H:i:s'), 'When did the billing cron job last run?', 'text', '', FALSE, TRUE);
		}
		elseif (date('Y-m-d') == date('Y-m-d', strtotime(setting('cron_billing_last_update')))) {
			cron_log('Billing cronjob has already run today.  Exiting.');

			$run_cron = FALSE;
		}
		elseif ((int)date('H') < $run_time) {
			cron_log('Billing cronjob is configured not to run until ' . $run_time . ' hours.  Exiting.');

			$run_cron = FALSE;
		}
		else {
			cron_log('Updated billing last update setting to current time.  We\'re running it!');

			$this->settings_model->update_setting('cron_billing_last_update', date('Y-m-d H:i:s'));
		}

		if ($run_cron == FALSE) {
			return;
		}

		// subscription maintenance
		$CI->load->model('billing/gateway_model');
		$CI->load->model('billing/recurring_model');

		// cancel subscriptions if end_date is today or earlier and they are still active
		$cancelled = array();
		$subscriptions = $this->get_subscriptions(array(
													'end_date_before' => date('Y-m-d',strtotime('now')),
													'active' => '1'
												));
		if (!empty($subscriptions)) {
			foreach($subscriptions as $subscription) {
				$response = $this->cancel_subscription($subscription['id']);
				if ($response) {
					$this->app_hooks->data('subscription',$subscription['id']);
					$this->app_hooks->trigger('subscription_cancel', $subscription['id']);
					$this->app_hooks->reset();
					$cancelled[] = $subscription['id'];
				}
			}
		}

		// expire subscriptions with an end_date of today or earlier and expiry_processed == 0
		$expired = array();
		$subscriptions = $this->get_subscriptions(array(
													'end_date_before' => date('Y-m-d',strtotime('now')),
													'expiry_processed' => '0'
												));
		if (!empty($subscriptions)) {
			foreach($subscriptions as $subscription) {
				$this->expire_subscription($subscription['id']);

				if ($subscription['is_renewed'] == TRUE or $subscription['is_updated'] == TRUE) {
					// don't send confusing notices
					continue;
				}

				$this->app_hooks->data('subscription',$subscription['id']);
				$this->app_hooks->trigger('subscription_expire', $subscription['id']);
				$this->app_hooks->reset();
				$expired[] = $subscription['id'];
			}
		}

		// charge subscriptions that need to be charged today
		$today = date('Y-m-d');
		$subscriptions = $CI->recurring_model->GetAllSubscriptionsForCharging($today);

		$charge_success = array();
		$charge_failure = array();
		if ($subscriptions) {
			foreach ($subscriptions as $subscription) {
				// try and make the charge
				$response = $CI->gateway_model->ChargeRecurring($subscription);
				if($response) {
					$charge_success[] = $subscription['subscription_id'];
				} else {
					$charge_failure[] = $subscription['subscription_id'];
				}
			}
		}

		// Check for emails to send
		// Get all the recurring charge emails to send in one week
		$sent_emails['subscription_autorecur_in_week'] = array();
		$next_week = mktime(0,0,0, date('m'), date('d') + 7, date('Y'));
		$charges = $CI->recurring_model->GetChargesByDate($next_week);
		if ($charges) {
			foreach($charges as $charge) {
				$this->app_hooks->data('subscription',$charge['subscription_id']);
				$this->app_hooks->trigger('subscription_renew_1_week', $charge['subscription_id']);
				$this->app_hooks->reset();

				$sent_emails['subscription_autorecur_in_week'][] = $charge['subscription_id'];
			}
		}

		// Get all the recurring charge emails to send in one month
		$sent_emails['subscription_autorecur_in_month'] = array();
		$next_month = mktime(0,0,0, date('m') + 1, date('d'), date('Y'));
		$charges = $CI->recurring_model->GetChargesByDate($next_month);
		if ($charges) {
			foreach($charges as $charge) {
				if ($charge['renewed'] == '1' or $charge['updated'] == '1') {
					// don't send confusing notices
					continue;
				}

				$this->app_hooks->data('subscription',$charge['subscription_id']);
				$this->app_hooks->trigger('subscription_renew_1_month', $charge['subscription_id']);
				$this->app_hooks->reset();

				$sent_emails['subscription_autorecur_in_month'][] = $charge['subscription_id'];
			}
		}

		// Get all the recurring expiration emails to send in one week
		$sent_emails['subscription_expiring_in_week'] = array();
		$charges = $CI->recurring_model->GetChargesByExpiryDate($next_week);
		if ($charges) {
			foreach($charges as $charge) {
				if ($charge['renewed'] == '1' or $charge['updated'] == '1') {
					// don't send confusing notices
					continue;
				}

				if (empty($charge['renewed']) and empty($charge['updated'])) {
					$this->app_hooks->data('subscription',$charge['subscription_id']);
					$this->app_hooks->trigger('subscription_expire_1_week', $charge['subscription_id']);
					$this->app_hooks->reset();

					$sent_emails['subscription_expiring_in_week'][] = $charge['subscription_id'];
				}
			}
		}

		// Get all the recurring expiration emails to send in one month
		$sent_emails['subscription_expiring_in_month'] = array();
		$charges = $CI->recurring_model->GetChargesByExpiryDate($next_month);
		if($charges) {
			foreach($charges as $charge) {
				if (empty($charge['renewed']) and empty($charge['updated'])) {
					$this->app_hooks->data('subscription',$charge['subscription_id']);
					$this->app_hooks->trigger('subscription_expire_1_month', $charge['subscription_id']);
					$this->app_hooks->reset();

					$sent_emails['subscription_expiring_in_month'][] = $charge['subscription_id'];
				}
			}
		}

		$charge_success = count($charge_success);
		$charge_failure = count($charge_failure);
		$cancelled = count($cancelled);
		$expired = count($expired);
		$autorecur_week = count($sent_emails['subscription_autorecur_in_week']);
		$autorecur_month = count($sent_emails['subscription_autorecur_in_month']);
		$expire_week = count($sent_emails['subscription_expiring_in_week']);
		$expire_month = count($sent_emails['subscription_expiring_in_month']);

		// report our cron accomplishments!
		cron_log('Successful charges: ' . $charge_success);
		cron_log('Failed Charges: ' . $charge_failure);
		cron_log('Cancelled Subscriptions: ' . $cancelled);
		cron_log('Expired Subscriptions: ' . $expired);
		cron_log('Weekly Charge Reminders Sent: ' . $autorecur_week);
		cron_log('Monthly Charge Reminders Sent: ' . $autorecur_month);
		cron_log('Weekly Expiration Reminders Sent: ' . $expire_week);
		cron_log('Monthly Expiration Reminders Sent: ' . $expire_month);

		return TRUE;
	}
}