<?php

/**
* Module Model 
*
* Contains all the methods used to create, and update modules
*
* @author Electric Function, Inc.
* @package Electric Framework
* @copyright Electric Function, Inc.
*/

class Module_model extends CI_Model {
	var $modules_cache;
	
	function __construct() {
		parent::__construct();
		
		$result = $this->db->get('modules');
		
		foreach ($result->result_array() as $module) {
			$this->modules_cache[$module['module_name']] = array(
																'name' => $module['module_name'],
																'version' => $module['module_version']
																);
		}
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
		$this->db->update('modules',array('module_version' => $version), array('module_name' => $name));
		
		// update cache
		$this->modules_cache[$name]['version'] = $version;
		
		return TRUE;
	}
}