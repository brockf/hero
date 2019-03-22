<?php

/**
* Filter out files from a CodeIgniter directory_map() array
*
* @param array $directory Output of directory_map()
* @param array $filters Files to not include
* @return array $directory_map
*
* @copyright Electric Function, Inc.
* @package Hero Framework
* @author Electric Function, Inc.
*/
function filter_directory ($directory, $filters = array()) {
	$directory_map = array();
	
	if (is_array($directory)) {
		foreach ($directory as $key => $file) {
			if (is_array($file)) {
				$file = filter_directory($file);
				$directory_map[] = $file;
			}
			elseif (!in_array($file, $filters)) {
				$directory_map[] = $file;
			}
		}
	}
	
	return $directory_map;
}