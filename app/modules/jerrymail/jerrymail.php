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
	var $version = '1.02';
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
		
		if ($db_version < '1.01') {
			$this->CI->load->model('link_model');
			$this->CI->link_model->new_link('newsletter', FALSE, 'Subscribe to the Newsletter', 'Newsletter', 'jerrymail', 'newsletter', 'index');
		}
		
		if ($db_version < '1.02') {
			$this->CI->load->model('link_model');
			$this->CI->link_model->new_link('newsletter_thanks', FALSE, 'Newsletter Thanks', 'Newsletter', 'jerrymail', 'newsletter', 'thanks');
		}
										
		// return current version
		return $this->version;
	}
}