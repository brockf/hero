<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
* RSS Module Definition
*
* Declares the module, update code, etc.
*
* @author Electric Function, Inc.
* @copyright Electric Function, Inc.
* @package Hero Framework
*
*/

class Rss extends Module {
	var $version = '1.04';
	var $name = 'rss';

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
		$this->CI->admin_navigation->child_link('publish',40,'RSS Feeds',site_url('admincp/rss'));
	}
	
	function update ($db_version) {
		if ($db_version < 1.04) {
			$this->CI->settings_model->new_setting(4, 'feed_items_count', '25', 'How many items would you to show in an RSS feed?', 'text');
		}
	
		if ($db_version < 1.03) {
			$this->CI->db->query('CREATE TABLE `rss_feeds` (
 								 `rss_id` int(11) NOT NULL auto_increment,
 								 `link_id` int(11) NOT NULL,
 								 `content_type_id` int(11) NOT NULL,
								 `rss_title` varchar(255) NOT NULL,
								 `rss_description` text NOT NULL,
 								 `rss_filter_author` varchar(250) NOT NULL,
 								 `rss_filter_topic` varchar(250) NOT NULL,
 								 `rss_summary_field` VARCHAR(255) NOT NULL,
 								 `rss_sort_field` varchar(100) NOT NULL,
 								 `rss_sort_dir` varchar(5) NOT NULL,
 								 `rss_template` varchar(150) NOT NULL,
   								 PRIMARY KEY  (`rss_id`)
								 ) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;');
		}
		
		return $this->version;
	}
}