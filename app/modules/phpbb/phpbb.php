<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
* phpBB3 Module Definition
*
* Declares the module, update code, etc.
*
* @author Electric Function, Inc.
* @copyright Electric Function, Inc.
* @package Electric Framework
*
*/

class Phpbb_module extends Module {
	public $version = '1.01';
	public $name = 'phpbb';

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
		$this->CI->admin_navigation->child_link('configuration',65,'phpBB3',site_url('admincp/phpbb'));
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
			$this->CI->settings_model->new_setting(1, 'phpbb3_document_root', '', '', 'text','', FALSE, TRUE);
			$this->CI->settings_model->new_setting(1, 'phpbb3_table_prefix', '', '', 'text','', FALSE, TRUE);
			$this->CI->settings_model->new_setting(1, 'phpbb3_group_default', '', '', 'text','', FALSE, TRUE);
			$this->CI->settings_model->new_setting(1, 'phpbb3_groups', '', '', 'text','', FALSE, TRUE);
		}
		
		if ($db_version < '1.01') {
			$this->CI->app_hooks->bind('member_login','Phpbb_functions','hook_login',APPPATH . 'modules/phpbb/libraries/phpbb_functions.php');
			$this->CI->app_hooks->bind('member_logout','Phpbb_functions','hook_logout',APPPATH . 'modules/phpbb/libraries/phpbb_functions.php');
			$this->CI->app_hooks->bind('member_register','Phpbb_functions','hook_register',APPPATH . 'modules/phpbb/libraries/phpbb_functions.php');
			$this->CI->app_hooks->bind('member_change_password','Phpbb_functions','hook_change_password',APPPATH . 'modules/phpbb/libraries/phpbb_functions.php');
			$this->CI->app_hooks->bind('subscription_new','Phpbb_functions','hook_subscribe',APPPATH . 'modules/phpbb/libraries/phpbb_functions.php');
			$this->CI->app_hooks->bind('subscription_expire','Phpbb_functions','hook_expire',APPPATH . 'modules/phpbb/libraries/phpbb_functions.php');
		}
								
		// return current version
		return $this->version;
	}
}