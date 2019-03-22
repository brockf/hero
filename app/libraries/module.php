<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
* Module Definition Class
*
* Declares the module, updates code, etc.  Is extended by each individual module
* and __construct() triggers the process
*
* @author Electric Function, Inc.
* @package Hero Framework
*
*/

class Module {
	var $active_module = FALSE;
	var $CI;
	
	function __construct() {
		$this->CI =& get_instance();
		
		if (!empty($this->active_module)) {
			// get the current version
			$version = $this->get_version();
			
			// has this module been installed before?
			if ($version === FALSE) {
				$this->CI->module_model->new_module($this->active_module, '0');
				
				// set the version to 0, we want all updates
				$version = 0;
			}
		
			$this->run_updates($version);
			
			// do we need to preload for the admin panel?
			if (defined("_CONTROLPANEL")) {
				$this->do_admin_preload();
			}
			
			// do we need to preload for the frontend?
			if (defined("_FRONTEND")) {
				$this->do_front_preload();
			}
		}
	}
	
	/*
	* Get Version
	*
	* Gets the current version of the module
	*/
	
	function get_version () {
		if ($module = $this->CI->module_model->get_module($this->active_module)) {
			return $module['version'];
		}
		else {
			return FALSE;
		}
	}
	
	/*
	* Run Updates
	*
	* Checks if there's an update() method to be called and, if so, calls it
	*/
	function run_updates($version) {
		if (method_exists($this, 'update')) {
			$this->CI->load->model('settings/settings_model');
		
			$new_version = $this->update($version);
			
			if ($version != $new_version) {
				$this->CI->module_model->update_version($this->active_module, $new_version);
			}
		}
	}
	
	/*
	* Call Admin Preload
	*
	* Checks if there's an admin_preload() method to be called and, if so, calls it
	*/
	function do_admin_preload () {
	
		if (method_exists($this, 'admin_preload')) {
			$this->admin_preload();
		}
	}
	
	/*
	* Call Frontend Preload
	*
	* Checks if there's an front_preload() method to be called and, if so, calls it
	*/
	function do_front_preload () {
	
		if (method_exists($this, 'front_preload')) {
			$this->front_preload();
		}
	}
}