<?php

class Front_Controller extends MY_Controller {
	var $navigation;

	function __construct () {
		parent::__construct();
		
		// load all modules with control panel to build navigation, etc.
		$directory = APPPATH . 'modules/';
		$modules = directory_map($directory);
		
		// load each module definition file, for admincp navigation
		foreach ($modules as $module => $module_folder) {
			MY_Loader::define_module($module . '/');
		}
	}
}