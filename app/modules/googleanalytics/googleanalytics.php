<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
* Google Analytics Module Definition
*
* Declares the module, update code, etc.
*
* @author Electric Function, Inc.
* @copyright Electric Function, Inc.
* @package Hero Framework
*
*/

class Googleanalytics extends Module {
	var $version = '1.00';
	var $name = 'googleanalytics';

	function __construct () {
		// set the active module
		$this->active_module = $this->name;
		
		parent::__construct();
	}
	
	function admin_preload ()
	{
		$this->CI->admin_navigation->child_link('configuration',63,'Google Analytics',site_url('admincp/googleanalytics'));
	}
	
	function front_preload () {
		include(APPPATH . 'modules/googleanalytics/template_plugins/outputfilter.googleanalytics.php');
		$this->CI->smarty->registerFilter('output', 'smarty_outputfilter_googleanalytics');
	}
	
	function update ($db_version) {
		if ($db_version < '1.00') {
			$this->CI->settings_model->new_setting(1, 'googleanalytics_id', '', '', 'text','', FALSE, TRUE);
		}
	
		return $this->version;
	}
}