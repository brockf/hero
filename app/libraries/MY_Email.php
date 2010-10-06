<?php

/*
* MY_Email extends the Email library to automatically queue excess emails
*/

class MY_Email extends CI_Email {
	private $_plaintext_subject;

	function __construct ($config = array()) {
		parent::CI_Email($config);
	}
	
	function subject ($subject) {
		$this->_plaintext_subject = $subject;
		
		parent::subject($subject);
	}
	
	function send ($queue = FALSE) {
		$CI =& get_instance();
		
		if ($queue == FALSE) {
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
			
			$subject = $this->_plaintext_subject;
			$body = $this->_body;
			
			$CI->db->insert('mail_queue', array(
												'`to`' => $to,
												'`subject`' => base64_encode($subject),
												'`body`' => base64_encode($body),
												'`date`' => date('Y-m-d H:i:s'),
												'`wordwrap`' => ($this->wordwrap == TRUE) ? '1' : '0',
												'`is_html`' => ($this->mailtype == 'html') ? '1' : '0'
										));
										
			return TRUE;
		}
	}
}
