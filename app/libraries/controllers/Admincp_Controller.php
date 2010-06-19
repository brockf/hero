<?php

class Admincp_Controller extends MY_Controller {
	var $navigation;

	function __construct () {
		parent::__construct();
		
		define("_CONTROLPANEL","TRUE");
		
		// are they logged in?
		if ($this->user_model->logged_in() and !$this->user_model->is_admin()) {
			die(show_error('You do not have control panel privileges.'));
		}
		elseif (!$this->user_model->logged_in() and $this->router->fetch_class() != 'login') {
			redirect(site_url('admincp/login'));
		}
	
		// store dynamically-generated navigation
		$this->load->library('navigation');
		
		$this->navigation->parent_link('dashboard','Dashboard');
		$this->navigation->parent_link('publish','Publish');
		$this->navigation->parent_link('storefront','Storefront');
		$this->navigation->parent_link('members','Members');
		$this->navigation->parent_link('reports','Reports');
		$this->navigation->parent_link('configuration','Configuration');
		
		$this->navigation->child_link('dashboard',1,'Dashboard',site_url('admincp'));
		
		// admin-specific loading
		$this->load->model('admincp/notices');
		$this->load->helper('admincp/get_notices');
		$this->load->helper('admincp/dataset_link');
		$this->load->helper('directory');
		$this->load->helper('form');
	
		// load all modules with control panel to build navigation, etc.
		$directory = APPPATH . 'modules/';
		$modules = directory_map($directory);
		
		// load each module definition file, for admincp navigation
		foreach ($modules as $module => $module_folder) {
			MY_Loader::define_module($module . '/');
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