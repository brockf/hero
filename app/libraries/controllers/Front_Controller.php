<?php

class Front_Controller extends MY_Controller {
	function __construct () {
		parent::__construct();
		
		// we are in the frontend
		define("_FRONTEND","TRUE");
		
		// set current theme
		$this->config->set_item('current_theme',setting('theme'));
		
		// load Smarty template engine and configure it
		$this->load->library('smarty');
		
		// load all modules with control panel to build navigation, etc.
		$directory = APPPATH . 'modules/';
		$this->load->helper('directory');
		
		$modules = directory_map($directory);
		
		// load each module definition file, for admincp navigation
		foreach ($modules as $module => $module_folder) {
			MY_Loader::define_module($module . '/');
		}
	}
}