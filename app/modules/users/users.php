<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
* Users Module Definition
*
* Declares the module, update code, etc.
*
* @author Electric Function, Inc.
* @copyright Electric Function, Inc.
* @package Hero Framework
*
*/

class Users_module extends Module {
	var $version = '1.23';
	var $name = 'users';

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
		$this->CI->admin_navigation->child_link('members',10,'Manage Members',site_url('admincp/users'));
		$this->CI->admin_navigation->child_link('members',20,'Add Member/Administrator',site_url('admincp/users/add'));
		$this->CI->admin_navigation->child_link('members',30,'Login Records',site_url('admincp/users/logins'));
		$this->CI->admin_navigation->child_link('members',40,'Member Groups',site_url('admincp/users/groups'));
	}
	
	/*
	* Pre-front Method
	*
	* Triggered prior to loading the frontend
	*/
	function front_preload () {
		$this->CI->smarty->addPluginsDir(APPPATH . 'modules/users/template_plugins/');
		
		// track user activity
		if ($this->CI->config->item('duplicate_login_check') != 'no') {
			include_once(APPPATH . 'modules/users/template_plugins/outputfilter.user_activity.php');
			$this->CI->smarty->registerFilter('output', 'smarty_outputfilter_user_activity');
		}
	}
	
	/*
	* Module update
	*
	* @param int $db_version The current DB version
	*
	* @return int The current software version, to update the database
	*/
	function update ($db_version) {
		if ($db_version < 1.0) {
			// initial install
			$this->CI->db->query('CREATE TABLE `usergroups` (
								  `usergroup_id` int(11) NOT NULL auto_increment,
								  `usergroup_name` varchar(150) NOT NULL,
								  `usergroup_default` tinyint(4) NULL,
								  PRIMARY KEY  (`usergroup_id`)
								) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;');
								
			$insert_fields = array(
									'usergroup_name' => 'Default',
									'usergroup_default' => '1'
								);					
								
			$this->CI->db->insert('usergroups',$insert_fields);
								
			$this->CI->db->query('CREATE TABLE `users` (
								  `user_id` int(11) NOT NULL auto_increment,
								  `customer_id` int(11) default \'0\',
								  `user_is_admin` tinyint(4) NOT NULL,
								  `user_groups` varchar(255) NOT NULL,
								  `user_first_name` varchar(255) NOT NULL,
								  `user_last_name` varchar(255) NOT NULL,
								  `user_username` varchar(255) NOT NULL,
								  `user_email` varchar(100) NOT NULL,
								  `user_password` varchar(255) NOT NULL,
								  `user_referrer` int(11) default NULL,
								  `user_signup_date` datetime default NULL,
								  `user_last_login` datetime default NULL,
								  `user_suspended` int(11) NOT NULL default \'0\',
								  `user_deleted` int(11) NOT NULL default \'0\',
								  `user_validate_key` varchar(32),
								  `user_remember_key` varchar(32),
								  `user_cart` text,
								  `user_pending_charge_id` int(11),
								  PRIMARY KEY  (`user_id`)
								) ENGINE=MyISAM AUTO_INCREMENT=1001 DEFAULT CHARSET=utf8 AUTO_INCREMENT=1001 ;');
		}
		
		if ($db_version < 1.01) {
			$this->CI->db->query('CREATE TABLE `user_fields` (
								  `user_field_id` int(11) NOT NULL auto_increment,
								  `custom_field_id` int(11) NOT NULL,
								  `subscription_plans` varchar(150) NOT NULL,
								  `products` varchar(150) NOT NULL,
								  `user_field_billing_equiv` varchar(250) NOT NULL,
								  `user_field_admin_only` tinyint(1) NOT NULL,
								  `user_field_registration_form` tinyint(1) NOT NULL,
								  PRIMARY KEY  (`user_field_id`)
								) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;');
		}
		
		if ($db_version < 1.02) {
			$this->CI->settings_model->new_setting(3, 'require_tos', '0', 'Require registering users to agree to your site\'s Terms of Service?', 'toggle', 'a:2:{i:0;s:2:"No";i:1;s:3:"Yes";}');
			$this->CI->settings_model->new_setting(3, 'terms_of_service', 'Enter your terms of service here.', 'If "require_tos" is On, users will be forced to accept these Terms prior to registering.', 'textarea');
		}
		
		if ($db_version < 1.03) {
			$this->CI->settings_model->new_setting(3, 'validate_emails', '0', 'Require registering users to validate their emails by clicking a link in an automated email', 'toggle', 'a:2:{i:0;s:2:"No";i:1;s:3:"Yes";}');
		}
		
		if ($db_version < 1.04) {
			$this->CI->db->query('CREATE TABLE `user_logins` (
								  `user_login_id` int(11) NOT NULL auto_increment,
								  `user_id` int(11) NOT NULL,
								  `user_login_date` DATETIME NOT NULL,
								  `user_login_ip` varchar(50) NOT NULL,
								  `user_login_browser` varchar(255) NOT NULL,
								  PRIMARY KEY  (`user_login_id`)
								) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1000 ;');
		}
		
		if ($db_version < 1.05) {
			$this->CI->settings_model->new_setting(3, 'registration_redirect', 'users/', 'Redirect to this address after a user registers.  Can be an absolute or relative URL.', 'text', '');
			$this->CI->settings_model->new_setting(3, 'show_subscriptions', '1', 'After a registration, should we redirect to subscription packages (if they exist)?  If this redirect doesn\'t happen, the "registration_redirect" setting will be used.', 'toggle', 'a:2:{i:0;s:2:"No";i:1;s:3:"Yes";}');
		}
		
		if ($db_version < 1.06) {
			$this->CI->load->library('app_hooks');
		
			$this->CI->app_hooks->register('member_register','A new member account is created.',array('member'),array('password'));
			$this->CI->app_hooks->register('member_validate_email','A member must validate their email address after registration.',array('member'),array('validation_link','validation_code'));
			$this->CI->app_hooks->register('member_forgot_password','A member requests a new password via the "Forgot Password" feature.',array('member'),array('new_password'));
		}
		
		if ($db_version < 1.07) {
			$this->CI->load->library('app_hooks');
			
			$this->CI->app_hooks->register('member_suspend','A member account is suspended.',array('member'));
			$this->CI->app_hooks->register('member_unsuspend','A member account is unsuspended.',array('member'));
			$this->CI->app_hooks->register('member_delete','A member account is deleted.',array('member'));
		}
		
		if ($db_version < 1.08) {
			$this->CI->load->library('app_hooks');
			
			$this->CI->app_hooks->register('member_login','A member logs in.',array('member'));
			$this->CI->app_hooks->register('member_logout','A member logs out.',array('member'));
			$this->CI->app_hooks->register('member_change_password','A member changes their password.',array('member'), array('new_password'));
		}
		
		if ($db_version < 1.09) {
			$this->CI->load->library('app_hooks');
			
			$this->CI->app_hooks->register('member_update','A member profile is updated.',array('member'));
		}
		
		if ($db_version < 1.10) {
			$this->CI->settings_model->new_setting(3, 'member_list_configuration', 'a:5:{i:0;s:8:"username";i:1;s:5:"email";i:2;s:9:"full_name";i:3;s:6:"groups";i:4;s:6:"status";}', '', 'text','', FALSE, TRUE);
		}
		
		if ($db_version < 1.11) {
			$this->CI->db->query('CREATE TABLE `user_activity` (
								  `user_activity_id` int(11) NOT NULL auto_increment,
								  `user_id` int(11) NOT NULL,
								  `user_activity_date` DATETIME NOT NULL,
								  PRIMARY KEY  (`user_activity_id`)
								) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1000 ;');
		}
		
		if ($db_version < 1.13) {
			$this->CI->settings_model->delete_setting('simultaneous_login_prevention');
			$this->CI->settings_model->new_setting(3, 'simultaneous_login_prevention', '0', 'Prevent two users from logging in with the same account credentials at the same time.', 'toggle', 'a:2:{i:0;s:3:"Off";i:1;s:2:"On";}');
		}
		
		if ($db_version < 1.14) {
			$this->CI->settings_model->new_setting(3, 'registration_spam_stopper', '0', 'Prevent spam in your standard registration forms.  You must paste the following HTML in your registration form if activated: &lt;span style="display: none"&gt;&ltlabel for="email_confirmation_hp"&gt;Email Confirmation&lt/label&gt;&lt;input type="text" name="email_confirmation_hp" value="" /&gt;&lt;/span&gt;', 'toggle', 'a:2:{i:0;s:3:"Off";i:1;s:2:"On";}');
		}
		
		if ($db_version < 1.15) {
			$this->CI->db->query('ALTER TABLE `users` ADD COLUMN `user_salt` VARCHAR(32) AFTER `user_password`');
		}
		
		if ($db_version < 1.16) {
			$this->CI->db->query('ALTER TABLE `users` ADD INDEX `customer_id` (`customer_id`)');
		}
		
		if ($db_version < 1.17) {
			$this->CI->settings_model->new_setting(1, 'datasets_rows_per_page', '50', 'Specify the number of rows to show per page when viewing a control panel dataset (e.g., published content, members listing, etc.).', 'text', FALSE, FALSE, FALSE);
		}
		
		if ($db_version < 1.18)
		{
			if ($this->CI->db->table_exists($this->CI->config->item('sess_table_name')) == FALSE) {			
				$this->CI->db->query("CREATE TABLE IF NOT EXISTS  `ci_sessions` (
					session_id varchar(40) DEFAULT '0' NOT NULL,
					ip_address varchar(16) DEFAULT '0' NOT NULL,
					user_agent varchar(120) NOT NULL,
					last_activity int(10) unsigned DEFAULT 0 NOT NULL,
					user_data text NOT NULL,
					PRIMARY KEY (session_id),
					KEY `last_activity_idx` (`last_activity`)
				);");
			}
		}
		
		if ($db_version < 1.19) {
			$this->CI->db->query('ALTER TABLE ci_sessions MODIFY user_agent VARCHAR(120);');
		}
		
		if ($db_version < 1.20) {
			$this->CI->db->query('ALTER TABLE ci_sessions MODIFY user_agent VARCHAR(255);');
		}
		
		if ($db_version < 1.21) {
			$this->CI->db->query('ALTER TABLE `users` ADD INDEX `user_username` (`user_username`)');
		}
		
		if ($db_version < 1.22) {
			$this->CI->db->query('ALTER TABLE `users` ADD INDEX `user_deleted` (`user_deleted`)');
		}
		
		if ($db_version < 1.23) {
			$this->CI->db->query('ALTER TABLE `users` ADD INDEX `user_suspended` (`user_suspended`)');
			$this->CI->db->query('ALTER TABLE `users` ADD INDEX `user_email` (`user_email`)');
			$this->CI->db->query('ALTER TABLE `users` ADD INDEX `user_remember_key` (`user_remember_key`)');
		}
		
		// return current version
		return $this->version;
	}
}