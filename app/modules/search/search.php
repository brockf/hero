<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
* Search Module Definition
*
* Declares the module, update code, etc.
*
* @author Electric Function, Inc.
* @copyright Electric Function, Inc.
* @package Hero Framework
*
*/

class Search_module extends Module {
	var $version = '1.0';
	var $name = 'search';

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
		$this->CI->admin_navigation->child_link('configuration',35,'Search',site_url('admincp/search'));
	}
	
	function update ($db_version) {	
		if ($db_version < 1.0) {
			$this->CI->settings_model->new_setting(6, 'search_content_types', 'a:0:{}', 'Which content types should be included in the search?', 'text', '', FALSE, TRUE);
			$this->CI->settings_model->new_setting(6, 'search_products', '1', 'Should we include store products in the search?', 'toggle', 'a:2:{i:0;s:2:"No";i:1;s:3:"Yes";}', FALSE, TRUE);
			$this->CI->settings_model->new_setting(6, 'search_delay', '5', 'How many seconds should we force the user to wait after a search before they search again?', 'text', '', FALSE, TRUE);
			$this->CI->settings_model->new_setting(6, 'search_trim', '300', 'If displaying a summary for a search result, trim to this many characters.', 'text', '', FALSE, TRUE);
		}
		
		return $this->version;
	}
}