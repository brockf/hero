<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
* Modules Module Definition
*
* Declares the module, update code, etc.
*
* @author Electric Function, Inc.
* @copyright Electric Function, Inc.
* @package Electric Framework
*
*/

class Modules_module extends Module {
	var $version = '1.0';
	var $name = 'modules';

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
		$this->CI->admin_navigation->child_link('publish',99,'Modules',site_url('admincp/modules'));
	}
}
	