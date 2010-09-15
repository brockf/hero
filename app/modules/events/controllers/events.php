<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/*
* Event Module
*
* Displays event list or single event
*
* @author Electric Function, Inc.
* @copyright Electric Function, Inc.
* @package Electric Publisher
*
*/

class Events extends Front_Controller {
	function __construct() {
		parent::__construct();
	}

	function index () {
		$notice = $this->session->flashdata('notice');
		
		// get all events
		$this->load->model('events/events_model');
		$events = $this->events_model->get_events();
			
		$this->smarty->assign('title','Events');
		$this->smarty->assign('events',$events);
		
		return $this->smarty->display('events.thtml');
	}
		
	function view ($url_path) {
		$this->load->model('publish/content_model');
		
		$content_id = $this->content_model->get_content_id($url_path);
		
		if (empty($content_id)) {
			return show_404($url_path);
		}
		
		// administrators don't have to wait to see content
		$allow_future = ($this->user_model->logged_in() and $this->user_model->is_admin()) ? TRUE : FALSE;
		
		$content = $this->content_model->get_content($content_id, $allow_future);
		
		if (empty($content)) {
			return show_404($url_path);
		}
		
		// show content
		$this->smarty->assign($content);
		return $this->smarty->display($content['template']);
	}
}