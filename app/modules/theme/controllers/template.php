<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/*
* Theme Module - Template Maps
*
* Maps URL to a template
*
* @author Electric Function, Inc.
* @copyright Electric Function, Inc.
* @package Hero Framework
*
*/

class Template extends Front_Controller {
	function __construct() {
		parent::__construct();
	}
	
	function view ($url_path) {
		// we may have a URL with "/" in it, so let's combine all the arguments into a URL string
		$args = func_get_args();
		$url_path = implode('/',$args);
		// end URL from arguments code
		
		$this->load->model('link_model');
		$link = $this->link_model->get_links(array('url_path' => $url_path));
		
		if (empty($link)) {
			return show_404($url_path);
		}
		
		$link = $link[0];
		
		// return the template via the link's stored parameter (the mapped template file)
		return $this->smarty->display($link['parameter']);
	}
}