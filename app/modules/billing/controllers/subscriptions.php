<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/*
* Subscriptions Module
*
* Displays a list of all available site subscriptions
*
* @author Electric Function, Inc.
* @copyright Electric Function, Inc.
* @package Hero Framework
*
*/

class Subscriptions extends Front_Controller {
	function __construct() {
		parent::__construct();
	}
	
	function index () {
		$this->load->model('billing/subscription_plan_model');
		$plans = $this->subscription_plan_model->get_plans(array('sort' => 'subscription_plan_id', 'sort_dir' => 'ASC'));
		
		$this->smarty->assign('plans', $plans);
		return $this->smarty->display('subscriptions.thtml');
	}
	
	function renew ($subscription_id) {
		$this->load->model('billing/subscription_model');
		$subscription = $this->subscription_model->get_subscription($subscription_id);
		
		if (empty($subscription)) {
			die(show_error('No subscription exists by that ID.'));
		}
		
		if ($subscription['user_id'] != $this->user_model->get('id')) {
			die(show_error('You do not own the subscription you are trying to renew.'));
		}
		
		// check that plan is still active
		$this->load->model('billing/subscription_plan_model');
		$plan = $this->subscription_plan_model->get_plan($subscription['plan_id']);
		
		if (empty($plan)) {
			die(show_error('The plan you are trying to renew is no longer available.'));
		}
		
		// let's renew!
		
		$this->load->model('store/cart_model');
		$this->cart_model->add_subscription_to_cart($subscription['plan_id'], $subscription['id']);
		
		return redirect('checkout');
	}
	
	function upgrade ($subscription_id, $new_plan_id) {
		$this->load->model('billing/subscription_model');
		$subscription = $this->subscription_model->get_subscription($subscription_id);
		
		if (empty($subscription)) {
			die(show_error('No subscription exists by that ID.'));
		}
		
		if ($subscription['user_id'] != $this->user_model->get('id')) {
			die(show_error('You do not own the subscription you are trying to renew.'));
		}
		
		// check that plan is active
		$this->load->model('billing/subscription_plan_model');
		$plan = $this->subscription_plan_model->get_plan($new_plan_id);
		
		if (empty($plan)) {
			die(show_error('The plan you are trying to upgrade to is not available.'));
		}
		
		// let's upgrade!
		
		$this->load->model('store/cart_model');
		$this->cart_model->add_subscription_to_cart($plan['id'], $subscription['id']);
		
		return redirect('checkout');
	}
	
	function add_to_cart ($subscription_plan_id) {
		$this->load->model('billing/subscription_plan_model');
		$plan = $this->subscription_plan_model->get_plan($subscription_plan_id);
		
		if (empty($plan)) {
			die(show_error('You have selected an invalid subscription plan.'));
		}
		
		$this->load->model('store/cart_model');
		$this->cart_model->add_subscription_to_cart($plan['id']);
		
		return redirect('store/cart');
	}
}
