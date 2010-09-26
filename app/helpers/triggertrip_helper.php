<?php

function TriggerTrip($trigger_type, $charge_id = FALSE, $subscription_id = FALSE, $customer_id = FALSE, $order_id = FALSE, $other_variables = array())
{
	$CI =& get_instance();
	$CI->load->model('emails/email_model');
	
	// get trigger ID
	$trigger_type_id = $CI->email_model->GetTriggerId($trigger_type);
	
	if (!$trigger_type_id) {
		return FALSE;
	}
	
	// load all available data
    if ($subscription_id) {
    	$CI->load->model('billing/recurring_model');
    	$subscription = $CI->recurring_model->GetRecurring($subscription_id);
    	
    	if (isset($subscription['customer']['id'])) {
    		$customer_id = $subscription['customer']['id'];
    	}
    }
    
    if ($charge_id) {
    	$CI->load->model('billing/charge_model');
    	$charge = $CI->charge_model->GetCharge($charge_id);
    	
    	if (isset($charge['customer']['id'])) {
    		$customer_id = $charge['customer']['id'];
    	}
    }
    
    if ($customer_id) {
    	$CI->load->model('billing/customer_model');
    	$customer = $CI->customer_model->GetCustomer($customer_id);
    } 
    
    if ($order_id) {
    	$CI->load->model('store/order_model');
    	$order = $CI->order_model->get_order($order_id);
    }
    
    // get member info via customer
    if (isset($customer['id'])) {
    	$user = $CI->user_model->get_user($customer['internal_id']);
    }
	
	// dynamically get plan-related info for recurring-related stuff
    if ($subscription_id and isset($subscription['plan'])) {
    	if (is_array($subscription['plan'])) {
    		$plan = $subscription['plan'];
    		$plan_id = $plan['id'];
    	}
    }
    
    if (!isset($plan_id)) {
    	$plan_id = FALSE;
    }
    
    // does this trigger have a usergroup move?
    if ($trigger_type == 'new_subscription' and !empty($plan_id) and isset($user)) {
    	$CI->load->model('billing/subscription_plan_model');
    	$sub_plan = $CI->subscription_plan_model->get_plan_from_api_plan_id($plan_id);
    	
    	if (!empty($sub_plan['promotion'])) {
    		$CI->user_model->add_group($user['id'], $sub_plan['promotion']);
    	}
    }
    
    if (($trigger_type == 'subscription_expire') and !empty($plan_id) and isset($user)) {
    	// let's make sure that there isn't an updating or renewing subscription, here
		$CI->load->model('billing/subscription_model');
		$subscription = $CI->subscription_model->get_subscription($subscription['id']);
		
		$move_user_groups = TRUE;
		if (!empty($subscription['renewing_subscription_id']) or !empty($subscription['updating_subscription_id'])) {
			$check_sub_id = (!empty($subscription['renewing_subscription_id'])) ? $subscription['renewing_subscription_id'] : $subscription['updating_subscription_id'];
			
			$check_sub = $CI->subscription_model->get_subscription($check_sub_id);
			
			if ($check_sub['active']) {
				$move_user_groups = FALSE;
				
				// let's end this - we won't even send an email
				return FALSE;
			}
		}
		
		if ($move_user_groups === TRUE) {
	    	$CI->load->model('billing/subscription_plan_model');
	    	$sub_plan = $CI->subscription_plan_model->get_plan_from_api_plan_id($plan_id);
	    	
	    	if (!empty($sub_plan['promotion'])) {
	    		$CI->user_model->remove_group($user['id'], $sub_plan['promotion']);
	    	}
	    	
	    	if (!empty($sub_plan['demotion'])) {
	    		$CI->user_model->add_group($user['id'], $sub_plan['demotion']);
	    	}
	    }
    }
    
    // if it's a recurring payment, we may need to add a taxes received line
	// is this subscription plan taxable?
	if ($trigger_type == 'subscription_charge') {
		// see if there is a subscription tax at play
		$CI->load->model('store/taxes_model');
		if ($tax = $CI->taxes_model->get_tax_for_subscription($subscription['id'])) {
			$CI->taxes_model->record_tax($tax['tax_id'], $charge_id, 0, $tax['tax_amount']);
		}
	}
    
    // build array of all possible variables, if they exist
	$variables = (!empty($other_variables)) ? $other_variables : array();
	
	/* other variables:
	 - download link for downloadable products
	 - product name for downloadable products
	 - password for new registrations
	 - validation_link
	 - validation_code
	 - new_password for forgotten passwords
	 */
	 
	// default variables
	$variables['account_link'] = site_url('account');
	$variables['site_link'] = site_url('');
	$variables['site_name'] = setting('site_name');
	
	if (isset($charge) and is_array($charge)) {
		$variables['amount'] = $charge['amount'];
		$variables['date'] = date("M j, Y \@ h:ia");
		$variables['charge_id'] = $charge['id'];
		$variables['card_last_four'] = $charge['card_last_four'];
	}
	
	if (isset($subscription) and is_array($subscription)) {
		$variables['subscription_id'] = $subscription['id'];
		$variables['subscription_start_date'] = date('M j, Y',strtotime($subscription['start_date']));
		$variables['subscription_end_date'] = date('M j, Y',strtotime($subscription['end_date']));
		$variables['subscription_expiry_date'] = date('M j, Y',strtotime($subscription['end_date']));
		$variables['subscription_next_charge_date'] = date('M j, Y',strtotime($subscription['next_charge_date']));
		$variables['subscription_amount'] = $subscription['amount'];
		
		if (isset($plan) and is_array($plan)) {
			$variables['plan_id'] = $plan['id'];
			$variables['plan_name'] = $plan['name'];
		}
	}
	
	if (isset($customer) and is_array($customer)) {
		$variables['billing_first_name'] = $customer['first_name'];
		$variables['billing_last_name'] = $customer['last_name'];
		$variables['billing_company'] = $customer['company'];
		$variables['billing_address_1'] = $customer['address_1'];
		$variables['billing_address_2'] = $customer['address_2'];
		$variables['billing_city'] = $customer['city'];
		$variables['billing_state'] = $customer['state'];
		$variables['billing_postal_code'] = $customer['postal_code'];
		$variables['billing_country'] = $customer['country'];
	}
	
	if (!empty($order_id) and is_array($order) and is_array($order['shipping'])) {
		// get shipping address
		$variables['shipping_first_name'] = $order['shipping']['first_name'];
		$variables['shipping_last_name'] = $order['shipping']['last_name'];
		$variables['shipping_company'] = $order['shipping']['company'];
		$variables['shipping_address_1'] = $order['shipping']['address_1'];
		$variables['shipping_address_2'] = $order['shipping']['address_2'];
		$variables['shipping_city'] = $order['shipping']['city'];
		$variables['shipping_state'] = $order['shipping']['state'];
		$variables['shipping_postal_code'] = $order['shipping']['postal_code'];
		$variables['shipping_country'] = $order['shipping']['country'];
	}
	
	if (isset($user) and is_array($user)) {
		$variables['member_id'] = $user['id'];
		$variables['member_first_name'] = $user['first_name'];
		$variables['member_last_name'] = $user['last_name'];
		$variables['member_email'] = $user['email'];
		$variables['member_username'] = $user['username'];
	}
		
	$site_email = $CI->config->item('site_email');
	
	/* Legacy Code - May dig up sometime
	
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
    */
	
    // check to see if this triggers any emails for the client
	$emails = $CI->email_model->GetEmailsByTrigger($trigger_type_id, $plan_id);
	
	if (!$emails) {
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
		if ($email['to_address'] == 'user') {
			// which user are we using?
			if (isset($user['email']) and !empty($user['email']) and $CI->field_validation->ValidateEmailAddress($user['email'])) {
				$to_address = $user['email'];
			}
			elseif (isset($customer['email']) and !empty($customer['email']) and $CI->field_validation->ValidateEmailAddress($customer['email'])) {
				$to_address = $customer['email'];
			}
		}
		elseif ($CI->field_validation->ValidateEmailAddress($email['to_address'])) {
			$to_address = $email['to_address'];
		}
		
		if ($to_address) {	
			$subject = $email['email_subject'];
			$body = $email['email_body'];
			
			// do we have a signature
			if (setting('email_signature') != '') {
				$body .= "\n\n" . setting('email_signature');
			}
			
			$from_name = setting('email_name');
			$from_email = setting('site_email');
			
			// make email variables available globally
			$GLOBALS['EMAIL_TRIGGER_VARIABLES'] = serialize($variables);
			
			// replace all possible variables that have parameter
			$body = preg_replace_callback('/\[\[([a-zA-Z_]*?)\|\"(.*?)\"\]\]/i', 'trigger_parse_variable_with_parameter', $body);
			$subject = preg_replace_callback('/\[\[([a-zA-Z_]*?)\|\"(.*?)\"\]\]/i', 'trigger_parse_variable_with_parameter', $subject);
			
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
			$send_bcc = FALSE;
			if (!empty($email['bcc_address'])) {
				if ($email['bcc_address'] == 'site_email') {
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

// replaces a variable that is being modified with a parameter
// for now, this is only date parameters
function trigger_parse_variable_with_parameter ($params) {
	// load $variables array
	$variables = unserialize($GLOBALS['EMAIL_TRIGGER_VARIABLES']);
	
	$variable = $params[1];
	$parameter = $params[2];
	
	$array_key = strtolower($variable);
	
	$return = $variables[$array_key];
	
	// format the date
	// we'll take strftime or date formatting:
	if (strpos($parameter, '%') !== FALSE) {				
		$return = strftime($parameter, strtotime($return));
	}
	else {
		$return = date($parameter, strtotime($return));
	}
	
	return $return;
}