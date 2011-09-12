<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/* load the MX_Loader class */
require APPPATH."third_party/MX/Loader.php";

class MY_Loader extends MX_Loader {
	private $CI;

	function __construct () {
		parent::__construct();
	}

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
	    	// normally, we'd do this in the constructor, but that way left us with
	    	// some issues in that module_model was NULL in certain instances
	    	if (isset($this->CI) or !isset($this->CI->module_model)) {
		    	$this->CI =& get_instance();
		    }
	    	
	    	// this may be a module
	    	list($module,$path) = explode('/',$path);
	    	
	    	$module = strtolower($module);
	    	
	    	// this is an early check - is this a module or not?
	    	if (!file_exists(APPPATH . 'modules/' . $module)) {
	    		return false;
	    	}
	    	
	    	// make sure the module_definitions var exists
	    	if (!isset($this->CI->module_definitions)) {
	    		$this->CI->module_definitions = new stdClass();
	    	}
	    	
	    	// are we ignoring this module?
			if (isset($this->CI->module_model->modules_cache[$module]['ignored']) and $this->CI->module_model->modules_cache[$module]['ignored'] === TRUE) {
	    		log_message('debug','Ignoring module: ' . $module);
	    		return;
	    	}
	    	
	    	// if this is not installed, should we auto-install it?
	    	if (isset($this->CI->module_model->modules_cache[$module]['installed']) and $this->CI->module_model->modules_cache[$module]['installed'] === FALSE) {
	    		if ($this->CI->config->item('modules_auto_install') === '0') {
	    			log_message('debug','Not auto-install module: ' . $module);
	    			return;
	    		}
	    	}
	    	
	    	if (!isset($this->CI->module_definitions->$module) and is_dir(APPPATH . 'modules/' . $module)) {
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
	    			
	    			$this->CI->module_definitions->$module = new $class_name;
	    			
	    			log_message('debug', 'Module defined and loaded: ' . $module);
	    		}
	    	}
	    }
	}
}