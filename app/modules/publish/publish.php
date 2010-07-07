<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
* Publish Module Definition
*
* Declares the module, update code, etc.
*
* @author Electric Function, Inc.
* @copyright Electric Function, Inc.
* @package Electric Publisher
*
*/

class Publish extends Module {
	var $version = '1.02';
	var $name = 'publish';

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
		
		$CI->navigation->child_link('publish',10,'Create New Content',site_url('admincp/publish/new'));
		$CI->navigation->child_link('publish',20,'Manage Content',site_url('admincp/publish'));
		$CI->navigation->child_link('publish',30,'Blogs/Listings',site_url('admincp/publish/blogs'));
		$CI->navigation->child_link('publish',40,'Topics',site_url('admincp/publish/topics'));
		$CI->navigation->child_link('publish',50,'Content Types',site_url('admincp/publish/types'));
	}
	
	/*
	* Module update
	*
	* @param int $db_version The current DB version
	*
	* @return int The current software version, to update the database
	*/
	function update ($db_version) {
		if ($db_version < 1.02) {	
			$this->CI->db->query('DROP TABLE IF EXISTS `content_types`');
										 
			$this->CI->db->query('CREATE TABLE `content_types` (
 								 `content_type_id` int(11) NOT NULL auto_increment,
 								 `content_type_is_standard` tinyint(1) NOT NULL,
 								 `content_type_is_privileged` tinyint(1) NOT NULL,
 								 `custom_field_group_id` int(11) NOT NULL,
 								 `content_type_friendly_name` varchar(100) NOT NULL,
 								 `content_type_system_name` varchar(50) NOT NULL,
 								 PRIMARY KEY  (`content_type_id`)
								) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;');
		}
		
		if ($db_version < 1.0) {								 
			$this->CI->settings_model->make_writeable_folder(setting('path_editor_uploads'));
		}
	
		return $this->version;
	}
}