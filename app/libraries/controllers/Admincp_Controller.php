<?php

class Admincp_Controller extends MY_Controller {
	var $navigation;

	function __construct () {
		parent::__construct();
	
		// store dynamically-generated navigation
		$this->navigation = array();
		
		// admin-specific loading
		$this->load->model('admincp/notices');
		$this->load->helper('admincp/get_notices');
		$this->load->helper('admincp/dataset_link');
		$this->load->helper('directory');
	
		// load all modules with control panel to build navigation, etc.
		$directory = APPPATH . 'modules/';
		$modules = directory_map($directory);
		
		// load control panel files for each eligible module
		foreach ($modules as $module => $module_folder) {
			$module_names = $this->module_names($module);
			
			if ($module != "." and $module != ".." and isset($module_folder['controllers']) and in_array($module_names['filename'],$module_folder['controllers'])) {
				require($directory . $module . '/libraries/' . $module_names['filename']);
				
				// load Module::initialize to generate navigation and other things
				call_user_func(array($module_names['class_name'],'initialize'),$this);
			}
		}
	}
	
	function module_names ($directory) {
		$name = str_replace('.php','',strtolower($directory));
		$class_name = ucfirst($name) . '_cp';
		$object_name = strtolower($class_name);
		$load_name = $name . '/' . $name . '_cp';
		$filename = ucfirst($name) . '_cp.php';
	
		return array(
					'name' => $name,
					'class_name' => $class_name,
					'object_name' => $object_name,
					'load_name' => $load_name,
					'filename' => $filename
				);
	}

}