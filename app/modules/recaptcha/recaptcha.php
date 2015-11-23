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

class Recaptcha extends Module {
	var $version = '2.00';
	var $name = 'recaptcha';

	function __construct () {
		// set the active module
		$this->active_module = $this->name;
		
		parent::__construct();
	}
	
	function admin_preload ()
	{
		$this->CI->admin_navigation->child_link('configuration',99,'Recaptcha 2',site_url('admincp/recaptcha'));
	}
	
	function front_preload () {
		//include(APPPATH . 'modules/recaptcha/template_plugins/function.recaptcha.php');
		//$this->CI->smarty->registerFilter('output', 'smarty_function_recaptcha');
		$this->CI->smarty->addPluginsDir(APPPATH . 'modules/recaptcha/template_plugins/');
	}
	
	function update ($db_version) {
		if ($db_version < '1.00') {
			$this->CI->settings_model->new_setting(1, 'recaptcha_site_key', '', 'Enter your Recaptcha Site Key in order to use Recaptcha to prevent spam', 'text','', FALSE, TRUE);
			$this->CI->settings_model->new_setting(1, 'recaptcha_secret_key', '', 'Enter your Recaptcha Secret Key in order to use Recaptcha to prevent spam', 'text','', FALSE, TRUE);
		}
	
		return $this->version;
	}
}