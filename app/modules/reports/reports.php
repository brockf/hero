<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
* Reports Module Definition
*
* Declares the module, update code, etc.
*
* @author Electric Function, Inc.
* @copyright Electric Function, Inc.
* @package Electric Publisher
*
*/

class Reports_module extends Module {
	var $version = '1.0';
	var $name = 'reports';

	function __construct () {
		// set the active module
		$this->active_module = $this->name;
		
		parent::__construct();
	}
	
	/*
	* Pre-admin function
	*
	* Initiate navigation in control panel
	*/
	function admin_preload ()
	{
		$this->CI->navigation->child_link('reports',10,'Invoices',site_url('admincp/reports/invoices'));
		$this->CI->navigation->child_link('reports',20,'Product Orders',site_url('admincp/reports/products'));
		//$this->CI->navigation->child_link('reports',25,'Refunds',site_url('admincp/reports/refunds'));
		$this->CI->navigation->child_link('reports',30,'Subscriptions',site_url('admincp/reports/subscriptions'));
		$this->CI->navigation->child_link('reports',40,'Cancellations',site_url('admincp/reports/cancellations'));
		$this->CI->navigation->child_link('reports',50,'Expirations',site_url('admincp/reports/expirations'));
		$this->CI->navigation->child_link('reports',60,'Taxes Received',site_url('admincp/reports/taxes'));
		$this->CI->navigation->child_link('reports',70,'Registrations',site_url('admincp/reports/registrations'));
	}
}