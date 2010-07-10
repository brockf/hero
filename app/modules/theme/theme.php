<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
* Theme Module Definition
*
* Declares the module, update code, etc.
*
* @author Electric Function, Inc.
* @copyright Electric Function, Inc.
* @package Electric Publisher
*
*/

class Theme extends Module {
	var $version = '1.0';
	var $name = 'theme';

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
		$CI =& get_instance();
		
		$CI->navigation->child_link('design',10,'Theme Editor',site_url('admincp/theme'));
	}
}