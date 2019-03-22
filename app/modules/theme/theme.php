<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
* Theme Module Definition
*
* Declares the module, update code, etc.
*
* @author Electric Function, Inc.
* @copyright Electric Function, Inc.
* @package Hero Framework
*
*/

class Theme extends Module {
	var $version = '1.01';
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
		$this->CI->admin_navigation->child_link('design',10,'Theme Editor',site_url('admincp/theme/editor'));
		$this->CI->admin_navigation->child_link('design',1,'Themes',site_url('admincp/theme'));
	}
	
	function update($db_version) {
		if ($db_version < 1.0) {
			$this->CI->settings_model->new_setting(5, 'theme', '', 'This is the name of the folder in /themes/ in which to access your template files.','text');
			$this->CI->settings_model->new_setting(5, 'frontpage_template', 'frontpage', 'Which template should be loaded when someone access the homepage?','text');
		}
		
		if ($db_version < 1.01) {
			$this->CI->settings_model->make_writeable_folder(FCPATH . 'writeable/templates_compile/');
			$this->CI->settings_model->make_writeable_folder(FCPATH . 'writeable/templates_cache/');
		}
		
		return $this->version;
	}
}