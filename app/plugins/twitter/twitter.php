<?php

class Twitter_plugin extends Plugin {
	$this->version = '1.0';
	$this->name = 'Twitter';
	$this->description = 'Tweets out your latest content posts to Twitter.';
	$this->settings = array(
							
						);

	function __construct() {
		parent::_construct();
		
		// now, we have the CodeIgniter superobject available at $this->CI.
	}
	
	function update ($db_version) {
		if ($db_version < '1.0') {
			// initial install
			// there's a nice method in the Plugin class which creates a simple install for us
			// based on the version, name, and description variables above
			$this->install_me();
			
			// this is also where we would bind some hooks
			// with $this->CI->app_hooks->bind();
		}
		
		// return the current version so we can save the version in the database and stop
		// perpetually running updates
		return $this->version;
	}
	
	function uninstall () {
		$this->uninstall_me();
		
		// if we created any bindings in the app_hooks library,
		// we should remove them here with $this->CI->app_hooks->unbind();
	}
}