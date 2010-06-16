<?php

/**
* Install Redirect
*
* If the system is not installed, the user is redirect to the install controller.
*
* @return boolean TRUE if installed, FALSE if not installed
*
*/

function install_redirect () {
	$not_installed = FALSE;
	
	if (!file_exists(APPPATH . 'config/database.php')) {
		$not_installed = TRUE;
	}
	
	if ($not_installed == TRUE) {
		$CI =& get_instance();
		
		if ($CI->router->fetch_class() != 'install') {
			show_error($CI->config->item('app_name') . ' has not been installed.  Visit yourdomain.com/install to install the server.<br /><br />Need help with the installation?  Check out the <a href="' . $CI->config->item('app_support') . '">support website</a>.');
			die();
		}
	}
	
	return TRUE;
}