<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/*
* Error Module
*
* Displays an error
*
* @author Jose' Vargas
* @copyright Jose' Vargas
* @package Hero Framework
*
*/

class Error extends Front_Controller {
	function __construct() {
		parent::__construct();
	}
	
	function view () {
		$this->output->set_status_header('404');
		$content = array(
			'title' => '404 Page Not Found'
			,'message' => 'The page you requested was not found.'
		);
		
		// show content
		$this->smarty->assign($content);
		
		return $this->smarty->display('error.thtml');
	}
}