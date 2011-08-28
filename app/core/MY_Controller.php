<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
* Controller Parent Class
*
* This class is inherited by both the Front_Controller and Admincp_Controller.
* It should include any code they have in common (not much).
* It's best use is to apply something across ALL pages in the framework without
* doing something ugly like touching index.php
*
* @copyright Electric Function, Inc.
* @package Hero Framework
* @author Electric Function, Inc.
*/

class MY_Controller extends CI_Controller {
	public function __construct() {
		parent::__construct();
		
		// we don't want to do anything if we're not installed
		$this->load->helper('install_redirect_helper');
		install_redirect();
		
		// this is a config.php setting.  if TRUE, it shows an awesome
		// profiler at the bottom of each page.
		if ($this->config->item('debug_profiler') === TRUE) {
			$this->output->enable_profiler(TRUE);
		}
	}	
}