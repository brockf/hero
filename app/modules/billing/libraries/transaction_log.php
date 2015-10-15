<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/*
* Transaction Log Class
*
* Log important billing events so that we have something to debug when issues arise
* with cancellations, etc.
*
* @class Transaction_log
* @author Electric Function, Inc.
* @copyright Electric Function, Inc.
* @package Hero Framework
*
*/
class Transaction_log {
	// global object
	private $CI;
	
	/**
	* Constructor
	*/
	function __construct () {
		$this->CI =& get_instance();
		
		// load user_agent library
		$this->CI->load->library('user_agent');
	}
	
	/**
	* Log Event
	*
	* @param int $order_id
	* @param int $subscription_id
	* @param string $event
	* @param array $data
	* @param string $file
	* @param int $line
	*/
	function log_event ($order_id = 0, $subscription_id = 0, $event = '', $data = array(), $file = '', $line = '') {
		$ip = $this->CI->input->ip_address();
		$browser = $this->CI->agent->agent_string();
		$date = date('Y-m-d H:i:s');
		
		$data = (is_array($data)) ? serialize($data) : $data;
		
		$insert_fields = array(
								'order_id' => (empty($order_id)) ? '0' : $order_id,
								'subscription_id' => (empty($subscription_id)) ? '0' : $subscription_id,
								'log_date' => $date,
								'log_event' => $event,
								'log_data' => (empty($data)) ? '' : $data,
								'log_ip' => $ip,
								'log_browser' => $browser,
								'log_file' => $file,
								'log_line' => $line
							);
		
		$this->CI->db->insert('transaction_log', $insert_fields);
		
		return $this->CI->db->insert_id();
	}
}