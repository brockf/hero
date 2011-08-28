<?php

/*
* Image Thumb
* 
* Generates an image thumbnail for a local image.  Caches thumbnails for 2 minutes before re-generating.
*
* @param string $image_path Local path to image
* @param int $height Maximum image height
* @param int $width Maximum image width
*
* @return string Web path to image thumbnail
*
* @copyright Electric Function, Inc.
* @package Hero Framework
* @author Electric Function, Inc.
*/
function image_thumb ($image_path, $height, $width)
{
	if (!file_exists($image_path)) {
		die(show_error('Image file does not exist for thumb: "' . $image_path . '".'));
	}

	// Get the CodeIgniter super object
	$CI =& get_instance();
	$CI->load->helper('file_extension');
	
	// take off "px" from $height and $width
	if (!empty($height) and strstr($height,'px')) {
		$height = str_replace('px','',$height);
	}
	
	if (!empty($width) and strstr($width, 'px')) {
		$width = str_replace('px','',$width);
	}
	
	// we NEED a height and width for the maintain ratio measure to work
	// set it to some very high measure so that it is ignored
	if (empty($height)) {
		$height = '15000';
	}
	
	if (empty($width)) {
		$width = '15000';
	}
	
	// are the height and width already OK?
	list($current_width, $current_height, $current_type, $current_attr) = getimagesize($image_path);
	
	if ($current_width == $width and $current_height == $height) {
		// strip base path from path
		$image_path = str_replace(FCPATH, '', $image_path);
		return site_url($image_path);
	}
	
	// get modification date of the source file
	$last_modified = filemtime($image_path);
	
	// generate image thumbnail filename from full path and dimensions
	$file_name = md5($last_modified . $image_path . $height . $width) . '.' . file_extension($image_path);

	// Path to image thumbnail
	$image_thumb = setting('path_image_thumbs') . $file_name;
	
	// if the file has been modified since the last thumb was generated, it will have a new md5 and thus the cache won't exist yet
	if (!file_exists($image_thumb)) {
		// load library
		$CI->load->library('image_lib');
		$CI->load->helper('get_available_image_library');
		
		// configuration
		$config['image_library'] = get_available_image_library();
		$config['library_path'] = $CI->config->item('image_library_path');
		$config['source_image']	= $image_path;
		$config['new_image'] = $image_thumb;
		$config['maintain_ratio'] = TRUE;
		if (!empty($height)) {
			$config['height'] = $height;
		}
		if (!empty($width)) {
			$config['width'] = $width;
		}
		
		$CI->image_lib->initialize($config);
		if (!$CI->image_lib->resize()) {		
			die(show_error($CI->image_lib->display_errors()));
		}
		$CI->image_lib->clear();
	}
	
	return site_url(str_replace(FCPATH,'',$image_thumb));
}

/* End of file image_thumb_helper.php */
/* Location: ./app/helpers/image_thumb_helper.php */
