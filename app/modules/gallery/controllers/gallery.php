<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/*
* Gallery Module
*
* Displays a gallery
*
* @author Electric Function, Inc.
* @copyright Electric Function, Inc.
* @package Electric Framework
*
*/

class Gallery extends Front_Controller {
	function __construct() {
		parent::__construct();
	}
	
	function view ($url_path) {
		// we may have a URL with "/" in it, so let's combine all the arguments into a URL string
		$args = func_get_args();
		$url_path = implode('/',$args);
		// end URL from arguments code
		
		// load gallery
		$this->load->model('publish/content_model');
		
		$content_id = $this->content_model->get_content_id($url_path);
		
		if (empty($content_id)) {
			return show_404($url_path);
		}
		
		$content = $this->content_model->get_content($content_id);
		
		// does this content exist?
		if (empty($content)) {
			return show_404($url_path);
		}
		
		// load images into gallery
		$this->load->model('gallery/gallery_image_model');
		$content['images'] = $this->gallery_image_model->get_images($content['id']);
		
		// do they have permissions to see content?
		if (!$this->user_model->in_group($content['privileges'])) {
			$this->load->helper('paywall/paywall');
			if (paywall($content, 'content') !== FALSE) {
				die();
			}
		}
		
		// are we downloading as a zip?
		if ($this->input->get('download') == 'zip') {
			return $this->download_zip($content);
		}
		
		// show content
		$this->smarty->assign($content);
		return $this->smarty->display('gallery.thtml');
	}
	
	/**
	* Download as Zip
	*
	* @param array $content The full content array, including "images" key
	*
	* @return download file
	*/
	function download_zip ($content = array()) {
		$this->load->library('zip');
		
		foreach ($content['images'] as $image) {
			$this->zip->read_file($image['path']);
		}
		
		// Download the file to your desktop. Name it "my_backup.zip"
		return $this->zip->download($content['url_path'] . '.zip'); 
	}
}