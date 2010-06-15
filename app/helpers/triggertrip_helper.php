<?php

function TriggerTrip($trigger_type, $charge_id = false, $subscription_id = false, $customer_id = false)
{
	$CI =& get_instance();
	$CI->load->model('email_model');
	
	// get trigger ID
	$trigger_type_id = $CI->email_model->GetTriggerId($trigger_type);
	
	if (!$trigger_type_id) {
		return FALSE;
	}
	
	// load all available data
    if ($subscription_id) {
    	$CI->load->model('recurring_model');
    	$subscription = $CI->recurring_model->GetRecurring($subscription_id);
    }
    
    if ($charge_id) {
    	$CI->load->model('charge_model');
    	$charge = $CI->charge_model->GetCharge($charge_id);
    }
    
    if ($customer_id) {
    	$CI->load->model('customer_model');
    	$customer = $CI->customer_model->GetCustomer($customer_id);
    }
    
    // dynamically get customer information for charge-related trips to save on SQL queries
    if (isset($subscription) and is_array($subscription['customer'])) {
    	$customer = $subscription['customer'];
    }
    elseif (isset($charge) and isset($charge['customer']) and is_array($charge['customer'])) {
    	$customer = $charge['customer'];
    }
    
    // dynamically get plan-related info for recurring-related stuff
    if ($subscription_id and isset($subscription['plan'])) {
    	if (is_array($subscription['plan'])) {
    		$plan = $subscription['plan'];
    		$plan_id = $plan['id'];
    	}
    }
    
    if (!isset($plan_id)) {
    	$plan_id = false;
    }
    
    // build array of all possible variables, if they exist
	$variables = array();
	
	if (isset($charge) and is_array($charge)) {
		$variables['amount'] = $charge['amount'];
		$variables['date'] = date("Y-m-d h:i");
		$variables['charge_id'] = $charge['id'];
		$variables['card_last_four'] = $charge['card_last_four'];
	}
	
	if (isset($subscription) and is_array($subscription)) {
		$variables['recurring_id'] = $subscription['id'];
		$variables['start_date'] = $subscription['start_date'];
		$variables['end_date'] = $subscription['end_date'];
		$variables['expiry_date'] = $subscription['end_date'];
		$variables['next_charge_date'] = $subscription['next_charge_date'];
		
		if (isset($plan) and is_array($plan)) {
			$variables['plan_id'] = $plan['id'];
			$variables['plan_name'] = $plan['name'];
		}
	}
	
	if (isset($customer) and is_array($customer)) {
		$variables['customer_id'] = $customer['id'];
		$variables['customer_first_name'] = $customer['first_name'];
		$variables['customer_last_name'] = $customer['last_name'];
		$variables['customer_internal_id'] = $customer['internal_id'];
		$variables['customer_company'] = $customer['company'];
		$variables['customer_address_1'] = $customer['address_1'];
		$variables['customer_address_2'] = $customer['address_2'];
		$variables['customer_city'] = $customer['city'];
		$variables['customer_state'] = $customer['state'];
		$variables['customer_postal_code'] = $customer['postal_code'];
		$variables['customer_country'] = $customer['country'];
		$variables['customer_email'] = $customer['email'];
		$variables['customer_phone'] = $customer['phone'];
	}
	
	// which events should go in the client log?
	$loggable = array(1,2,3,4,9,10);
	
	$site_email = $CI->config->item('site_email');
	$secret_key = $CI->config->item('secret_key'); // for notification security
	
	// notification_url needs triggering too, if it exists
    if (isset($plan) and is_array($plan)) {
    	if (!empty($plan['notification_url'])) {
    		$CI->load->library('notifications');
    		
    		// build var array
    		$array = array(
    					'action' => $trigger_type,
    					'secret_key' => $secret_key
    				);
    		
    		if (isset($variables['plan_id'])) {
    			$array['plan_id'] = $variables['plan_id'];
    		}
    		if (isset($variables['customer_id'])) {
    			$array['customer_id'] = $variables['customer_id'];
    		}
    		if (isset($variables['charge_id'])) {
    			$array['charge_id'] = $variables['charge_id'];
    		}
    		if (isset($variables['recurring_id'])) {
    			$array['recurring_id'] = $variables['recurring_id'];
    		}
    			
    		$CI->notifications->QueueNotification($plan['notification_url'],$array);
    	}
    }
	
    // check to see if this triggers any emails for the client
	$emails = $CI->email_model->GetEmailsByTrigger($trigger_type_id, $plan_id);
	
	if(!$emails) {
		return FALSE;
	}	
	
	// load validation
	$CI->load->library('field_validation');
	
	$email_count = 0;
	
	foreach ($emails as $email) {		
		// is this HTML?
		$config['mailtype'] = ($email['is_html'] == '1') ? 'html' : 'text';
		$config['wordwrap'] = ($email['is_html'] == '1') ? FALSE : TRUE;
		$CI->email->initialize($config);
		
		// who is this going to?
		$to_address = false;
		if ($email['to_address'] == 'customer' and isset($customer['email']) and !empty($customer['email']) and $CI->field_validation->ValidateEmailAddress($customer['email'])) {
			$to_address = $customer['email'];
		}
		elseif ($email['to_address'] == 'client') {
			$to_address = $site_email;
		}
		elseif ($CI->field_validation->ValidateEmailAddress($email['to_address'])) {
			$to_address = $email['to_address'];
		}
		
		if ($to_address) {	
			$subject = $email['email_subject'];
			$body = $email['email_body'];
			$from_name = $email['from_name'];
			$from_email = $email['from_email'];
			
			// replace all possible variables
			while (list($name,$value) = each($variables)) {
				$subject = str_ireplace('[[' . $name . ']]',$value,$subject);
				$body = str_ireplace('[[' . $name . ']]',$value,$body);
			}
			reset($variables);
			
			// send the email
			$CI->email->from($from_email, $from_name);
			$CI->email->to($to_address);
			$CI->email->subject($subject);
			$CI->email->message($body);
			
			$CI->email->send();
			
			// send a BCC?
			$send_bcc = false;
			if (!empty($email['bcc_address'])) {
				if ($email['bcc_address'] == 'client') {
					$send_bcc = $site_email;
				}
				elseif ($CI->field_validation->ValidateEmailAddress($email['bcc_address'])) {
					$send_bcc = $email['bcc_address'];
				}
			}
			
			if ($send_bcc != false) {
				$CI->email->from($from_email, $from_name);
				$CI->email->to($send_bcc);
				$CI->email->subject($subject);
				$CI->email->message($body);
				$CI->email->send();
			}
		}
		
		$email_count++;		
	}
	
	return $email_count;
}