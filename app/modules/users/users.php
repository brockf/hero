<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
* Users Module Definition
*
* Declares the module, update code, etc.
*
* @author Electric Function, Inc.
* @package Electric Publisher
*
*/

class Users extends Module {
	var $version = '1.01';
	var $name = 'users';

	function __construct () {
		// set the active module
		$this->active_module = $this->name;
		
		parent::__construct();
	}
	
	function admin_preload ()
	{
		$CI =& get_instance();
		
		$CI->navigation->child_link('members',10,'Member Search',site_url('admincp/users'));
		$CI->navigation->child_link('members',20,'Add Member',site_url('admincp/users/add'));
		$CI->navigation->child_link('members',30,'Member Groups',site_url('admincp/users/groups'));
		$CI->navigation->child_link('configuration',25,'Member Data',site_url('admincp/users/data'));
	}

	function update ($db_version) {
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
								  PRIMARY KEY  (`user_id`)
								) ENGINE=MyISAM AUTO_INCREMENT=1001 DEFAULT CHARSET=utf8 AUTO_INCREMENT=1001 ;');
		}
		
		// return current version
		return $this->version;
	}
}