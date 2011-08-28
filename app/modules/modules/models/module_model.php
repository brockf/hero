<?php

/**
* Module Model 
*
* Contains all the methods used to create, and update modules
*
* @author Electric Function, Inc.
* @package Hero Framework
* @copyright Electric Function, Inc.
*/

class Module_model extends CI_Model {
	public $modules_cache;
	private $CI;
	
	function __construct() {
		parent::__construct();
		
		$this->CI =& get_instance();
		
		// pre-cache modules table
		$result = $this->db->get('modules');
		
		$this->modules_cache = array();
		
		foreach ($result->result_array() as $module) {
			$this->modules_cache[$module['module_name']] = array(
																'name' => $module['module_name'],
																'version' => ($module['module_version'] != '') ? $module['module_version'] : FALSE,
																'installed' => ($module['module_installed'] == '1') ? TRUE : FALSE,
																'ignored' => ($module['module_ignore'] == '1') ? TRUE : FALSE
																);
		}
		
		// alphabetically sort!
		ksort($this->modules_cache);
	}
	
	/**
	* Install Module
	*
	* @param string $module
	*
	* @return boolean
	*/
	function install ($module) {
		$this->db->update('modules', array('module_ignore' => '0', 'module_installed' => '1'), array('module_name' => $module));
		
		// look for install module
		if (file_exists(APPPATH . 'modules/' . $module . '/' . $module . '.php')) {
			// initiate module
			// this will run update() in the module definition file
			include_once(APPPATH . 'modules/' . $module . '/' . $module . '.php');
			
			// because of a name conflict, this may be called Modulename_module
			if (class_exists($module . '_module')) {
				$class_name = $module . '_module';
			}
			else {
				$class_name = $module;
			}
			
			$this->CI->module_definitions->$module = new $class_name;
			
			log_message('debug', 'Module installed: ' . $module);
		}
		
		// force the templates to recompile because of {module_installed} tags
		$this->CI->settings_model->update_setting('smarty_library','0');
		
		return TRUE;
	}
	
	/**
	* Uninstall Module
	*
	* @param string $module
	*
	* @return boolean
	*/
	function uninstall ($module) {
		// look for module definition file
		// to run uninstall()
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
			
			// look for uninstall method
			if (method_exists($this->CI->module_definitions->$module, 'uninstall')) {
				$this->CI->module_definitions->$module->uninstall();
			}
		}
	
		$this->db->update('modules', array('module_installed' => '0', 'module_ignore' => '1', 'module_version' => ''), array('module_name' => $module));
		
		// force the templates to recompile because of {module_installed} tags
		$this->CI->settings_model->update_setting('smarty_library','0');
	}
	
	/**
	* Get Module
	*
	* @param string $name
	*
	* @return array
	*/
	function get_module ($name) {
		if (!isset($this->modules_cache[$name])) {
			return FALSE;
		}
		
		return $this->modules_cache[$name];
	}
	
	/**
	* Get Modules
	*
	* @return array 
	*/
	function get_modules () {
		return $this->modules_cache;
	}
	
	/**
	* Get Module Folders
	*
	* @return array
	*/
	function get_module_folders () {
		$directory = APPPATH . 'modules/'; 
		$this->CI->load->helper('directory');
		
		$modules = directory_map($directory, 1);
		
		return $modules;
	}
	
	/**
	* New Module
	*
	* @param string $name
	* @param float $version
	*
	* @return boolean 
	*/
	function new_module ($name, $version) {
		$insert_fields = array(
							'module_name' => $name,
							'module_version' => $version
						);
						
		$this->db->insert('modules',$insert_fields);
		
		return TRUE;
	}
	
	/**
	* Update Version
	*
	* @param string $name
	* @param float $version
	*
	* @return boolean 
	*/
	function update_version ($name, $version) {
		$this->db->update('modules',array('module_version' => $version, 'module_installed' => '1'), array('module_name' => $name));
		
		// update cache
		$this->modules_cache[$name]['version'] = $version;
		
		return TRUE;
	}
}