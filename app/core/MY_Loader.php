<?php (defined('BASEPATH')) OR exit('No direct script access allowed');

/* load the MX_Loader class */
require APPPATH."third_party/MX/Loader.php";

class MY_Loader extends MX_Loader {

	/*
	* Define Module
	*
	* Loads the main module definition file from /modules/[module]/[module].php
	*
	* @param string $path The path to the file being loaded, e.g., "settings/settings_model.php"
	*
	*/
	function define_module ($path) {
	    if (strpos($path, '/') !== FALSE) {
	    	// this may be a module
	    	list($module,$path) = explode('/',$path);
	    	
	    	$module = strtolower($module);
	    	
	    	$CI =& get_instance();
	    	
	    	// make sure the module_definitions var exists
	    	if (!isset($CI->module_definitions)) {
	    		$CI->module_definitions = new stdClass();
	    	}
	    	
	    	if (!isset($CI->module_definitions->$module) and is_dir(APPPATH . 'modules/' . $module)) {
	    		// this is a module
	    		if (file_exists(APPPATH . 'modules/' . $module . '/' . $module . '.php')) {
	    			include_once(APPPATH . 'modules/' . $module . '/' . $module . '.php');
	    			
	    			// because of a name conflict, this may be called Modulename_module
	    			if (class_exists($module . '_module')) {
	    				$class_name = $module . '_module';
	    			}
	    			else {
	    				$class_name = $module;
	    			}
	    			
	    			$CI->module_definitions->$module = new $class_name;
	    			
	    			log_message('debug', 'Module defined and loaded: ' . $module);
	    		}
	    	}
	    }
	}

}