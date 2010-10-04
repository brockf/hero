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
	var $version = '1.02';
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
			$this->CI->db->query('UPDATE `settings` SET `setting_value`=\'\' WHERE `setting_name`=\'email_signature\'');
			
			$this->CI->db->query('CREATE TABLE `emails` (
								  `email_id` int(11) NOT NULL auto_increment,
								  `hook_name` varchar(125) NOT NULL,
								  `email_parameters` TEXT NOT NULL,
								  `email_subject` VARCHAR(255) NOT NULL,
								  `email_subject_template` varchar(255) NOT NULL,
								  `email_body_template` varchar(255) NOT NULL,
								  `email_recipients` TEXT NOT NULL,
								  `email_bccs` TEXT NOT NULL,
								  `email_is_html` tinyint(1) NOT NULL,
								  `email_deleted` tinyint(0) NOT NULL,
								  PRIMARY KEY  (`email_id`)
								) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;');
		}
		
		if ($db_version < 1.02) {
			$this->CI->load->helper('file');
			
			$body = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
<style>
h1 { background-color: #e6f2f8; color: #0b5679; font-size: 18pt; font-weight: normal; font-family: helvetica, arial, sans-serif; margin: 0 0 10px 0; padding: 7px 10px }
div.body { padding: 10px; font-size: 10pt; font-family: helvetica, arial, sans-serif; color: #111; }
</head>
<body>
<div class="body">
	{block name="body"}
	
	{/block}
</div>
</body>
</html>';
			
			write_file(setting('path_email_templates') . '/email_layout.thtml', $body);
		}
		
		return $this->version;
	}
}