<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
* RSS Module Definition
*
* Declares the module, update code, etc.
*
* @author Electric Function, Inc.
* @copyright Electric Function, Inc.
* @package Electric Publisher
*
*/

class Rss extends Module {
	var $version = '1.02';
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
		$CI =& get_instance();
		
		$CI->navigation->child_link('publish',40,'RSS Feeds',site_url('admincp/rss'));
	}
	
	function update ($db_version) {
		if ($db_version < 1.02) {
			$this->CI->db->query('DROP TABLE IF EXISTS `rss_feeds`');
			
			$this->CI->db->query('CREATE TABLE `rss_feeds` (
 								 `rss_id` int(11) NOT NULL auto_increment,
 								 `link_id` int(11) NOT NULL,
 								 `content_type_id` int(11) NOT NULL,
								 `rss_title` varchar(255) NOT NULL,
								 `rss_description` text NOT NULL,
 								 `rss_filter_author` varchar(250) NOT NULL,
 								 `rss_filter_topic` varchar(250) NOT NULL,
 								 `rss_summary_field` VARCHAR(255) NOT NULL,
   								 PRIMARY KEY  (`rss_id`)
								 ) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;');
		}
		
		return $this->version;
	}
}