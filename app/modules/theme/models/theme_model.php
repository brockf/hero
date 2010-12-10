<?php

/**
* Theme Model
*
* Manages content
*
* @author Electric Function, Inc.
* @package Electric Publisher

*/

class Theme_model extends CI_Model
{
	private $CI;
	
	function __construct()
	{
		parent::CI_Model();
		
		$this->CI =& get_instance();
	}
	
	function get_themes() {
		$themes = array();
		
		if ($handle = opendir(FCPATH . 'themes')) {
		    while (false !== ($file = readdir($handle))) {
		        if (is_dir(FCPATH . 'themes/' . $file) and $file != '.' and $file != '..') {
		        	if (strpos($file, '_') !== 0) {
		        		$themes[] = $file;
		        	}
		        }
		    }	
		}

		return $themes;
	}
}