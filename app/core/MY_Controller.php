<?php (defined('BASEPATH')) OR exit('No direct script access allowed');

class MY_Controller extends Controller {
	public function __construct() {
		parent::__construct();
		
		// we don't want to do anything if we're not installed
		$this->load->helper('install_redirect_helper');
		install_redirect();
		
		if ($this->config->item('debug_profiler') === TRUE) {
			$this->output->enable_profiler(TRUE);
		}
	}	
}