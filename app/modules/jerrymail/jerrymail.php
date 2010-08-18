<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
* Jerrymail Module Definition
*
* Declares the module, update code, etc.
*
* @author Electric Function, Inc.
* @copyright Electric Function, Inc.
* @package Electric Publisher
*
*/

class Jerrymail_module extends Module {
	var $version = '1.0';
	var $name = 'jerrymail';

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
			$this->CI->db->query('CREATE TABLE `friendshare` (
 								 `friendshare_id` int(11) NOT NULL auto_increment,
 								 `friendshare_email` varchar(250) NOT NULL,
 								 `content_id` int(11) NOT NULL,
 								 `friendshare_date` DATETIME NOT NULL,
 								 PRIMARY KEY  (`friendshare_id`)
								) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;');
		}
								
		// return current version
		return $this->version;
	}
}