<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
* Events Module Definition
*
* Declares the module, update code, etc.
*
* @author Electric Function, Inc.
* @copyright Electric Function, Inc.
* @package Electric Publisher
*
*/

class Events_module extends Module {
	var $version = '1.01';
	var $name = 'events';

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
		$this->CI->navigation->child_link('publish',43,'Events',site_url('admincp/events'));
	}

	/*
	* Pre-front Method
	*
	* Triggered prior to loading the frontend
	*/
	function front_preload () {
		$this->CI->smarty->addPluginsDir(APPPATH . 'modules/events/template_plugins/');
	}
		
	function update ($db_version) {
		if ($db_version < 1.0) {
			$this->CI->db->query('CREATE TABLE `events` (
									`event_id` int(11) NOT NULL auto_increment,
									`link_id` int(11) NOT NULL,
									`event_title` varchar(255) NOT NULL,
									`event_url_path` varchar(250) NOT NULL,
									`event_description` text NOT NULL,
									`event_filter_author` varchar(250) default NULL,
									`event_location` varchar(100) NOT NULL,
									`event_max_attendees` int(11) default NULL,
									`event_price` varchar(15) default NULL,
									`event_start_date` datetime default NULL,
									`event_end_date` datetime default NULL,
									`event_privileges` varchar(255) NOT NULL,
									`user_id` int(11) default NULL,
									PRIMARY KEY  (`event_id`)
									) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;');
		}
		
		if ($db_version < 1.01) {
			$this->CI->db->query('ALTER TABLE `events` DROP COLUMN `event_url_path`');
		}
		
		return $this->version;
	}
}