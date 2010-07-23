<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
* Modified URI Class
*
* If we can, we will save the real query_string passed by the user.
* It's stored in the MY_QUERY_STRING constant and, combined with a modified MY_Input
* class, we'll be able to access them with $this->input->get();
*/
class MY_URI extends CI_URI {
	public function __construct() {
		parent::__construct();
	}
	
	/**
	* Modified _fetch_uri_string
	*
	* If we are passing a real query_string, we need to deal with it especially
	*/
	function _fetch_uri_string () {
		// strip out the real query_string
		if (isset($_SERVER['REQUEST_URI']) and strpos($_SERVER["REQUEST_URI"],'?') !== FALSE) {
			list($path,$query_string) = explode('?', $_SERVER["REQUEST_URI"]);
			
			$this->uri_string = trim($path, '/');
			
			// now let's replace the query_string
			if (empty($query_string)) {
				$_GET = array();
			}
			else {
				$my_query_string = array();
				parse_str($query_string, $my_query_string);
				define('MY_QUERY_STRING',serialize($my_query_string));
			}
			
			return;
		}
		
		parent::_fetch_uri_string();
	}
}