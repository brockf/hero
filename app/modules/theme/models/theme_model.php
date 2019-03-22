<?php

/**
* Theme Model
*
* Manages content
*
* @author Electric Function, Inc.
* @package Hero Framework

*/

class Theme_model extends CI_Model
{
	private $CI;
	
	function __construct()
	{
		parent::__construct();
		
		$this->CI =& get_instance();
	}
	
	function get_themes() {
		$themes = array();
		
		if ($handle = opendir(FCPATH . 'themes')) {
		    while (false !== ($file = readdir($handle))) {
		        if (is_dir(FCPATH . 'themes/' . $file) and $file != '.' and $file != '..') {
		        	if (strpos($file, '_') !== 0) {
		        		$themes[] = $file;
		        	}
		        }
		    }	
		}

		return $themes;
	}
	
	function preview_image ($theme) {
		$this->CI->load->helper('image_thumb');
		
		// preview file should be here
		$preview = FCPATH . 'themes/' . $theme . '/preview.jpg';
	
		if (file_exists($preview)) {
			return image_thumb($preview, 160, 208);
		}
		else {
			$default = FCPATH . 'themes/_common/preview.jpg';
			
			return image_thumb($default, 160, 208);
		}
	}
	
	function install_url ($theme) {
		return site_url('admincp/theme/install/' . $theme);
	}
}