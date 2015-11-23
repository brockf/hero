<?php

/**
* PayPal Fix
*
* Query PayPal to update all next_charge_dates for PayPal subscriptions.  This is related to a problem
* in our previous handling of PayPal subs that kept dates from being perfectly in sync.
*
* @copyright Electric Function, Inc.
* @author Electric Function, Inc.
* @package Hero Framework
*/

class Paypal_fix {
	private $gateways;
	private $CI;

	function __construct ()
	{
		$this->CI =& get_instance();
	}
	
	function hook_cron ()
	{	
		cron_log('Beginning the PayPal charge date fixes.');
		
		// only run once at 5am
		if (date('H') != 5) {
			cron_log('Cronjob should only run once between 5:03 and 5:11.  Exiting (1).');
			
			return TRUE;
		}
		
		if (date('i') < 3 or date('i') > 11) {
			cron_log('Cronjob should only run once between 5:03 and 5:11.  Exiting (2).');
		
			return TRUE;
		}
	
		set_time_limit(0);
	
		$this->CI->db->select('*');
		$this->CI->db->from('subscriptions');
		$this->CI->db->join('gateways','gateways.gateway_id = subscriptions.gateway_id','inner');
		$this->CI->db->join('external_apis','external_apis.external_api_id = gateways.external_api_id','inner');
		$this->CI->db->where('(`external_apis`.`name` = \'paypal\' or `external_apis`.`name` = \'paypal_standard\')',NULL,FALSE);
		$this->CI->db->where('subscriptions.active','1');
		$this->CI->db->where('subscriptions.next_charge <',date('Y-m-d', strtotime('now +4 days')));
		$this->CI->db->where('subscriptions.next_charge >',date('Y-m-d', strtotime('tomorrow')));
		$result = $this->CI->db->get();
		
		$updated = 0;
		
		if ($result->num_rows() > 0) {
			foreach ($result->result_array() as $sub) {
				$profile = $this->_get_profile($sub['gateway_id'], $sub['api_customer_reference']);

				if (isset($profile['NEXTBILLINGDATE']) and !empty($profile['NEXTBILLINGDATE'])) {
					$paypal_date = $profile['NEXTBILLINGDATE'];
					$local_date = $sub['next_charge'];
					$formatted_paypal_date = date('Y-m-d', strtotime($profile['NEXTBILLINGDATE']));
					
					// update local
					$this->CI->db->update('subscriptions', array('next_charge' => $formatted_paypal_date), array('subscription_id' => $sub['subscription_id']));
					
					$updated++;
				}
			}
		}
		
		cron_log('Fixed PayPal subscription next_charge dates: ' . $updated);
		
		return TRUE;
	}
	
	function _get_profile ($gateway_id, $profile_id)
	{
		if (isset($this->gateways[$gateway_id])) {
			$gateway = $this->gateways[$gateway_id];
		}
		else {			
			$gateway = $this->_get_gateway_details($gateway_id);
			$this->gateways[$gateway_id] = $gateway;
		}
		
		$post_url = $this->_get_api_url($gateway);
		
		$post = array();
		$post['version'] = '60';
		$post['method'] = 'GetRecurringPaymentsProfileDetails';
		$post['user'] = $gateway['user'];
		$post['pwd'] = $gateway['pwd'];
		$post['signature'] = $gateway['signature'];
		$post['profileid'] = $profile_id;
		
		$post_response = $this->_process($post_url, $post);
		
		if ($post_response['ACK'] == 'Success') {
			return $post_response;
		} else {
			return FALSE;
		}
	}
	
	private function _get_gateway_details ($gateway_id) {
		$this->CI->load->library('encrypt');
	
		$this->CI->db->where('gateway_id',$gateway_id);
		$result = $this->CI->db->get('gateway_params');
		
		$data = array();
		foreach ($result->result_array() as $item) {
			$data[$item['field']] = $this->CI->encrypt->decode($item['value']);
		}
		
		$this->CI->db->where('gateway_id', $gateway_id);
		$result = $this->CI->db->get('gateways');

		$gateway = $result->row_array();
		
		$this->CI->db->where('external_api_id', $gateway['external_api_id']);
		$result = $this->CI->db->get('external_apis');
		
		$external_api = $result->row_array();
		
		foreach ($external_api as $item => $value) {
			$data[$item] = $value;
		}
		
		return $data;
	}
	
	private function _get_api_url ($gateway) {
		if ($gateway['mode'] == 'test') {
			return $gateway['test_url'];
		}
		else {
			return $gateway['prod_url'];
		}
	}
	
	private function _process ($url, $post_data)
	{
		$data = '';

		foreach ($post_data as $key => $value) {
			if (!empty($value)) {
				$data .= strtoupper($key) . '=' . urlencode(trim($value)) . '&';
			}
		}

		// remove the extra ampersand
		$data = substr($data, 0, strlen($data) - 1);
		
		// setting the curl parameters.
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_VERBOSE, 1);
	
		// turning off the server and peer verification(TrustManager Concept).
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
	
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_POST, 1);
	
		// setting the nvpreq as POST FIELD to curl
		curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
	
		// getting response from server
		$response = curl_exec($ch);
		
		// Throw an error if we can't continue. Will help in debugging.
		if (curl_error($ch)) {
			show_error(curl_error($ch));
		}
		
		$response = $this->_response_to_array($response);
		
		return $response;
	}
	
	private function _response_to_array($string)
	{
		$string = urldecode($string);
		$pairs = explode('&', $string);
		$values = array();

		foreach($pairs as $pair)
		{
			list($key, $value) = explode('=', $pair);
			$values[$key] = $value;
		}

		return $values;
	}
}