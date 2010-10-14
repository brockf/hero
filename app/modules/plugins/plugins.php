<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
* Plugins Module Definition
*
* Declares the module, update code, etc.
*
* @author Electric Function, Inc.
* @copyright Electric Function, Inc.
* @package Electric Publisher
*
*/

class Plugins_module extends Module {
	public $version = '1.0';
	public $name = 'plugins';

	function __construct () {
		// set the active module
		$this->active_module = $this->name;
		
		parent::__construct();
		
		// load plugin class, it may be extended by plugins
		// this kills the app
		//$this->CI->load->library('plugins/plugin');
	}
	
	/*
	* Pre-admin function
	*
	* Initiate navigation in control panel
	*/
	function admin_preload ()
	{
		//$this->CI->navigation->child_link('configuration',50,'Plugins',site_url('admincp/plugins'));
	}
	
	/*
	* Module update
	*
	* @param int $db_version The current DB version
	*
	* @return int The current software version, to update the database
	*/
	function update ($db_version) {
		if ($db_version < '1.0') {
			// initial install
			$this->CI->db->query('CREATE TABLE `plugins` (
 								 `plugin_id` int(11) NOT NULL auto_increment,
 								 `plugin_name` varchar(255) NOT NULL,
 								 `plugin_description` varchar(255) NOT NULL,
								 `plugin_version` FLOAT NOT NULL,
 								 `plugin_settings` TEXT NOT NULL,
 								 `plugin_active` TINYINT(1),
 								 PRIMARY KEY  (`plugin_id`)
								) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;');
		}
								
		// return current version
		return $this->version;
	}
}