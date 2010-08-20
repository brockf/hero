<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
* Newsletter Model 
*
* Contains all the methods used to send and track newsletters
*
* @author Electric Function, Inc.
* @copyright Electric Function, Inc.
* @package Electric Publisher
*
*/

class Newsletter_model extends CI_Model
{
	function __construct()
	{
		parent::CI_Model();
	}
	
	/**
	* Send Newsletter
	*
	* @param string $email Body of the email, built using a returned view()
	*
	* @return boolean
	*/
	function send ($email, $subject = 'Daily Newsletter') {
		// load all subscribers
		$this->db->save_queries = TRUE;
		
		if (!defined('PRESALE_MODE')) {
			$result = $this->db->select('user_email')->from('users')->where('user_deleted','0')->where('user_validate_key','')->where('newsletter','1')->get();
		}
		else {
			$result = $this->db->select('user_email')->from('users')->where('user_is_admin','1')->where('user_deleted','0')->get();
		}
		
		if ($result->num_rows() == 0) {
			return FALSE;
		}
		
		// initialize email
		$CI =& get_instance();
		$CI->load->library('email');
		$CI->email->initialize(array('mailtype' => 'html'));
		
		foreach ($result->result_array() as $user) {
			$CI->email->clear();
		
			// send full email
			$CI->email->from(setting('site_email'), setting('email_name'));
			$CI->email->to($user['user_email']);
			
			$CI->email->subject(setting('site_name') . ': ' . $subject);
			$CI->email->message($email);
			
			$CI->email->send();
		}
	}
}