<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
* Twitter Controller 
*
* @author Electric Function, Inc.
* @copyright Electric Function, Inc.
* @package Electric Framework
*
*/

class Twitter extends Front_Controller {
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
		
		$link = $this->db->select('link_url_path')->from('links')->where('link_id',$link['parameter'])->get()->row_array();
		
		if (empty($link)) {
			return show_404($url_path);
		}
		
		// return the template via the link's stored parameter (the mapped template file)
		return redirect($link['link_url_path']);
	}
}