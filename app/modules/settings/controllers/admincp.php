<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Admincp extends Admincp_Controller {
	function __construct()
	{
		parent::__construct();
		
		$this->navigation->parent_active('configuration');
	}

	function index ()
	{
		$this->load->model('billing/customer_model');
		
		$this->load->view('settings.php');
	}
}