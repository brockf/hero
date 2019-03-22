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
*
* @copyright Electric Function, Inc.
* @package Hero Framework
* @author Electric Function, Inc.
*/
function module_installed () {
	$CI =& get_instance();
	
	$modules = func_get_args();
	
	if (empty($modules)) {
		return FALSE;
	}
	
	// create our cache-holder
	if (!isset($GLOBALS['modules_installed'])) {
		$GLOBALS['modules_installed'] = array();
	}
	
	foreach ($modules as $module) {
		if (isset($GLOBALS['modules_installed'][$module]))  {
			if ($GLOBALS['modules_installed'][$module] == FALSE) {
				return FALSE;
			}
		}
	
		$result = $CI->db->select('module_id')
						 ->where('module_name',$module)
						 ->where('module_installed','1')
						 ->get('modules');
						 
		if ($result->num_rows() == 0) {
			$GLOBALS['modules_installed'][$module] = FALSE;
		
			return FALSE;
		}
		else {
			$GLOBALS['modules_installed'][$module] = TRUE;
		}
	}
	
	return TRUE;
}