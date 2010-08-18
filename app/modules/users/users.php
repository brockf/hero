<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
* Users Module Definition
*
* Declares the module, update code, etc.
*
* @author Electric Function, Inc.
* @copyright Electric Function, Inc.
* @package Electric Publisher
*
*/

class Users_module extends Module {
	var $version = '1.05';
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
		$this->CI->navigation->child_link('members',10,'Manage Members',site_url('admincp/users'));
		$this->CI->navigation->child_link('members',20,'Add Member/Administrator',site_url('admincp/users/add'));
		$this->CI->navigation->child_link('members',30,'Login Records',site_url('admincp/users/logins'));
		$this->CI->navigation->child_link('members',40,'Member Groups',site_url('admincp/users/groups'));
	}
	
	/*
	* Pre-front Method
	*
	* Triggered prior to loading the frontend
	*/
	function front_preload () {
		$this->CI->smarty->addPluginsDir(APPPATH . 'modules/users/template_plugins/');
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
								  `usergroup_default` tinyint(4) NOT NULL,
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
								  PRIMARY KEY  (`user_field_id`)
								) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;');
		}
		
		if ($db_version < 1.02) {
			$this->CI->settings_model->new_setting(3, 'require_tos', '0', 'Require registering users to agree to your site\'s Terms of Service?', 'toggle', 'a:2:{i:0;s:2:"No";i:1;s:3:"Yes";}');
			$this->CI->settings_model->new_setting(3, 'terms_of_service', 'Enter your terms of service here.', 'If "require_tos" is On, users will be forced to accept these Terms prior to registering.', 'textarea');
		}
		
		if ($db_version < 1.03) {
			$this->CI->settings_model->new_setting(3, 'validate_emails', '1', 'Require registering users to validate their emails by clicking a link in an automated email', 'toggle', 'a:2:{i:0;s:2:"No";i:1;s:3:"Yes";}');
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
		
		// return current version
		return $this->version;
	}
}