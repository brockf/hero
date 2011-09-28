<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
* Email Module Definition
*
* Declares the module, update code, etc.
*
* @author Electric Function, Inc.
* @copyright Electric Function, Inc.
* @package Hero Framework
*
*/

class Emails extends Module {
	var $version = '1.09';
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
		$this->CI->admin_navigation->child_link('configuration',20,'Emails',site_url('admincp/emails'));
		
		$this->CI->admin_navigation->child_link('members',25,'Send Email',site_url('admincp/emails/send'));
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
								  `email_subject_template` varchar(255)  NULL,
								  `email_body_template` varchar(255) NOT NULL,
								  `email_recipients` TEXT NOT NULL,
								  `email_bccs` TEXT NOT NULL,
								  `email_is_html` tinyint(1) NOT NULL,
								  `email_deleted` tinyint(0) NOT NULL,
								  PRIMARY KEY  (`email_id`)
								) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;');
		}
		
		if ($db_version < 1.04) {
			// initial import of template files
			
			$this->CI->load->helper('file');
			$layout_file = read_file(APPPATH . 'modules/emails/template_import/email_layout.thtml');
			write_file(setting('path_email_templates') . '/email_layout.thtml', $layout_file);
			
			$import = array(
						'member_register' => array('subject' => '{$site_name}: Account Details', 'to' => array('member')),
						'member_forgot_password' => array('subject' => 'Your new password', 'to' => array('member')),
						'member_validate_email' => array('subject' => 'Please validate your email', 'to' => array('member'))
					);
					
			if (file_exists(APPPATH . 'modules/billing')) {
				$import_billing = array(		
							'subscription_charge' => array('subject' => '{$site_name}: Thank you for your subscription payment', 'to' => array('member'), 'bcc' => array('admin')),
							'subscription_expire' => array('subject' => '{$site_name}: Your subscription has expired', 'to' => array('member')),
							'store_order' => array('subject' => 'Thank you for your order from {$site_name}', 'to' => array('member'), 'bcc' => array('admin')),
							'store_order_product_downloadable' => array('subject' => 'Your product download: {$product.name}', 'to' => array('member'))
						);
						
				$import = array_merge($import, $import_billing);
			}
		
			$this->_email_import($import);
		}
		
		if ($db_version < 1.05) {
			// mail queue
			$this->CI->db->query('CREATE TABLE `mail_queue` (
								  `mail_queue_id` int(11) NOT NULL auto_increment,
								  `to` TEXT NOT NULL,
								  `subject` TEXT NOT NULL,
								  `body` VARCHAR(250) NOT NULL,
								  `date` DATETIME NOT NULL,
								  `wordwrap` TINYINT(1) NOT NULL,
								  `is_html` TINYINT(1) NOT NULL,
								  PRIMARY KEY  (`mail_queue_id`)
								) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;');
								
			$this->CI->load->library('app_hooks');
			
			$this->CI->app_hooks->bind('cron','email_model','mail_queue',APPPATH . 'modules/emails/models/email_model.php');
		}
		
		if ($db_version < 1.06) {
			$this->CI->settings_model->new_setting(1, 'mail_queue_limit', '250', 'How many emails should be processed from the mail queue every 5 minutes?', 'text', '', FALSE, FALSE);
		}
		
		if ($db_version < 1.07) {
			$this->CI->db->query('ALTER TABLE `mail_queue` ADD COLUMN `from_name` VARCHAR(250) AFTER `to`');
			$this->CI->db->query('ALTER TABLE `mail_queue` ADD COLUMN `from_email` VARCHAR(250) AFTER `from_name`');
		}
		
		if ($db_version < 1.08) {
			$this->CI->db->query('CREATE TABLE `email_templates` (
								  `email_template_id` int(11) NOT NULL auto_increment,
								  `email_template_name` varchar(100) NOT NULL,
								  `email_template_subject` text NOT NULL,
								  `email_template_body` text NOT NULL,
								  `email_template_is_html` tinyint(1) NOT NULL,
								  PRIMARY KEY  (`email_template_id`)
								) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;');
		}
		
		if ($db_version < 1.09) {
			$this->CI->load->library('app_hooks');
			
			$this->CI->app_hooks->register('mass_email_pre','Just before a control panel mass email is sent.');
			$this->CI->app_hooks->register('mass_email','All emails have been sent in a control panel mass email.');
		}
		
		return $this->version;
	}
	
	/**
	* Email Import
	*
	* Imports emails and their template files from the template_import/ directory and an array
	*/
	function _email_import($import) {
		$this->CI->load->helper('file');
		
		foreach ($import as $hook => $details) {
			$subject = $details['subject'];
			$to = $details['to'];
			$bcc = isset($details['bcc']) ? $details['bcc'] : array();
			
			if (file_exists(APPPATH . 'modules/emails/template_import/' . $hook . '.thtml')) {
				$insert_fields = array(
									'hook_name' => $hook,
									'email_parameters' => serialize(array()),
									'email_subject' => $subject,
									'email_recipients' => serialize($to),
									'email_bccs' => serialize($bcc),
									'email_is_html' => '1'
								);
								
				$this->CI->db->insert('emails', $insert_fields);
				
				$email_id = $this->CI->db->insert_id();
				
				// get body
				$body = read_file(APPPATH . 'modules/emails/template_import/' . $hook . '.thtml');
				
				// write files
				$subject_template = $hook . '_' . $email_id . '_subject.thtml';
				write_file(setting('path_email_templates') . '/' . $subject_template, $subject);
				$body_template = $hook . '_' . $email_id . '_body.thtml';
				write_file(setting('path_email_templates') . '/' . $body_template, $body);
				
				$this->CI->db->update('emails', array('email_body_template' => $body_template, 'email_subject_template' => $subject_template), array('email_id' => $email_id));
			}
		}
	}
}