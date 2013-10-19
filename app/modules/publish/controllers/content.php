<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/*
* Content Module
*
* Displays single content item
*
* @author Electric Function, Inc.
* @copyright Electric Function, Inc.
* @package Hero Framework
*
*/

class Content extends Front_Controller {
	function __construct() {
		parent::__construct();
	}
	
	function view ($url_path) {
		// we may have a URL with "/" in it, so let's combine all the arguments into a URL string
		$args = func_get_args();
		$url_path = implode('/',$args);
		// end URL from arguments code
		
		$this->load->model('publish/content_model');
		
		$content_id = $this->content_model->get_content_id($url_path);
		
		if (empty($content_id)) {
			return show_404($url_path);
		}
		
		// administrators used to not have to wait to see content, but that's not good enough anymore
		// because (a) it's confusing and (b) they are not auto-logged-in to the frontend even though
		// they are logged into the control panel
		//
		// so now we have the ?preview=[key] appendage to the URL which activates "preview mode"
		$preview_mode = FALSE;
		if ($this->input->get('preview')) {
			$this->load->library('encrypt');
			$preview_key = $this->encrypt->decode(base64_decode($this->input->get('preview')));
			
			if ($preview_key == $url_path) {
				$preview_mode = TRUE;
			}
		}
		
		$allow_future = ($preview_mode == TRUE) ? TRUE : FALSE;
		
		$content = $this->content_model->get_content($content_id, $allow_future);
		
		// does this content exist?
		if (empty($content)) {
			if ($this->input->get('preview')) {
				return show_error('Your preview key cannot be validated, and so we are showing the standard 404 page.');
			}
			else {
				return show_404($url_path);
			}
		}
		
		// do they have permissions to see content?
		if (!$this->user_model->in_group($content['privileges'])) {
			$this->load->helper('paywall/paywall');
			if (paywall($content, 'content') !== FALSE) {
				die();
			}
		}
		
		// trigger show_content hook
		// prep the hook with data
		$this->app_hooks->data_var('content_id', $content_id);
		 
		// trigger with 1 additional arguments
		$this->app_hooks->trigger('view_content', $content_id);
		 
		// be kind, reset the hook's data so that the next hook trigger doesn't accidentally pass email data that doesn't exist
		$this->app_hooks->reset();
		
		// show content
		$this->smarty->assign($content);
		
		// should we format this is as XML?
		if (strpos($url_path,'.xml') !== FALSE) {
			header("Content-Type: text/xml");
		}
		
		return $this->smarty->display($content['template']);
	}
}