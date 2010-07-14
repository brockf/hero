<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
* Menu Manager Module Definition
*
* Declares the module, update code, etc.
*
* @author Electric Function, Inc.
* @copyright Electric Function, Inc.
* @package Electric Publisher
*
*/

class Menu_manager extends Module {
	var $version = '1.01';
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
		$CI =& get_instance();
		
		$CI->navigation->child_link('design',20,'Menu Manager',site_url('admincp/menu_manager'));
	}
	
	function update ($db_version) {
		if ($db_version < 1.01) {
			$this->CI->db->query('DROP TABLE IF EXISTS `menus_links`');
			$this->CI->db->query('DROP TABLE IF EXISTS `menus`');
		
			$this->CI->db->query('CREATE TABLE `menus_links` (
								  `menu_link_id` int(11) NOT NULL auto_increment,
								  `menu_id` int(11) NOT NULL,
								  `parent_menu_link_id` int(11),
								  `menu_link_type` varchar(25) NOT NULL,
								  `link_id` int(11),
								  `menu_link_module` varchar(150),
								  `menu_link_name` varchar(150),
								  `menu_link_special_type` varchar(50),
								  `menu_link_external_url` varchar(250),
								  `menu_link_privileges` varchar(255),
								  `menu_link_require_active_parent` tinyint(1),
								  `menu_link_order` int(5),
								  PRIMARY KEY  (`menu_link_id`)
								) ENGINE=MyISAM AUTO_INCREMENT=1000 DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;');
		
			$this->CI->db->query('CREATE TABLE `menus` (
								  `menu_id` int(11) NOT NULL auto_increment,
								  `menu_name` varchar(200) NOT NULL,
								  PRIMARY KEY  (`menu_id`)
								) ENGINE=MyISAM AUTO_INCREMENT=1000 DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;');
		}
		
		return $this->version;
	}
}