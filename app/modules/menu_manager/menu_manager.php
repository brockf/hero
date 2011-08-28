<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
* Menu Manager Module Definition
*
* Declares the module, update code, etc.
*
* @author Electric Function, Inc.
* @copyright Electric Function, Inc.
* @package Hero Framework
*
*/

class Menu_manager extends Module {
	var $version = '1.02';
	var $name = 'menu_manager';

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
		$this->CI->admin_navigation->child_link('design',20,'Menu Manager',site_url('admincp/menu_manager'));
	}
	
	/*
	* Pre-front Method
	*
	* Triggered prior to loading the frontend
	*/
	function front_preload () {
		$this->CI->smarty->addPluginsDir(APPPATH . 'modules/menu_manager/template_plugins/');
	}
	
	function update ($db_version) {
		if ($db_version < 1.01) {
			$this->CI->db->query('CREATE TABLE `menus_links` (
								  `menu_link_id` int(11) NOT NULL auto_increment,
								  `menu_id` int(11) NOT NULL,
								  `parent_menu_link_id` int(11),
								  `menu_link_type` varchar(25) NOT NULL,
								  `link_id` int(11),
								  `menu_link_text` varchar(150),
								  `menu_link_special_type` varchar(50),
								  `menu_link_external_url` varchar(250),
								  `menu_link_class` varchar(50),
								  `menu_link_privileges` varchar(255),
								  `menu_link_order` int(5),
								  PRIMARY KEY  (`menu_link_id`)
								) ENGINE=MyISAM AUTO_INCREMENT=1000 DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;');
		
			$this->CI->db->query('CREATE TABLE `menus` (
								  `menu_id` int(11) NOT NULL auto_increment,
								  `menu_name` varchar(200) NOT NULL,
								  PRIMARY KEY  (`menu_id`)
								) ENGINE=MyISAM AUTO_INCREMENT=1000 DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;');
		}
		
		if ($db_version < 1.02) {
			// we no longer use our caching library, but the CI standard
			// $this->CI->settings_model->make_writeable_folder(setting('path_writeable') . 'menu_cache', TRUE);
		}
		
		return $this->version;
	}
}