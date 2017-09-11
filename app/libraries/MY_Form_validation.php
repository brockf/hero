<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class MY_Form_validation extends CI_Form_validation {
	public function __construct() {
		parent::__construct();
		
		$this->CI =& get_instance();
	}
	
    function unique_email($email) {
	    $this->CI->load->model('users/user_model');
      if ($this->CI->user_model->unique_email($email)) {		
        return TRUE;
      }
      else {
        $this->CI->form_validation->set_message('unique_email', 'The Email you have selected is unavailable.');
        return FALSE;
      }
    }
    
    function unique_username($username) {
	    $this->CI->load->model('users/user_model');
      if ($this->CI->user_model->unique_username($username)) {
        return TRUE;
      }
      else {
        $this->CI->form_validation->set_message('unique_username', 'The Username you have selected is unavailable.');
        return FALSE;
      }
    }
}
