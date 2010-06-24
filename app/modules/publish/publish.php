<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
* Publish Module Definition
*
* Declares the module, update code, etc.
*
* @author Electric Function, Inc.
* @package Electric Publisher
*
*/

class Publish extends Module {
	var $version = '1.0';
	var $name = 'publish';

	function __construct () {
		// set the active module
		$this->active_module = $this->name;
		
		parent::__construct();
	}

	function admin_preload ()
	{
		$CI =& get_instance();
		
		//$CI->navigation->child_link('configuration',20,'Emails',site_url('admincp/emails'));
	}
	
	function update ($db_version) {
		if ($db_version < 1.0) {								 
			$this->CI->settings_model->make_writeable_folder(setting('path_editor_uploads'));
		}
	
		return $this->version;
	}
}