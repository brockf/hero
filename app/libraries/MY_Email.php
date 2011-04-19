<?php

/*
* MY_Email extends the Email library to automatically queue excess emails
*
* Queue is done by storing the email body in a folder as plaintext
* If the message being sent shares the same body as the last message, it won't generate multiple
* email files (to save HD space).
*
* Queued messages are sent via cron hook and Email_model::send_queue()
*
*/

class MY_Email extends CI_Email {
	private $_plaintext_subject;
	private $_plaintext_body;
	private $_plaintext_from_name;
	private $_plaintext_from_email;
	private $_previous_body; // for caching body files in queued mass mails
	private $_previous_body_file;

	function __construct ($config = array()) {
		parent::__construct($config);
	}
	
	function subject ($subject) {
		$this->_plaintext_subject = $subject;
		
		parent::subject($subject);
	}
	
	function message ($message) {
		$this->_plaintext_body = $message;
		
		parent::message($message);
	}
	
	function from ($from_email, $from_name) {
		$this->_plaintext_from_name = $from_name;
		$this->_plaintext_from_email = $from_email;
		
		parent::from($from_email, $from_name);
	}
	
	function send ($queue = FALSE) {
		$CI =& get_instance();
		
		if ($queue == FALSE) {
			parent::send();
		}
		else {
			// let's put this in the queue
			// do we have a mail queue folder?
			$mail_queue_folder = setting('path_writeable') . 'mail_queue';
			
			if (!file_exists($mail_queue_folder)) {
				$CI->settings_model->make_writeable_folder($mail_queue_folder, TRUE);
			}
			
			// if this body is the same as the last,
			// then the body file doesn't need to be regenerated
			if (empty($this->_previous_body) or ($this->_plaintext_body != $this->_previous_body)) {
				// create the file
				if (!function_exists('write_file')) {
					$CI->load->helper('file');
				}
				
				$body_file = md5($this->_plaintext_body) . '.email';
				
				write_file($mail_queue_folder . '/' . $body_file, $this->_plaintext_body);
				
				$this->_previous_body = $this->_plaintext_body;
				$this->_previous_body_file = $body_file;
			}
			else {
				$body_file = $this->_previous_body_file;
			}
			
			if (is_array($this->_recipients)) {
				$to = implode(', ', $this->_recipients);
			}
			else {
				$to = $this->_recipients;
			}
			
			$CI->db->insert('mail_queue', array(
												'`to`' => $to,
												'`from_name`' => $this->_plaintext_from_name,
												'`from_email`' => $this->_plaintext_from_email,
												'`subject`' => $this->_plaintext_subject,
												'`body`' => $body_file,
												'`date`' => date('Y-m-d H:i:s'),
												'`wordwrap`' => ($this->wordwrap == TRUE) ? '1' : '0',
												'`is_html`' => ($this->mailtype == 'html') ? '1' : '0'
										));
			
			unset($CI);
										
			return TRUE;
		}
	}
}
