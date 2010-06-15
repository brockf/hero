<?php

/**
* Auto-Updater
*
* Update the current system, if the database is out of date
*
* @version 1.0
* @author Electric Function, Inc.
* @package Electric Publisher

*/

class Auto_updater {
	function Auto_updater () {
		$CI =& get_instance();
		
		$software_version = $CI->config->item('app_version');
		
		// track the update files to run
		$run_updates = array();
		
		// now let's check the DB version (if it exists)
		if (!$CI->db->table_exists('system')) {
			// no DB version tracker
			$db_version = '0';
		}
		else {
			// get DB version
			$query = $CI->db->get('system');
			$version = $query->row();
			
			$db_version = $version->db_version;
			unset($version);
		}
		
		// are we up-to-date?
		if ($software_version > $db_version) {
			// check for update files to run
			$CI->load->helper('directory');
			
			$files = directory_map(APPPATH . 'updates');
			
			foreach ($files as $file) {
				if ($file != 'install.php') {
					$file_version = str_replace('.php','',$file);
					
					if ($file_version > $db_version) {
						$run_updates[] = $file_version;
					}
				}
			}
		}
		
		// run updates?
		if (!empty($run_updates)) {
			// make sure we run the earlier updates first
			sort($run_updates);
			
			foreach ($run_updates as $update) {
				include(APPPATH . 'updates/' . $update . '.php');
			}
		}
		
		// update database version
		$CI->db->query('UPDATE `system` SET `db_version`=\'' . $software_version . '\'');
	}
}