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
	var $version = '1.05';
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
		
		if ($db_version < 1.04) {
			// initial import of template files
			
			$this->CI->load->helper('file');
			$layout_file = read_file(APPPATH . 'modules/emails/template_import/email_layout.thtml');
			write_file(setting('path_email_templates') . '/email_layout.thtml', $layout_file);
			
			$import = array(
						'member_register' => array('subject' => '{$site_name}: Account Details', 'to' => array('member')),
						'member_forgot_password' => array('subject' => 'Your new password', 'to' => array('member')),
						'member_validate_email' => array('subject' => 'Please validate your email', 'to' => array('member')),
						'subscription_charge' => array('subject' => '{$site_name}: Thank you for your subscription payment', 'to' => array('member'), 'bcc' => array('admin')),
						'subscription_expire' => array('subject' => '{$site_name}: Your subscription has expired', 'to' => array('member')),
						'store_order' => array('subject' => 'Thank you for your order from {$site_name}', 'to' => array('member'), 'bcc' => array('admin')),
						'store_order_product_downloadable' => array('subject' => 'Your product download: {$product.name}', 'to' => array('member'))
					);
		
			$this->_email_import($import);
		}
		
		if ($db_version < 1.05) {
			// mail queue
			$this->CI->db->query('CREATE TABLE `mail_queue` (
								  `mail_queue_id` int(11) NOT NULL auto_increment,
								  `to` TEXT NOT NULL,
								  `subject` TEXT NOT NULL,
								  `body` TEXT NOT NULL,
								  `date` DATETIME NOT NULL,
								  `wordwrap` TINYINT(1) NOT NULL,
								  `is_html` TINYINT(1) NOT NULL,
								  PRIMARY KEY  (`mail_queue_id`)
								) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;');
								
			$this->load->library('app_hooks');
			
			$this->app_hooks->bind('cron','email_model','mail_queue',APPPATH . 'modules/emails/models/email_model.php');
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