<?php

/**
* Stats Library
*
* Provides a simple interface to interesting site wide statistics.
*
* @author Electric Function, Inc.
* @copyright Electric Function, Inc.
* @package Hero Framework
*
*/

class Stats {
	private $CI;

	function __construct () {
		$this->CI =& get_instance();
	}

	/**
	* Revenue
	*
	* @param date $date_start
	* @param date $date_end (default: TODAY)
	*
	* @return float 
	*/
	public function revenue ($date_start = '24 hours ago', $date_end = FALSE) {
		if ($date_end === FALSE) {
			$date_end = date('Y-m-d H:i:s');
		}
	
		$result = $this->CI->db->select('SUM(amount) AS `revenue`',FALSE)
						   ->from('orders')
						   ->where('timestamp >=',date('Y-m-d H:i:s', strtotime($date_start)))
						   ->where('timestamp <=',date('Y-m-d H:i:s', strtotime($date_end)))
						   ->where('orders.status','1')
						   ->where('orders.refunded','0')
						   ->get();
						   
		return money_format("%!^i", $result->row()->revenue);
	}
	
	/**
	* Revenue by Day
	*
	* @param date $date_start
	* @param date $date_end (default: TODAY)
	*
	* @return float 
	*/
	public function revenue_by_day ($date_start = '24 hours ago', $date_end = FALSE) {
		if ($date_end === FALSE) {
			$date_end = date('Y-m-d H:i:s');
		}
	
		$result = $this->CI->db->select('SUM(amount) AS `revenue`',FALSE)
						   ->select('DATE(DATE(timestamp)) as `date`',FALSE)
						   ->from('orders')
						   ->where('timestamp >=',date('Y-m-d H:i:s', strtotime($date_start)))
						   ->where('timestamp <=',date('Y-m-d H:i:s', strtotime($date_end)))
						   ->where('orders.status','1')
						   ->where('orders.refunded','0')
						   ->group_by('DATE(orders.timestamp)')
						   ->get();
		
		$days = array();				   
		foreach ($result->result_array() as $day) {
			$days[$day['date']] = money_format("%!^i", $day['revenue']);
		}
		
		$days = $this->complete_days($days, $date_start, $date_end);
		
		return $days;
	}
	
	/**
	* Product Orders
	*
	* @param date $date_start
	* @param date $date_end (default: TODAY)
	*
	* @return int 
	*/
	public function orders ($date_start = '24 hours ago', $date_end = FALSE) {
		if ($date_end === FALSE) {
			$date_end = date('Y-m-d H:i:s');
		}
	
		$result = $this->CI->db->select('COUNT(`order_details_id`) AS `order_count`',FALSE)
						   ->from('order_details')
						   ->join('orders','orders.order_id = order_details.order_id')
						   ->where('timestamp >=',date('Y-m-d H:i:s', strtotime($date_start)))
						   ->where('timestamp <=',date('Y-m-d H:i:s', strtotime($date_end)))
						   ->where('orders.status','1')
						   ->where('orders.refunded','0')
						   ->get();
						   
		return $result->row()->order_count;
	}
	
	/**
	* Product Orders by Day
	*
	* @param date $date_start
	* @param date $date_end (default: TODAY)
	*
	* @return int 
	*/
	public function orders_by_day ($date_start = '24 hours ago', $date_end = FALSE) {
		if ($date_end === FALSE) {
			$date_end = date('Y-m-d H:i:s');
		}
	
		$result = $this->CI->db->select('COUNT(`order_details_id`) AS `order_count`',FALSE)
						   ->select('DATE(orders.timestamp) as `date`',FALSE)
						   ->from('order_details')
						   ->join('orders','orders.order_id = order_details.order_id')
						   ->where('timestamp >=',date('Y-m-d H:i:s', strtotime($date_start)))
						   ->where('timestamp <=',date('Y-m-d H:i:s', strtotime($date_end)))
						   ->where('orders.status','1')
						   ->where('orders.refunded','0')
						   ->group_by('DATE(orders.timestamp)')
						   ->get();
						   
		$days = array();				   
		foreach ($result->result_array() as $day) {
			$days[$day['date']] = $day['order_count'];
		}
		
		$days = $this->complete_days($days, $date_start, $date_end);
		
		return $days;
	}
	
	/**
	* Subscriptions
	*
	* @param date $date_start
	* @param date $date_end (default: TODAY)
	*
	* @return int 
	*/
	public function subscriptions ($date_start = '24 hours ago', $date_end = FALSE) {
		if ($date_end === FALSE) {
			$date_end = date('Y-m-d H:i:s');
		}
	
		$result = $this->CI->db->select('COUNT(`subscription_id`) AS `subscription_count`',FALSE)
						   ->from('subscriptions')
						   ->where('start_date >=',date('Y-m-d H:i:s', strtotime($date_start)))
						   ->where('start_date <=',date('Y-m-d H:i:s', strtotime($date_end)))
						   ->get();
						   
		return $result->row()->subscription_count;
	}
	
