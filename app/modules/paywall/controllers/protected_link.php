<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
* Protected Link Controller 
*
* Checks access privileges, and redirects to restricted content
*
* @author Electric Function, Inc.
* @copyright Electric Function, Inc.
* @package Hero Framework
*
*/

class Protected_link extends Front_Controller {
	function __construct() {
		parent::__construct();
	}
	
	function redirect ($url_path) {
		$this->load->model('link_model');
		$link = $this->link_model->get_links(array('url_path' => $url_path));
		
		if (empty($link)) {
			return show_404($url_path);
		}
		
		$link = $link[0];
		
		// get serialized url/groups data
		$data = unserialize($link['parameter']);
		
		if (empty($data)) {
			return show_error('Invalid link.');
		}
		
		// if this is an absolute link, we'll try and make it relative
		if (strpos($data['url'], $this->config->item('base_url')) === 0) {
			// it begins with the URL, so we can just strip this to get a relative path
			
			$data['url'] = substr_replace($data['url'], '', 0, strlen($this->config->item('base_url')));
		}
		elseif (strpos($data['url'], FCPATH) === 0) {
			$data['url'] = substr_replace($data['url'], '', 0, strlen(FCPATH)); 
		}
		
		// add APPPATH to make this an absolute path
		$data['url'] = FCPATH . $data['url'];
		
		// check permissions
		if (!$this->user_model->in_group($data['groups'])) {
			return show_error('Insufficient access privileges.');
		}
		else {
			// load and return file
			$this->load->helper('file_extension');
			
			// set filename
			$filename = (isset($data['filename']) and !empty($data['filename'])) ? $data['filename'] : $link['url_path'] . '.' . file_extension($data['url']);
			
			$extension = file_extension($data['url']);
			
			// get the mime type
			include(APPPATH . 'config/mimes.php');
			
			if (!isset($mimes[$extension])) {
				die(show_error('Failed to retrieve mime-type data for file extension "' . $extension . '".'));
			}
			
			$mime_type = $mimes[$extension];

			// some mime types are arrays...
			if (is_array($mime_type)) {
				$mime_type = $mime_type[0];
			}
			
			// don't limit to small files
			set_time_limit(0);

			header("Pragma: public"); // required
			header("Expires: 0");
			header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
			header("Cache-Control: private", FALSE); // required for certain browsers 
			header("Content-Type: " . $mime_type);
			header("Content-Disposition: attachment; filename=\"" . $filename . "\";");
			header("Content-Transfer-Encoding: binary");
			header("Content-Length: " . filesize($data['url']));
			
			readfile($data['url'], "r");

			die();
		}
	}
}