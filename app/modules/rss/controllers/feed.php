<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/*
* Feed Module
*
* Displays a feed
*
* @author Electric Function, Inc.
* @copyright Electric Function, Inc.
* @package Hero Framework
*
*/

class Feed extends Front_Controller {
	function __construct() {
		parent::__construct();
	}
	
	function view ($url_path) {
		// we may have a URL with "/" in it, so let's combine all the arguments into a URL string
		$args = func_get_args();
		$url_path = implode('/',$args);
		// end URL from arguments code
		
		$this->load->model('rss/rss_model');
		
		$feed_id = $this->rss_model->get_feed_id($url_path);
		
		$feed = $this->rss_model->get_feed($feed_id);
		
		if (empty($feed_id) or empty($feed)) {
			return show_404($url_path);
		}
		
		// get feed
		$content = $this->rss_model->get_feed_content($feed_id);
		
		if (empty($content)) {
			return show_404($url_path);
		}
		
		// show content
		$this->smarty->assign('content',$content);
		$this->smarty->assign($feed);
		
		header("Content-Type: application/rss+xml");
		return $this->smarty->display($feed['template']);
	}
}