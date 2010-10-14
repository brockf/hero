<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
* Twitter Module Definition
*
* Declares the module, update code, etc.
*
* @author Electric Function, Inc.
* @copyright Electric Function, Inc.
* @package Electric Publisher
*
*/

class Twitter_module extends Module {
	public $version = '1.01';
	public $name = 'twitter';

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
		$this->CI->navigation->child_link('configuration',60,'Twitter',site_url('admincp/twitter'));
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
			$this->CI->settings_model->new_setting(1, 'twitter_consumer_key', '', '', 'text','');
			$this->CI->settings_model->new_setting(1, 'twitter_consumer_secret', '', '', 'text','');
			$this->CI->settings_model->new_setting(1, 'twitter_oauth_token', '', '', 'text','');
			$this->CI->settings_model->new_setting(1, 'twitter_oauth_token_secret', '', '', 'text','');
			$this->CI->settings_model->new_setting(1, 'twitter_content_types', '', '', 'text','');
			$this->CI->settings_model->new_setting(1, 'twitter_topics', '', '', 'text','');
			$this->CI->settings_model->new_setting(1, 'twitter_template', '', '', 'text','');
			$this->CI->settings_model->new_setting(1, 'twitter_enabled', '0', '', 'text','');
			$this->CI->settings_model->new_setting(1, 'twitter_last_tweet', '', '', 'text','');
		}
		
		if ($db_version < '1.01') {
			$this->CI->app_hooks->bind('cron','Twitter_model','hook_cron',APPPATH . 'modules/twitter/models/twitter_model.php');
		}
								
		// return current version
		return $this->version;
	}
}