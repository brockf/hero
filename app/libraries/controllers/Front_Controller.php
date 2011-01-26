<?php

class Front_Controller extends MY_Controller {
	function __construct () {
		parent::__construct();
		
		$this->load->helper('ssl');
		
		// we are in the frontend
		define("_FRONTEND","TRUE");
		
		// set current theme
		$this->config->set_item('current_theme',setting('theme'));
		
		// load Smarty template engine and configure it
		$this->load->library('smarty');
		$this->smarty->initialize();
		
		// if we don't have a theme, we'll setup the default theme
		// we do it after Smarty because some module definitions reference the Smarty library
		if (setting('theme') == FALSE and setting('default_theme')) {
			$this->settings_model->update_setting('theme',$this->config->item('default_theme'));
			
			// install the default theme
			$install_file = FCPATH . 'themes/' . $this->config->item('default_theme') . '/install.php';
			
			if (file_exists($install_file)) {
				include($install_file);
			}
			
			// redirect to home page
			redirect('/');
			die();
		}
		
		
		// init hooks
		$this->load->library('app_hooks');
		
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