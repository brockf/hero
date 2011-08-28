<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
* Notices Model 
*
* Set and retrieve the notices and errors that appear at the top of the control panel
*
* @copyright Electric Function, Inc.
* @author Electric Function, Inc.
* @package Hero Framework
*
*/

class Notices {
	var $CI;
	
    function __construct () {
    	$this->CI =& get_instance();
    }
    
    /**
    * Set Error
    *
    * Sets an error for later retrieval in the view
    *
    * @param string $message The message for the error
    *
    * @return boolean TRUE upon success
    */
    function SetError($message) {    	
    	$errors = $this->GetErrors(false);
    	
    	$errors[] = $message;
    	
    	$errors = serialize($errors);
    	
    	$this->CI->session->set_userdata(array('errors' => $errors));
    	
    	return true;
    }

	/**
    * Get Errors
    *
    * Gets all errors that have been set
    *
    * @param boolean $clear Set to FALSE to retain all elements after they've been retrieved.  Default: true.
    *
    * @return array All errors
    */
	function GetErrors ($clear = true) {
		$errors = $this->CI->session->userdata('errors');
		
		if (!empty($errors) and is_array(unserialize($errors))) {
			if ($clear == true) {
				$this->CI->session->set_userdata(array('errors' => ''));
			}
			return unserialize($errors);
		}
		else {
			return array();
		}
	}
	
	/**
    * Set Notice
    *
    * Sets a notice for later retrieval in the view
    *
    * @param string $message The message for the notice
    *
    * @return boolean TRUE upon success
    */
   	function SetNotice($message) {
    	$notices = $this->GetNotices(false);
    	
    	$notices[] = $message;
    	
    	$notices = serialize($notices);
    	
    	$this->CI->session->set_userdata(array('notices' => $notices));
    	
    	return true;
    }

	/**
    * Get Notices
    *
    * Gets all notices that have been set
    *
    * @param boolean $clear Set to FALSE to retain all elements after they've been retrieved.  Default: true.
    *
    * @return array All notices
    */
	function GetNotices ($clear = true) {
		$notices = $this->CI->session->userdata('notices');
		
		if (!empty($notices) and is_array(unserialize($notices))) {
			if ($clear == true) {
				$this->CI->session->set_userdata(array('notices' => ''));
			}
			return unserialize($notices);
		}
		else {
			return array();
		}
	}
}