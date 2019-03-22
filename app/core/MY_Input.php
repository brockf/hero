<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
* Custom Input library
*
* This library does two important things:
*
* 	(1) Always enable GET query strings, regardless of configuration.
*	(2) Allow <? and ?> in input, so that we can take XML requests.
*
* @copyright Electric Function, Inc.
* @package Hero Framework
* @author Electric Function, Inc.
*/

class MY_Input extends CI_Input {
	/**
	* Constructor
	*
	* We replaced this to set the GET array to always be used.
	*/
	function __construct () {
		log_message('debug', "Input Class Initialized");

		$this->_allow_get_array	= TRUE;
		$this->_enable_xss		= (config_item('global_xss_filtering') === TRUE);
		$this->_enable_csrf		= (config_item('csrf_protection') === TRUE);

		global $SEC;
		$this->security =& $SEC;

		// Do we need the UTF-8 class?
		if (UTF8_ENABLED === TRUE)
		{
			global $UNI;
			$this->uni =& $UNI;
		}

		// Sanitize global arrays
		$this->_sanitize_globals();
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