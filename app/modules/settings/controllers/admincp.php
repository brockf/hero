<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Admincp extends Admincp_Controller {
	function __construct()
	{
		parent::__construct();
	}

	public function initialize (&$admin)
	{
		$admin->navigation['Settings'] = 'settings';
		$admin->navigation['Add Setting'] = 'settings/add';
	}
	
	function index ()
	{
	
		$this->load->model('billing/customer_model');
		
		
		var_dump($this->customer_model->NewCustomer(array('first_name' => 'test', 'last_name' => 'test')));
	
		$this->load->view('settings/transactions.php');
	}
}