<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
* Gateways Module Definition
*
* Declares the module, update code, etc.
*
* @author Electric Function, Inc.
* @package Electric Publisher
*
*/

class Gateways extends Module {
	var $version = '1.0';
	var $name = 'gateways';

	function __construct () {
		// set the active module
		$this->active_module = $this->name;
		
		parent::__construct();
	}

	function admin_preload ()
	{
		$CI =& get_instance();
		
		$CI->navigation->child_link('configuration',30,'Payment Gateways',site_url('admincp/gateways'));
	}
}