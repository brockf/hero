<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
* My Session
*
* Don't use the database for sessions if the table does not exist.
*/
class MY_Session extends CI_Session {
	public function __construct($params = array()) {
		$CI =& get_instance();
		
		// we really only need to check this during the installation wizard
		if ($CI->uri->segment(1) == 'install' and isset($CI->db) and $CI->db->table_exists($CI->config->item('sess_table_name')) == FALSE) {
			$params['sess_use_database'] = FALSE;
		}
		
		parent::__construct($params);
	}
}