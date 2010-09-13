<?php
/**
* Jerry Sub Purchase Controller 
*
* Automatically forward the user to checkout
*
* @version 1.0
* @author Electric Function, Inc.
* @package Electric Publisher
*/
class Jerrysubpurchase extends Front_Controller {
	function __construct ()
	{
		parent::__construct();
	}
	
	function index ($plan_id) {
		$this->load->model('store/cart_model');
		$this->cart_model->add_subscription_to_cart($plan_id);
		
		return redirect('checkout');
	}
}