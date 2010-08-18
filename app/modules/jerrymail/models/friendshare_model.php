<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
* Friendshare Model 
*
* Contains all the methods used to send and track Share-with-a-Friends
*
* @author Electric Function, Inc.
* @copyright Electric Function, Inc.
* @package Electric Publisher
*
*/

class Friendshare_model extends CI_Model
{
	function __construct()
	{
		parent::CI_Model();
	}
	
	/**
	* Send to a Friend
	*
	* @param int $content_id
	* @param string $email
	*
	* @return boolean
	*/
	function send ($content_id, $email) {
		// load content
		$CI =& get_instance();
		$CI->load->model('publish/content_model');
		$content = $CI->content_model->get_content($content_id);
		
		if (empty($content)) {
			return FALSE;
		}
	
		// has this person received more than 3 emails before?
		$count = $this->count_shares($email);
		
		if ($count < 3) {
			// send full email
			$body = $CI->load->view('jerrymail/share_full_article', $content, TRUE);
			
			$CI->load->library('email');
			$CI->email->initialize(array('mailtype' => 'html'));

			$CI->email->from(setting('site_email'), setting('email_name'));
			$CI->email->to($email);
			
			$CI->email->subject('Free Sample of ' . setting('site_name') . ': ' . $content['title']);
			$CI->email->message($body);
			
			$CI->email->send();
		}
		else {
			// send trimmed email
			$body = $CI->load->view('jerrymail/share_snippet', $content, TRUE);
			
			$CI->load->library('email');
			$CI->email->initialize(array('mailtype' => 'html'));

			$CI->email->from(setting('site_email'), setting('email_name'));
			$CI->email->to($email);
			
			$CI->email->subject('Recommended Reading: ' . $content['title']);
			$CI->email->message($body);
			
			$CI->email->send();
		}	
		
		$this->track_share($content['id'], $email);
	}
	
	/**
	* Track Share
	*
	* @param int $content_id
	* @param string $email
	*
	* @return $friendshare_id
	*/
	function track_share ($content_id, $email) {
		$this->db->insert('friendshare',array('content_id' => $content_id, 'friendshare_email' => $email, 'friendshare_date' => date('Y-m-d H:i:s')));
		
		return $this->db->insert_id();
	}
	
	/**
	* Count Shares
	*
	* @param string $email
	*
	* @return int Number of shares to this email
	*/
	function count_shares ($email) {
		$result = $this->db->select('friendshare_id')->where('friendshare_email',$email)->get('friendshare');
		
		return $result->num_rows();
	}
	
}