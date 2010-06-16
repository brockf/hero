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
	function __construct() {
		parent::CI_model();
	}
	
	function get_module ($name) {
		$this->db->where('module_name',$name);
		
		$result = $this->db->get('modules');
		
		if ($result->num_rows() == 0) {
			return FALSE;
		}
		
		$module = $result->row_array();
		
		$module = array(
						'name' => $module['module_name'],
						'version' => $module['module_version']
					);
		
		return $module;
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