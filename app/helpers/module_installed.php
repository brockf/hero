<?php

/**
* Module Installed
*
* Quickly check to see if a module is installed
*
* @param string $name
* @param string $name_2
* @param string ...
*
* @return boolean
*/

function module_installed () {
	$CI =& get_instance();
	
	$modules = func_get_args();
	
	if (empty($modules)) {
		return FALSE;
	}
	
	foreach ($modules as $module) {
		$result = $CI->db->select('module_id')
						 ->where('module_name',$module)
						 ->where('module_installed','1')
						 ->get();
						 
		if ($result->num_rows() == 0) {
			return FALSE;
		}
	}
	
	return TRUE;
}