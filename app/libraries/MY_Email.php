<?php

/*
* MY_Email extends the Email library to automatically queue excess emails
*/

class MY_Email extends CI_Email {
	function __construct ($config = array()) {
		parent::CI_Email($config);
	}
	
	function send ($queue = FALSE) {
		if ($queue = FALSE) {
			parent::send();
		}
		else {
			// let's put this in the queue
			if (is_array($this->_recipients)) {
				$to = implode(', ', $this->_recipients);
			}
			else {
				$to = $this->_recipients;
			}
			
			$subject = $this->_headers['Subject'];
			$body = $this->_body;
			
			$this->db->insert('mail_queue', array(
												'to' => $to,
												'subject' => $subject,
												'body' => $body,
												'date' => date('Y-m-d H:i:s'),
												'wordwrap' => ($this->wordwrap == TRUE) ? '1' : '0',
												'is_html' => ($this->mailtype == 'html') ? '1' : '0'
										));
										
			return TRUE;
		}
	}
}
