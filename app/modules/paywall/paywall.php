<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
* Paywall Module Definition
*
* Declares the module, update code, etc.
*
* @author Electric Function, Inc.
* @copyright Electric Function, Inc.
* @package Hero Framework
*
*/

class Paywall_module extends Module {
	var $version = '1.0';
	var $name = 'paywall';

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
		$this->CI->admin_navigation->child_link('configuration',36,'Paywall',site_url('admincp/paywall'));
	}
	
	function front_preload () {
		$this->CI->smarty->addPluginsDir(APPPATH . 'modules/paywall/template_plugins/');
	}
	
	/*
	* Module update
	*
	* @param int $db_version The current DB version
	*
	* @return int The current software version, to update the database
	*/
	function update ($db_version) {
		if ($db_version < '1.00') {
			// initial install
			$this->CI->settings_model->new_setting(4, 'paywall_auto', '1', 'Turn on to automatically redirect users to your paywall when they hit a restricted access point.', 'toggle', 'a:2:{i:0;s:3:"Off";i:1;s:2:"On";}', FALSE, TRUE);
			$this->CI->settings_model->new_setting(4, 'paywall_template', 'paywall.thtml', 'If using an auto-paywall, what template file should be loaded at the paywall?', 'text', '', FALSE, TRUE);	
		}
		
		return $this->version;
	}
}