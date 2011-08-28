<?php

/**
* Head Compile Library
*
* A work in progress: This will dynamically compile and minify included JS and CSS files.
*
* @author Electric Function, Inc.
* @copyright Electric Function, Inc.
* @package Hero Framework
*
*/

class Head_compile {
	var $CI;
	
	function __construct () {
		$this->CI =& get_instance();
	}
	
	function compile () {
		$output = $this->CI->output->get_output();
		
		// get <head> contents
		$matches = array();
		preg_match('#<head>(.*?)<\/head>#si',$output,$matches);
		
		// head contents
		$head = $matches[1];
		
		// get JS includes via <script> references
		preg_match_all('#<script(.*?)src="(.*?)"(.*?)>(.*?)<\/script>#si',$head,$matches);
		$js_includes = $matches[2];
		
		// delete non-local includes
		foreach ($js_includes as $key => $script) {
			if (strpos($script,'//') !== FALSE and strpos($script,$this->CI->config->item('base_url')) === FALSE) {
				// this script is external (has "//") and it's not just a domain reference to this site
				unset($js_includes[$key]);
			}
		}
		
		if (!empty($js_includes)) {
			$this->load->library('JSMin');
			
			// minify!
			$js_compiled = '';
			foreach ($js_includes as $script) {
				// get the file
				$js_compiled .= JSMin::minify($)
			}
		}
		
		// TODO:
		
		// - load the files for minification
		//		- this includes using the base_href tag if one exists, otherwise loading paths relative to the domain or
		//		  using the full script URL if it's in that format
		// - replace old <script> references with one big reference
		// return the output
	}
}