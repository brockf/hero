<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
* Admin Controller
*
* This controller is extended by all control panel controllers in modules.  It forces the
* user to be logged in, initializes certain universal control panel libraries, initializes
* the navigation with the parent links (Members, Storefront, etc.), loads modules,
* loads the app hooks engine, and initializes the KCFinder for CKEditor.
*
* @copyright Electric Function, Inc.
* @package Hero Framework
* @author Electric Function, Inc.
*/

class Admincp_Controller extends MY_Controller {
	var $navigation;

	function __construct () {
		parent::__construct();
		
		// by defining _CONTROLPANEL, certain functionality can be modified to be appropriate to this context
		define("_CONTROLPANEL","TRUE");
		
		// load the SSL helper, and redirect to HTTPS if necessary (or to HTTP)
		$this->load->helper('ssl');
		
		// load notices library (display success/error messages at top of screen)
		$this->load->library('notices');
		$this->load->helper('admincp/get_notices');
		
		// are they logged in? and an administrator?
		if ($this->user_model->logged_in() and !$this->user_model->is_admin()) {
			$this->notices->SetError('You are logged in but do not have control panel privileges.');
			redirect(site_url('admincp/login'));
			die();
		}
		elseif (!$this->user_model->logged_in() and $this->router->fetch_class() != 'login') {
			redirect(site_url('admincp/login'));
			die();
		}
	
		// store dynamically-generated navigation
		$this->load->library('admin_navigation');
		
		// add basic navigation categories
		$this->admin_navigation->parent_link('dashboard','Dashboard');
		$this->admin_navigation->parent_link('publish','Publish');
		if (module_installed('store') or module_installed('billing') or module_installed('coupons')) {
			$this->admin_navigation->parent_link('storefront','Storefront');
		}
		$this->admin_navigation->parent_link('members','Members');
		$this->admin_navigation->parent_link('reports','Reports');
		$this->admin_navigation->parent_link('design','Design');
		$this->admin_navigation->parent_link('configuration','Configuration');
		
		$this->admin_navigation->child_link('dashboard',1,'Dashboard',site_url('admincp'));
		
		// admin-specific loading
		$this->load->helper('admincp/dataset_link');
		$this->load->helper('directory');
		$this->load->helper('form');
		$this->load->helper('admincp/admin_link');
		
		// load assets library (include stylesheets and javascript files dynamically)
		$this->load->library('head_assets');
		
		// load caching library
		$this->load->driver('cache');
		
		// init hooks
		$this->load->library('app_hooks');
		
		// load all modules with control panel to build navigation, etc.
		$modules = $this->module_model->get_module_folders();
		
		// first, reset module definitions so that we run them all as a "backend" call and their preloads get called
		$this->module_definitions = new stdClass();
		
		foreach ($modules as $module) {
			MY_Loader::define_module($module . '/');
		}
		
		// define WYSIWYG session variables for file uploading
		@session_start();
		$_SESSION['KCFINDER'] = array();
		$_SESSION['KCFINDER']['disabled'] = FALSE;
		
		// Safari base_href fix
		$url = parse_url(base_url());
		$this->load->library('user_agent');
		// if they are using Safari and don't have Hero installed in a sub-folder, this prefix "/" fixes the problem
		if (stripos($this->agent->browser(),'safari') !== FALSE and trim($url['path'], '/') == '') {
			$prefix = '/';
		}
		else {
			$prefix = '';
		}
		$_SESSION['KCFINDER']['uploadURL'] = $prefix . str_replace(FCPATH,'',setting('path_editor_uploads'));
		$_SESSION['KCFINDER']['uploadDir'] = rtrim(setting('path_editor_uploads'),'/');
		
		// check cronjob is active!
		if (setting('cron_last_update') == FALSE or ((time() - strtotime(setting('cron_last_update'))) > (60*60*24))) {
			$this->notices->SetError('WARNING!  Your cronjob is not running properly.  <a href="' . site_url('admincp/reports/cronjob') . '">Click here for details</a>');
		}
	}
}