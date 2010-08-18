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
		$this->CI->smarty->addPluginsDir(APPPATH . 'modules/jerrymail/template_plugins/');
	
		// presale code
		$redirect_to_presale_page = TRUE;
		if ($this->CI->user_model->logged_in() and $this->CI->user_model->is_admin()) {
			$redirect_to_presale_page = FALSE;
		}
		elseif ($this->CI->router->fetch_class() == 'user') {
			$redirect_to_presale_page = FALSE;
		}
		elseif ($this->CI->router->fetch_class() == 'store' and $this->CI->router->fetch_method() == 'cart') {
			$redirect_to_presale_page = FALSE;
		}
		elseif ($this->CI->router->fetch_class() == 'checkout') {
			$redirect_to_presale_page = FALSE;
		}
		elseif ($this->CI->router->fetch_class() == 'subscriptions') {
			$redirect_to_presale_page = FALSE;
		}
		elseif ($_SERVER['REQUEST_URI'] == 'presale') {
			$redirect_to_presale_page = FALSE;
		}
		
		if ($redirect_to_presale_page == TRUE) {
			header('Location: ' . site_url('presale'));
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
		}
		
		if ($db_version < '1.01') {
			$this->CI->load->model('link_model');
			$this->CI->link_model->gen_routes_file();
		}
								
		// return current version
		return $this->version;
	}
}