<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class MY_Input extends CI_Input {
	function __construct () {
		parent::__construct();
	}
	
	/**
	* Modified get
	*
	* Allows us to access a real Query_String as created by our MY_URI class
	* and stored in the MY_QUERY_STRING constant.
	*/
	function get($index = '', $xss_clean = FALSE)
	{
		if (!defined('MY_QUERY_STRING')) {
			// no $_GET data exists
			
			return FALSE;
		}
	
		// do we have a query string?
		$my_query_string = unserialize(MY_QUERY_STRING);
		if (is_array($my_query_string)) {
			$_TEMP_GET = $my_query_string;
		}
		else {
			$_TEMP_GET = array();
		}
		
		return $this->_fetch_from_array($_TEMP_GET, $index, $xss_clean);
	}
	
	/**
	* Modified _clean_input_keys
	*
	* We are a bit more lenient because we take XML through this
	*/
	function _clean_input_keys($str)
	{
		if ( ! preg_match("/^[a-z0-9:_\/\-\<\?\>\;]+$/i", $str))
		{
			exit('Disallowed Key Characters.');
		}

		// Clean UTF-8 if supported
		if (UTF8_ENABLED === TRUE)
		{
			$str = $this->uni->clean_string($str);
		}

		return $str;
	}
}