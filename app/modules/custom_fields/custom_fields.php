<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
* Custom Fields Module Definition
*
* Declares the module, update code, etc.
*
* @author Electric Function, Inc.
* @copyright Electric Function, Inc.
* @package Electric Publisher
*
*/

class Custom_fields extends Module {
	var $version = '1.0';
	var $name = 'custom_fields';

	function __construct () {
		// set the active module
		$this->active_module = $this->name;
		
		parent::__construct();
	}
	
	function admin_preload () {
		$this->CI->navigation->child_link('configuration',40,'Custom Fields',site_url('admincp/custom_fields'));
	}
	
	/*
	* Pre-front Method
	*
	* Triggered prior to loading the frontend
	*/
	function front_preload () {
		$this->CI->smarty->addPluginsDir(APPPATH . 'modules/custom_fields/template_plugins/');
	}
	
	/*
	* Module update
	*
	* @param int $db_version The current DB version
	*
	* @return int The current software version, to update the database
	*/
	function update($db_version) {
		if ($db_version < 1.0) {
			$this->CI->settings_model->make_writeable_folder(setting('path_custom_field_uploads'));
		}
		
		return $this->version;
	}
}