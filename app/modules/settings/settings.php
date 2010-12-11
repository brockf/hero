<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
* Settings Module Definition
*
* Declares the module, update code, etc.
*
* @author Electric Function, Inc.
* @copyright Electric Function, Inc.
* @package Electric Framework
*
*/

class Settings extends Module {
	var $version = '1.0';
	var $name = 'settings';

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
		$this->CI->navigation->child_link('configuration',10,'Settings',site_url('admincp/settings'));
	}
}