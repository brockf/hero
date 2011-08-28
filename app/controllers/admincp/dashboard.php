<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
* Admincp Default Controller 
*
* Display the administration control panel homepage
*
* @copyright Electric Function, Inc.
* @author Electric Function, Inc.
* @package Hero Framework
*/

class Dashboard extends Admincp_Controller {
	function __construct() {
		parent::__construct();
		
		$this->admin_navigation->parent_active('dashboard');
	}
	
	function index() {
		if (module_installed('billing', 'store', 'coupons')) {
			// stats boxes
			$day = array();
			$day_start = '24 hours ago';
			$this->load->library('stats');
			$day['revenue'] = ceil($this->stats->revenue($day_start));
			$day['orders'] = $this->stats->orders($day_start);
			$day['subscriptions'] = $this->stats->subscriptions($day_start);
			$day['registrations'] = $this->stats->registrations($day_start);
			$day['logins'] = $this->stats->logins($day_start);
							   
			$week = array();
			$week_start = '1 week ago';
			$this->load->library('stats');
			$week['revenue'] = ceil($this->stats->revenue($week_start));
			$week['orders'] = $this->stats->orders($week_start);
			$week['subscriptions'] = $this->stats->subscriptions($week_start);
			$week['registrations'] = $this->stats->registrations($week_start);
			$week['logins'] = $this->stats->logins($week_start);
			
			$month = array();
			$month_start = '1 month ago';
			$this->load->library('stats');
			$month['revenue'] = ceil($this->stats->revenue($month_start));
			$month['orders'] = $this->stats->orders($month_start);
			$month['subscriptions'] = $this->stats->subscriptions($month_start);
			$month['registrations'] = $this->stats->registrations($month_start);
			$month['logins'] = $this->stats->logins($month_start);
			
			// stats charts
			$week_by_day = array();
			$week_start = '1 week ago';
			$this->load->library('stats');
			$week_by_day['revenue'] = $this->stats->revenue_by_day($week_start);
			$week_by_day['orders'] = $this->stats->orders_by_day($week_start);
			$week_by_day['subscriptions'] = $this->stats->subscriptions_by_day($week_start);
			$week_by_day['registrations'] = $this->stats->registrations_by_day($week_start);
			$week_by_day['logins'] = $this->stats->logins_by_day($week_start);
			
			$month_by_day = array();
			$month_start = '1 month ago';
			$this->load->library('stats');
			$month_by_day['revenue'] = $this->stats->revenue_by_day($month_start);
			$month_by_day['orders'] = $this->stats->orders_by_day($month_start);
			$month_by_day['subscriptions'] = $this->stats->subscriptions_by_day($month_start);
			$month_by_day['registrations'] = $this->stats->registrations_by_day($month_start);
			$month_by_day['logins'] = $this->stats->logins_by_day($month_start);
		}
		
		// top 3 content types (for quick publish)
		$content_types = $this->db->select('content_type_friendly_name')
								  ->select('content_types.content_type_id')
								  ->select('COUNT(content.content_type_id) AS posts')
								  ->from('content')
								  ->join('content_types','content_types.content_type_id = content.content_type_id')
								  ->group_by('content.content_type_id')
								  ->order_by('COUNT(content.content_type_id)','DESC')
								  ->limit(3)
								  ->get();
		
		$this->load->library('inflect');
		
		$quick_publish = array();
		foreach ($content_types->result_array() as $type) {
			$quick_publish[] = array(
								'name' => $this->inflect->singularize($type['content_type_friendly_name']),
								'link' => site_url('admincp/publish/create_post/' . $type['content_type_id'])
							);
		}
		
		// system stats
		$system = array();
		
		$system['PHP'] = phpversion();
		$system['MySQL'] = mysql_get_server_info();
		$system[$this->config->item('app_name')] = $this->config->item('app_version');
		$system['Theme'] = setting('theme');
		if (defined("_LICENSENUMBER")) {
			$system['License'] = _LICENSENUMBER;
		}
		
		$data = array();
		
		if (isset($day)) {
			$data['day'] = $day;
			$data['week'] = $week;
			$data['month'] = $month;
			$data['week_by_day'] = $week_by_day;
			$data['month_by_day'] = $month_by_day;
		}
		
		$data['quick_publish'] = $quick_publish;
		$data['system'] = $system;

		$this->load->view('cp/dashboard', $data);
	}
	
