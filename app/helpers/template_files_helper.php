<?php

/*
* Get Template Files
*
* Returns an array of all template files in a theme's directory
*
* @return array
*
* @copyright Electric Function, Inc.
* @package Hero Framework
* @author Electric Function, Inc.
*/function template_files () {
	$CI = get_instance();
	$CI->load->helper('directory');
	
	$files = directory_map(FCPATH . 'themes/' . setting('theme'));
	
	$filtered_files = array();
	$filtered_files = parse_template_files_array($files, $filtered_files);
	
	asort($filtered_files);
	
	return $filtered_files;
}

function parse_template_files_array ($files, $return = array(), $prefix = '') {
	foreach ($files as $key => $file) {
		$extension = (!is_array($file) and strpos($file, '.') !== FALSE) ? end(explode('.', $file)) : '';
		
		if (is_array($file)) {
			$return = array_merge($return,parse_template_files_array($file, $return, $prefix . $key . '/'));
		}
		elseif (strpos($file, '.') !== FALSE and ($extension == 'thtml' or $extension == 'txml')) {
			$return[$prefix . $file] = $prefix . $file;
		}
	}
	
	return $return;
}