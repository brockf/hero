<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
* Jerrypresale Module Definition
*
* Declares the module, update code, etc.
*
* @author Electric Function, Inc.
* @copyright Electric Function, Inc.
* @package Electric Publisher 
*
*/

class Jerrypresale_module extends Module {
	var $version = '1.01';
	var $name = 'jerrypresale';

	function __construct () {
		// set the active module
		$this->active_module = $this->name;
		
		parent::__construct();
	}
	
	/*
	* Pre-admin function
	*
	* Initiate navigation in control panel
	*/
	function admin_preload ()
	{
		//$this->CI->navigation->child_link('configuration',45,'Jerry Mail',site_url('admincp/jerrymail'));
	}
	
	/*
	* Pre-front Method
	*
	* Triggered prior to loading the frontend
	*/
	function front_preload () {
		$this->CI->smarty->addPluginsDir(APPPATH . 'modules/jerrypresale/template_plugins/');
		
		// let's make sure that jerrymail knows we're in PRESALE mode
		define('PRESALE_MODE','TRUE');
		
		// are the limited to the presale site?
		if (!$this->CI->user_model->logged_in() or !$this->CI->user_model->is_admin()) {
			define('PRESALE_ONLY','TRUE');
		}
	
		// presale redirect
		$redirect_to_presale_page = TRUE;
		if ($this->CI->user_model->logged_in() and $this->CI->user_model->is_admin()) {
			$redirect_to_presale_page = FALSE;
		}
		elseif ($this->CI->router->fetch_class() == 'users') {
			$redirect_to_presale_page = FALSE;
		}
		elseif ($this->CI->router->fetch_class() == 'cron') {
			$redirect_to_presale_page = FALSE;
		}
		elseif ($this->CI->router->fetch_class() == 'store' and $this->CI->router->fetch_method() == 'cart') {
			$redirect_to_presale_page = FALSE;
		}
		elseif ($this->CI->router->fetch_class() == 'store' and $this->CI->router->fetch_method() == 'update_cart') {
			$redirect_to_presale_page = FALSE;
		}
		elseif ($this->CI->router->fetch_class() == 'checkout') {
			$redirect_to_presale_page = FALSE;
		}
		elseif ($this->CI->router->fetch_class() == 'subscriptions' and $this->CI->router->fetch_method() == 'add_to_cart') {
			$redirect_to_presale_page = FALSE;
		}
		elseif (strpos($_SERVER['REQUEST_URI'],'/presale') !== FALSE) {
			$redirect_to_presale_page = FALSE;
		}
		
		if ($redirect_to_presale_page == TRUE) {
			header('Location: ' . site_url('presale'));
			die();
		}
		
		// don't show the normal thank you page
		if ($this->CI->router->fetch_class() == 'checkout' and $this->CI->router->fetch_method() == 'complete') {
			header('Location: ' . site_url('presale/thanks'));
			die();
		}
	}
	
	/*
	* Module update
	*
	* @param int $db_version The current DB version
	*
	* @return int The current software version, to update the database
	*/
	function update ($db_version) {
		if ($db_version < '1.00') {
			$this->CI->load->model('link_model');
			$this->CI->link_model->new_link('presale', FALSE, 'Presale', 'Presale', 'jerrypresale', 'presale', 'index');
			$this->CI->link_model->new_link('presale/thanks', FALSE, 'Presale', 'Presale', 'jerrypresale', 'presale', 'thanks');
		}
		
		if ($db_version < '1.01') {
			$this->CI->load->model('link_model');
			$this->CI->link_model->gen_routes_file();
		}
								
		// return current version
		return $this->version;
	}
}