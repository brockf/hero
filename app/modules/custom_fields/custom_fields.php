<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
* Custom Fields Module Definition
*
* Declares the module, update code, etc.
*
* @author Electric Function, Inc.
* @package Electric Publisher
*
*/

class Custom_fields extends Module {
	var $version = '1.0';
	var $name = 'custom_fields';

	function __construct () {
		// set the active module
		$this->active_module = $this->name;
		
		parent::__construct();
	}
}