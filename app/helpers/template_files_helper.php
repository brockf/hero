<?php

/*
* Get Template Files
*
* Returns an array of all template files in a theme's directory
*/
function template_files () {
	$CI = get_instance();
	$CI->load->helper('directory');
	
	$files = directory_map(FCPATH . 'themes/' . setting('theme'));
	
	$filtered_files = array();
	foreach ($files as $file) {
		if (end(explode('.', $file)) == 'thtml') {
			$filtered_files[$file] = $file;
		}
	}
	
	unset($CI);
	
	return $filtered_files;
}