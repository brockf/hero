<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
* Settings Module Definition
*
* Declares the module, update code, etc.
*
* @author Electric Function, Inc.
* @copyright Electric Function, Inc.
* @package Hero Framework
*
*/

class Settings extends Module {
	var $version = '1.03';
	var $name = 'settings';

	function __construct () {
		// set the active module
		$this->active_module = $this->name;
		
		parent::__construct();
	}
	
	/**
	* Pre-admin function
	*
	* Initiate navigation in control panel
	*/
	function admin_preload ()
	{
		$this->CI->admin_navigation->child_link('configuration',10,'Settings',site_url('admincp/settings'));
		$this->CI->admin_navigation->child_link('configuration',110,'Modules',site_url('admincp/settings/modules'));
	}
}