<?php

/**
* Admincp Default Controller 
*
* Shows the admin homepage
*
* @version 1.0
* @author Electric Function, Inc.
* @package Electric Framework

*/

class Dashboard extends Admincp_Controller {
	function __construct() {
		parent::__construct();
		
		$this->navigation->parent_active('dashboard');
	}
	
	function index() {
		
	
		$this->load->view('cp/dashboard');
	}
}