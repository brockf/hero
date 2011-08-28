<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
* Blogs Module Definition
*
* Declares the module, update code, etc.
*
* @author Electric Function, Inc.
* @copyright Electric Function, Inc.
* @package Hero Framework
*
*/

class Blogs extends Module {
	var $version = '1.02';
	var $name = 'blogs';

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
		$this->CI->admin_navigation->child_link('publish',30,'Blogs/Content Listings',site_url('admincp/blogs'));
	}
	
	function update ($db_version) {
		if ($db_version < 1.0) {
			$this->CI->settings_model->new_setting(4, 'blog_summary_length', '800', 'How many characters would you like to trim each blog post summary to?  Note: words will not be split in two - we use an intelligent content shortening algorithm.', 'text', '');
		}
		
		if ($db_version < 1.02) {
			$this->CI->db->query('DROP TABLE IF EXISTS `blogs`');
		
			$this->CI->db->query('CREATE TABLE `blogs` (
 								 `blog_id` int(11) NOT NULL auto_increment,
 								 `link_id` int(11) NOT NULL,
 								 `content_type_id` int(11) NOT NULL,
								 `blog_title` varchar(255) NOT NULL,
								 `blog_description` text NOT NULL,
 								 `blog_filter_author` varchar(250) NOT NULL,
 								 `blog_filter_topic` varchar(250) NOT NULL,
 								 `blog_summary_field` VARCHAR(255) NOT NULL,
 								 `blog_sort_field` varchar(100) NOT NULL,
 								 `blog_sort_dir` varchar(5) NOT NULL,
 								 `blog_auto_trim` tinyint(1) NOT NULL,
 								 `blog_privileges` varchar(255) NOT NULL,
 								 `blog_template` varchar(255) NOT NULL,
 								 `blog_per_page` int(11) NOT NULL,
   								 PRIMARY KEY  (`blog_id`)
								 ) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;');
		}
		
		return $this->version;
	}
}