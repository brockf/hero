<?php

class Cron extends Controller {

	function Cron()
	{
		parent::Controller();	
		
		set_time_limit(300);
	}
	
	function update ($key = '') {
		$this->subscription_maintenance($key);
		$this->send_notifications($key);
	}
	
	function send_notifications ($key = '') {
		if ($this->config->item('cron_key') != $key) {
			echo 'Invalid key.';
			return FALSE;
		}
		
		$this->load->library('notifications');
		
		$notified = $this->notifications->ProcessQueue();
		
		echo $notified . ' notifications sent.';

		return true;
	}
	
	function subscription_maintenance($key = '')
	{
		if ($this->config->item('cron_key') != $key) {
			echo 'Invalid key.';
			return FALSE;
		}
		
		$this->load->model('recurring_model');
		$this->load->model('gateway_model');
		$this->load->library('email');
		
		// Expire subscription if the end date is today or before
		$cancelled = array();
		$subscriptions = $this->recurring_model->GetAllSubscriptionsForExpiring();
		if($subscriptions) {
			foreach($subscriptions as $subscription) {
				// Try and make the charge
				$response = $this->recurring_model->CancelRecurring($subscription['subscription_id'], TRUE);
				if($response) {
					TriggerTrip('subscription_expire', FALSE, $subscription['subscription_id']);
					$cancelled[] = $subscription['subscription_id'];
				}
			}
		}
		
		// get all the subscriptions with a next_charge date of today for the next charge
		$today = date('Y-m-d');
		$subscriptions = $this->recurring_model->GetAllSubscriptionsByDate('next_charge', $today);
		
		$charge_success = array();
		$charge_failure = array();
		if($subscriptions) {
			foreach($subscriptions as $subscription) {
				// Try and make the charge
				$response = $this->gateway_model->ChargeRecurring($subscription);
				if($response) {
					$charge_success[] = $subscription['subscription_id'];
				} else {
					$charge_failure[] = $subscription['subscription_id'];
				}
			}
		}
		
		// Check for emails to send
		// Get all the recurring charge emails to send in one week
		$sent_emails['recurring_autorecur_in_week'] = array();
		$next_week = mktime(0,0,0, date('m'), date('d') + 7, date('Y'));
		$charges = $this->recurring_model->GetChargesByDate($next_week);
		if($charges) {
			foreach($charges as $charge) {
				if (TriggerTrip('subscription_autorecur_in_week', false, $charge['subscription_id'])) {
					$sent_emails['subscription_autorecur_in_week'][] = $charge['subscription_id'];
				}
			}		
		}
		
		// Get all the recurring charge emails to send in one month
		$sent_emails['recurring_autorecur_in_month'] = array();
		$next_month = mktime(0,0,0, date('m') + 1, date('d'), date('Y'));
		$charges = $this->recurring_model->GetChargesByDate($next_month);
		if($charges) {
			foreach($charges as $charge) {
				if (TriggerTrip('subscription_autorecur_in_month', false, $charge['subscription_id'])) {
					$sent_emails['subscription_autorecur_in_month'][] = $charge['subscription_id'];
				}
			}		
		}

		// Get all the recurring expiration emails to send in one week
		$sent_emails['recurring_expiring_in_week'] = array();
		$charges = $this->recurring_model->GetChargesByExpiryDate($next_week);
		if($charges) {
			foreach($charges as $charge) {
				if (TriggerTrip('subscription_expiring_in_week', false, $charge['subscription_id'])) {
					$sent_emails['subscription_expiring_in_week'][] = $charge['subscription_id'];
				}
			}		
		}
		
		// Get all the recurring expiration emails to send in one month
		$sent_emails['recurring_expiring_in_month'] = array();
		$charges = $this->recurring_model->GetChargesByExpiryDate($next_month);
		if($charges) {
			foreach($charges as $charge) {
				if (TriggerTrip('subscription_expiring_in_month', false, $charge['subscription_id'])) {
					$sent_emails['subscription_expiring_in_month'][] = $charge['subscription_id'];
				}
			}		
		}
		
		$charge_success = count($charge_success);
		$charge_failure = count($charge_failure);
		$cancelled = count($cancelled);
		$autorecur_week = count($sent_emails['subscription_autorecur_in_week']);
		$autorecur_month = count($sent_emails['subscription_autorecur_in_month']);
		$expire_week = count($sent_emails['subscription_expiring_in_week']);
		$expire_month = count($sent_emails['subscription_expiring_in_month']);
		
		$response = $charge_success." Successful Charges. \n";
		$response .= $charge_failure." Failed Charges. \n";
		$response .= $cancelled." Expired Subscriptions. \n";
		$response .= $autorecur_week." Weekly Charge Reminders Sent. \n";
		$response .= $autorecur_month." Monthly Charge Reminders Sent. \n";
		$response .= $expire_week." Weekly Expiration Reminders Sent. \n";
		$response .= $expire_month." Monthly Expiration Reminders Sent. \n";
		
		echo $response;
	}
}


/* End of file cron.php */
/* Location: ./system/opengateway/controllers/cron.php */