	function activity () {
		// we'll place everything in here as timestamp => message
		$activity = array();
	
		// get last 10 signups
		$result = $this->db->select('user_id')
						   ->select('user_first_name')
						   ->select('user_last_name')
						   ->select('user_signup_date')
						   ->from('users')
						   ->order_by('user_signup_date','DESC')
						   ->limit(10)
						   ->get();
						   
		foreach ($result->result_array() as $user) {
			$activity[strtotime($user['user_signup_date'])] = '<a href="' . site_url('admincp/users/profile/' . $user['user_id']) . '">' . $user['user_first_name'] . ' ' . $user['user_last_name'] . ' created a member account.</a>';
		}
		
		if (module_installed('store','billing')) {
			// ... orders
			$result = $this->db->select('orders.order_id')
							   ->select('user_first_name')
							   ->select('user_last_name')
							   ->select('orders.amount')
							   ->select('orders.timestamp')
							   ->select('orders.subscription_id')
							   ->from('orders')
							   ->join('users','users.customer_id = orders.customer_id')
							   ->order_by('orders.timestamp','DESC')
							   ->limit(10)
							   ->get();
							   
			foreach ($result->result_array() as $order) {
				$purchase_or_sub = (!empty($order['subscription_id'])) ? ' made a subscription payment of ' : ' made a purchase from the store for ';
				$activity[strtotime($order['timestamp'])] = '<a href="' . site_url('admincp/reports/invoice/' . $order['order_id']) . '">' . $order['user_first_name'] . ' ' . $order['user_last_name'] . $purchase_or_sub . setting('currency_symbol') . money_format("%!^i",$order['amount']) . '.</a>';
			}
		}
		
		// ... logins
		$result = $this->db->select('user_first_name')
						   ->select('user_last_name')
						   ->select('users.user_id')
						   ->select('users.user_username')
						   ->select('user_logins.user_login_date')
						   ->select('user_logins.user_login_ip')
						   ->from('user_logins')
						   ->join('users','users.user_id = user_logins.user_id')
						   ->order_by('user_logins.user_login_date','DESC')
						   ->limit(10)
						   ->get();
						   
		foreach ($result->result_array() as $login) {
			$activity[strtotime($login['user_login_date'])] = '<a href="' . dataset_link('admincp/users/logins', array('username' => $login['user_username'])) . '">' . $login['user_first_name'] . ' ' . $login['user_last_name'] . ' logged in from <span class="tooltip" title="This is an IP address.  It specifies from which computer/network the user logged in.">' . $login['user_login_ip'] . '</span>.</a>';
		}
		
		if (module_installed('billing')) {
			// ... subscriptions
			$result = $this->db->select('subscriptions.subscription_id')
							   ->select('user_first_name')
							   ->select('user_last_name')
							   ->select('users.user_id')
							   ->select('plans.name')
							   ->select('subscriptions.timestamp')
							   ->from('subscriptions')
							   ->join('users','users.customer_id = subscriptions.customer_id')
							   ->join('plans','plans.plan_id = subscriptions.plan_id')
							   ->order_by('subscriptions.timestamp','DESC')
							   ->limit(10)
							   ->get();
							   
			foreach ($result->result_array() as $order) {
				$activity[strtotime($order['timestamp'])] = '<a href="' . site_url('admincp/users/profile/' . $order['user_id']) . '">' . $order['user_first_name'] . ' ' . $order['user_last_name'] . ' subscribed to ' . $order['name'] . '.</a>';
			}
		}
		
		// ... published content
		$result = $this->db->select('content.content_id')
						   ->select('user_first_name')
						   ->select('user_last_name')
						   ->select('content_types.content_type_friendly_name')
						   ->select('content.content_date')
						   ->from('content')
						   ->join('users','users.user_id = content.user_id')
						   ->join('content_types','content.content_type_id = content_types.content_type_id')
						   ->order_by('content.content_date','DESC')
						   ->limit(10)
						   ->get();
						   
		$this->load->library('inflect');
						   
		foreach ($result->result_array() as $content) {
			$activity[strtotime($content['content_date'])] = '<a href="' . site_url('admincp/publish/edit/' . $content['content_id']) . '">' . $content['user_first_name'] . ' ' . $content['user_last_name'] . ' published a new ' . $this->inflect->singularize($content['content_type_friendly_name']) . '.</a>';
		}
		
		// sort and show
		ksort($activity);
		$activity = array_reverse($activity, TRUE);
		
		$this->load->helper('time_since');
		
		$count = 1;
		foreach ($activity as $time => $message) {
			if ($time > time()) {
				// future event, ignore...
				continue;
			}
			
			if ($count <= 20) {
				$date = time_since($time);
				if ($date === FALSE) {
					$date = date('M d, h:ia', $time);
				}
			
				echo '<li><div class="date">' . $date . '</div><div class="message">' . $message . '</div><div style="clear:both"></div></li>';
				$count++;
			}
			else {
				break;
			}
		}
		
		return TRUE;
	}
	
	function activity_time () {
		echo 'Last update: ' . date('h:ia');
	}
}