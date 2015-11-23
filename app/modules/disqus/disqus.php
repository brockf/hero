<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
* Disqus Module Definition
*
* Declares the module, update code, etc.
*
* @author Electric Function, Inc.
* @copyright Electric Function, Inc.
* @package Electric Framework
*
*/

class Disqus_module extends Module {
	public $version = '1.0';
	public $name = 'disqus';

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
		$this->CI->admin_navigation->child_link('configuration',65,'Disqus Comments',site_url('admincp/disqus'));
	}
	
	/**
	* Pre-front Method
	*
	* Triggered prior to loading the frontend
	*/
	function front_preload () {
		$this->CI->smarty->addPluginsDir(APPPATH . 'modules/disqus/template_plugins/');
	}
	
	/*
	* Module update
	*
	* @param int $db_version The current DB version
	*
	* @return int The current software version, to update the database
	*/
	function update ($db_version) {
		if ($db_version < '1.0') {
			// initial install
			$this->CI->settings_model->new_setting(1, 'disqus_shortname', '', '', 'text','', FALSE, TRUE);
		}
								
		// return current version
		return $this->version;
	}
}