	/**
	* Subscriptions by Day
	*
	* @param date $date_start
	* @param date $date_end (default: TODAY)
	*
	* @return int 
	*/
	public function subscriptions_by_day ($date_start = '24 hours ago', $date_end = FALSE) {
		if ($date_end === FALSE) {
			$date_end = date('Y-m-d H:i:s');
		}
	
		$result = $this->CI->db->select('COUNT(`subscription_id`) AS `subscription_count`',FALSE)
						   ->select('DATE(start_date) as `date`',FALSE)
						   ->from('subscriptions')
						   ->where('start_date >=',date('Y-m-d H:i:s', strtotime($date_start)))
						   ->where('start_date <=',date('Y-m-d H:i:s', strtotime($date_end)))
						   ->group_by('DATE(start_date)')
						   ->get();
						   
		$days = array();				   
		foreach ($result->result_array() as $day) {
			$days[$day['date']] = $day['subscription_count'];
		}
		
		$days = $this->complete_days($days, $date_start, $date_end);
		
		return $days;
	}
	
	/**
	* Registrations
	*
	* @param date $date_start
	* @param date $date_end (default: TODAY)
	*
	* @return int 
	*/
	public function registrations ($date_start = '24 hours ago', $date_end = FALSE) {
		if ($date_end === FALSE) {
			$date_end = date('Y-m-d H:i:s');
		}
	
		$result = $this->CI->db->select('COUNT(`user_id`) AS `member_count`',FALSE)
						   ->from('users')
						   ->where('user_signup_date >=',date('Y-m-d H:i:s', strtotime($date_start)))
						   ->where('user_signup_date <=',date('Y-m-d H:i:s', strtotime($date_end)))
						   ->get();
						   
		return $result->row()->member_count;
	}
	
	/**
	* Registrations by Day
	*
	* @param date $date_start
	* @param date $date_end (default: TODAY)
	*
	* @return int 
	*/
	public function registrations_by_day ($date_start = '24 hours ago', $date_end = FALSE) {
		if ($date_end === FALSE) {
			$date_end = date('Y-m-d H:i:s');
		}
	
		$result = $this->CI->db->select('COUNT(`user_id`) AS `member_count`',FALSE)
						   ->select('DATE(user_signup_date) as `date`',FALSE)
						   ->from('users')
						   ->where('user_signup_date >=',date('Y-m-d H:i:s', strtotime($date_start)))
						   ->where('user_signup_date <=',date('Y-m-d H:i:s', strtotime($date_end)))
						   ->group_by('DATE(user_signup_date)')
						   ->get();
						   
		$days = array();				   
		foreach ($result->result_array() as $day) {
			$days[$day['date']] = $day['member_count'];
		}
		
		$days = $this->complete_days($days, $date_start, $date_end);
		
		return $days;
	}
	
	/**
	* Logins
	*
	* @param date $date_start
	* @param date $date_end (default: TODAY)
	*
	* @return int 
	*/
	public function logins ($date_start = '24 hours ago', $date_end = FALSE) {
		if ($date_end === FALSE) {
			$date_end = date('Y-m-d H:i:s');
		}
	
		$result = $this->CI->db->select('COUNT(`user_id`) AS `member_count`',FALSE)
						   ->from('user_logins')
						   ->where('user_login_date >=',date('Y-m-d H:i:s', strtotime($date_start)))
						   ->where('user_login_date <=',date('Y-m-d H:i:s', strtotime($date_end)))
						   ->get();
						   
		return $result->row()->member_count;
	}
	
	/**
	* Logins by Day
	*
	* @param date $date_start
	* @param date $date_end (default: TODAY)
	*
	* @return int 
	*/
	public function logins_by_day ($date_start = '24 hours ago', $date_end = FALSE) {
		if ($date_end === FALSE) {
			$date_end = date('Y-m-d H:i:s');
		}
	
		$result = $this->CI->db->select('COUNT(`user_id`) AS `member_count`',FALSE)
						   ->select('DATE(user_login_date) as `date`')
						   ->from('user_logins')
						   ->where('user_login_date >=',date('Y-m-d H:i:s', strtotime($date_start)))
						   ->where('user_login_date <=',date('Y-m-d H:i:s', strtotime($date_end)))
						   ->group_by('DATE(user_login_date)')
						   ->get();
						   
		$days = array();				   
		foreach ($result->result_array() as $day) {
			$days[$day['date']] = $day['member_count'];
		}
		
		$days = $this->complete_days($days, $date_start, $date_end);
		
		return $days;
	}
	
	private function complete_days ($days, $date_start, $date_end) {
		$date_start = strtotime($date_start);
		$date_end = strtotime($date_end);
		
		$new_days = array();
		
		for ($i=$date_start;$i<=$date_end;$i = $i+(60*60*25)) {
			$date = date('Y-m-d', $i);
			if (!isset($days[$date])) {
				$days[$date] = 0;
			}
			
			$new_days[] = (!isset($days[$date])) ? 0 : $days[$date];
		}
		
		return $new_days;
	}
}