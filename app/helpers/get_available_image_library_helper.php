<?php

/**
* Get Available Image Library
*
* If ImageMagick is available, use it!  If nothing's there, throw an error.
*
* @return string Image library name
*
* @copyright Electric Function, Inc.
* @package Hero Framework
* @author Electric Function, Inc.
*/
function get_available_image_library () {
	$CI =& get_instance();
	
	if ($CI->config->item('image_library') and $CI->config->item('image_library') != '') {
		return $CI->config->item('image_library');
	}

	if (class_exists('Imagick')) {
		return 'ImageMagick';
	}
	elseif (function_exists('imagecreatetruecolor')) {
		$gd = gd_info();
		
		// get the pure version number
		$gd_version = $gd['GD Version'];
		$gd_version = preg_replace('/[^0-9\.]/i','',$gd_version);
		$gd_version = trim($gd_version, ' .');
		
		if (version_compare('2.0',$gd_version) === 1) {
			return 'GD';
		}
		else {
			return 'GD2';
		}
	}
	else {
		die(show_error('No image library could be found.'));
	}
}