<?php

/**
* Module Model 
*
* Contains all the methods used to create, and update modules
*
* @author Electric Function, Inc.
* @package Electric Publisher

*/

class Module_model extends CI_Model {
	var $modules_cache;
	
	function __construct() {
		parent::CI_model();
		
		$result = $this->db->get('modules');
		
		foreach ($result->result_array() as $module) {
			$this->modules_cache[$module['module_name']] = array(
																'name' => $module['module_name'],
																'version' => $module['module_version']
																);
		}
	}
	
	function get_module ($name) {
		if (!isset($this->modules_cache[$name])) {
			return FALSE;
		}
		
		return $this->modules_cache[$name];
	}
	
	function new_module ($name, $version) {
		$insert_fields = array(
							'module_name' => $name,
							'module_version' => $version
						);
						
		$this->db->insert('modules',$insert_fields);
		
		return TRUE;
	}
	
	function update_version ($name, $version) {
		$this->db->update('modules',array('module_version' => $version), array('module_name' => $name));
		
		return TRUE;
	}
}