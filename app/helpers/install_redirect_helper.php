<?php

/**
* Install Redirect
*
* If the system is not installed, the user is redirect to the install controller.
*
* @return boolean TRUE if installed, FALSE if not installed
*
* @copyright Electric Function, Inc.
* @package Hero Framework
* @author Electric Function, Inc.
*/
function install_redirect () {
	$not_installed = FALSE;
	
	if (!file_exists(APPPATH . 'config/database.php')) {
		$not_installed = TRUE;
	}
	
	if ($not_installed == TRUE) {
		$CI =& get_instance();
		
		if ($CI->router->fetch_class() != 'install') {
			$CI->load->helper('url');
		
			show_error($CI->config->item('app_name') . ' has not been installed.  Visit <a href="' . site_url('install') . '">the Installation Wizard</a> to install ' . $CI->config->item('app_name') . ' on this server.<br /><br />Need help with the installation?  Visit the <a href="' . $CI->config->item('app_support') . '">support website</a>.');
			die();
		}
	}
	
	return TRUE;
}