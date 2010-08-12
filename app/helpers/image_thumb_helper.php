<?php

/*
* Image Thumb
* 
* Generates an image thumbnail for a local image
*
* @param string $image_path Local path to image
* @param int $height Maximum image height
* @param int $width Maximum image width
*
* @return string Web path to image thumbnail
*/
function image_thumb ($image_path, $height = FALSE, $width = FALSE)
{
	// Get the CodeIgniter super object
	$CI =& get_instance();
	$CI->load->helper('file_extension');
	
	// take off "px" from $height and $width
	if (strstr($height,'px')) {
		$height = str_replace('px','',$height);
	}
	
	if (strstr($width, 'px')) {
		$width = str_replace('px','',$width);
	}
	
	// generate image thumbnail filename from full path and dimensions
	$file_name = md5($image_path . $height . $width) . '.' . file_extension($image_path);

	// Path to image thumbnail
	$image_thumb = setting('path_image_thumbs') . $file_name;
	
	$modified_time = file_exists($image_thumb) ? filemtime($image_thumb) : FALSE;
	
	if (!file_exists($image_thumb) or ($modified_time and (time() - $modified_time > 120)))
	{
		// load library
		$CI->load->library('image_lib');

		// configuration
		$config['image_library']	= 'gd2';
		$config['source_image']		= $image_path;
		$config['new_image']		= $image_thumb;
		$config['maintain_ratio']	= TRUE;
		if ($height) {
			$config['height']			= $height;
		}
		if ($width) {
			$config['width']			= $width;
		}
		
		$CI->image_lib->initialize($config);
		$CI->image_lib->resize();
		$CI->image_lib->clear();
	}

	return site_url(str_replace(FCPATH,'',$image_thumb));
}

/* End of file image_thumb_helper.php */
/* Location: ./application/helpers/image_thumb_helper.php */
