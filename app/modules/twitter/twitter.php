<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
* Twitter Module Definition
*
* Declares the module, update code, etc.
*
* @author Electric Function, Inc.
* @copyright Electric Function, Inc.
* @package Electric Framework
*
*/

class Twitter_module extends Module {
	public $version = '1.10';
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
		$this->CI->admin_navigation->child_link('configuration',60,'Twitter',site_url('admincp/twitter'));
		$this->CI->admin_navigation->child_link('reports',91,'Tweets Sent',site_url('admincp/twitter/tweet_logs'));
	}
	
	/*
	* Module update
	*
	* @param int $db_version The current DB version
	*
	* @return int The current software version, to update the database
	*/
	function update ($db_version) {
		if ($db_version < '1.01') {
			$this->CI->db->query('CREATE TABLE `tweets_sent` (
 								 `tweet_id` int(11) NOT NULL auto_increment,
 								 `tweet` varchar(255) NOT NULL,
  								 `sent_time` DATETIME,
  								 `content_id` int(11) NOT NULL,
 								 `content_type` int(11) NOT NULL,
 								 PRIMARY KEY  (`tweet_id`)
								 ) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;');
		}
		if($db_version < '1.01'){
			// initial install
			$this->CI->settings_model->new_setting(1, 'bitly_id', '', '', 'text','', FALSE, FALSE);
			$this->CI->settings_model->new_setting(1, 'bitly_api_key', '', '', 'text','', FALSE, FALSE);
		}
		if ($db_version < '1.01') {
			
			// initial install
			$this->CI->settings_model->new_setting(1, 'twitter_consumer_key', '', '', 'text','', FALSE, TRUE);
			$this->CI->settings_model->new_setting(1, 'twitter_consumer_secret', '', '', 'text','', FALSE, TRUE);
			$this->CI->settings_model->new_setting(1, 'twitter_oauth_token', '', '', 'text','', FALSE, TRUE);
			$this->CI->settings_model->new_setting(1, 'twitter_oauth_token_secret', '', '', 'text','', FALSE, TRUE);
			$this->CI->settings_model->new_setting(1, 'twitter_content_types', '', '', 'text','', FALSE, TRUE);
			$this->CI->settings_model->new_setting(1, 'twitter_topics', '', '', 'text','', FALSE, TRUE);
			$this->CI->settings_model->new_setting(1, 'twitter_template', '', '', 'text','', FALSE, TRUE);
			$this->CI->settings_model->new_setting(1, 'twitter_enabled', '0', '', 'text','', FALSE, TRUE);
			$this->CI->settings_model->new_setting(1, 'twitter_last_tweet', '', '', 'text','', FALSE, TRUE);
		}
		
		if ($db_version < '1.01') {
			$this->CI->app_hooks->bind('cron','Twitter_model','hook_cron',APPPATH . 'modules/twitter/models/twitter_model.php');
		}
								
		// return current version
		return $this->version;
	}
}