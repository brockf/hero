<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
* Forms Module Definition
*
* Declares the module, update code, etc.
*
* @author Electric Function, Inc.
* @copyright Electric Function, Inc.
* @package Hero Framework
*
*/

class Forms_module extends Module {
	public $version = '1.0';
	public $name = 'forms';

	function __construct () {
		// set the active module
		$this->active_module = $this->name;
		
		parent::__construct();
	}
	
	/**
	* Pre-admin function
	*
	* Initiate navigation in control panel
	*/
	function admin_preload ()
	{
		$this->CI->admin_navigation->child_link('publish',45,'Forms',site_url('admincp/forms'));
	}
	
	/**
	* Pre-front Method
	*
	* Triggered prior to loading the frontend
	*/
	function front_preload () {
		$this->CI->smarty->addPluginsDir(APPPATH . 'modules/forms/template_plugins/');
	}
	
	/**
	* Module update
	*
	* @param int $db_version The current DB version
	*
	* @return int The current software version, to update the database
	*/
	function update ($db_version) {
		if ($db_version < '1.0') {
			// initial install
			$this->CI->db->query('CREATE TABLE `forms` (
 								 `form_id` int(11) NOT NULL auto_increment,
 								 `link_id` int(11) NOT NULL,
 								 `form_table_name` varchar(150) NOT NULL,
								 `custom_field_group_id` int(11) NOT NULL,
 								 `form_title` varchar(250) NOT NULL,
 								 `form_text` TEXT NOT NULL,
							     `form_email` varchar(250) NOT NULL,
							     `form_button_text` varchar(250) NOT NULL,
							     `form_redirect` varchar(250) NOT NULL,
							     `form_privileges` varchar(250) NOT NULL,
							     `form_template` varchar(100) NOT NULL,
 								 PRIMARY KEY  (`form_id`)
								) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;');
		}
								
		// return current version
		return $this->version;
	}
}