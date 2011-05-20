<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Import extends Module {
	var $version	= "1.0";
	var $name		= 'Member Import';
	
	function __construct() {
		$this->active_module = $this->name;
		
		parent::__construct();
	} 
	
	//--------------------------------------------------------------------
	
	function admin_preload () {
	    $this->CI->admin_navigation->child_link('members', 44, 'Import Members', site_url('admincp/import'));
	}
	
	//--------------------------------------------------------------------
}