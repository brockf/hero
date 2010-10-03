<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
* Email Module Definition
*
* Declares the module, update code, etc.
*
* @author Electric Function, Inc.
* @copyright Electric Function, Inc.
* @package Electric Publisher
*
*/

class Emails extends Module {
	var $version = '1.01';
	var $name = 'emails';

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
		$this->CI->navigation->child_link('configuration',20,'Emails',site_url('admincp/emails'));
	}
	
	function update ($db_version) {
		if ($db_version < 1.01) {
			$this->CI->settings_model->make_writeable_folder(setting('path_email_templates'), TRUE);
			
			$this->CI->db->query('DROP TABLE IF EXISTS `emails`');
			$this->CI->db->query('DROP TABLE IF EXISTS `email_triggers`');
			
			$this->CI->db->query('CREATE TABLE `emails` (
								  `email_id` int(11) NOT NULL auto_increment,
								  `hook_name` varchar(125) NOT NULL,
								  `email_parameters` TEXT NOT NULL,
								  `email_subject_template` varchar(255) NOT NULL,
								  `email_body_template` varchar(255) NOT NULL,
								  `email_recipients` TEXT NOT NULL,
								  `email_bccs` TEXT NOT NULL,
								  `email_is_html` tinyint(1) NOT NULL,
								  `email_deleted` tinyint(0) NOT NULL,
								  PRIMARY KEY  (`email_id`)
								) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;');
		}
		
		return $this->version;
	}
}