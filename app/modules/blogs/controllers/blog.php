<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/*
* Blog Module
*
* Displays a blog
*
* @author Electric Function, Inc.
* @copyright Electric Function, Inc.
* @package Hero Framework
*
*/

class Blog extends Front_Controller {
	function __construct() {
		parent::__construct();
	}
	
	function view ($url_path) {
		// we may have a URL with "/" in it, so let's combine all the arguments into a URL string
		$args = func_get_args();
		$url_path = implode('/',$args);
		// end URL from arguments code
		
		$this->load->model('blogs/blog_model');
		
		$blog_id = $this->blog_model->get_blog_id($url_path);

		$blog = $this->blog_model->get_blog($blog_id);
		
		if (empty($blog_id) or empty($blog)) {
			return show_404($url_path);
		}
		
		// get blog
		$content = $this->blog_model->get_blog_content($blog_id, $this->input->get('page'));

		// do they have permissions?
		if (!$this->user_model->in_group($blog['privileges'])) {
			$this->load->helper('paywall/paywall');
			if (paywall($blog, 'content') !== FALSE) {
				die();
			}
		}
				
		// get pagination
		$pagination = $this->blog_model->get_blog_pagination($blog_id, site_url($blog['url_path']) . '?', $this->input->get('page'));
		
		// show content
		$this->smarty->assign('content',$content);
		$this->smarty->assign('pagination',$pagination);
		$this->smarty->assign($blog);
		
		return $this->smarty->display($blog['template']);
	}
}