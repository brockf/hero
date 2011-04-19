<?php (defined('BASEPATH')) OR exit('No direct script access allowed');

/* load the MX_Loader class */
require APPPATH."third_party/MX/Loader.php";

class MY_Loader extends MX_Loader {
	/**
	* Customized loader methods, which will use define_module
	* to load the module definition file if necessary.
	*/
	function helper ($helper) {
		if (!is_array($helper)) {
			self::define_module($helper);
		}	
		
		return parent::helper($helper);
	}
	
	function library ($library, $params = NULL, $object_name = NULL) {
		if (!is_array($library)) {
			self::define_module($library);
		}	
		
		return parent::library($library, $params, $object_name);
	}
	
	function model ($model, $object_name = NULL, $connect = FALSE) {
		if (!is_array($model)) {
			self::define_module($model);
		}	
		
		return parent::model($model, $object_name, $connect);
	}
	
	function plugin ($plugin) {
		if (!is_array($plugin)) {
			self::define_module($plugin);
		}	
		
		return parent::plugin($plugin);
	}

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