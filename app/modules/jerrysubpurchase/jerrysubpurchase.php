<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
* Jerrypresale Module Definition
*
* Declares the module, update code, etc.
*
* @author Electric Function, Inc.
* @copyright Electric Function, Inc.
* @package Electric Publisher
*
*/

class Jerrysubpurchase_module extends Module {
	var $version = '1.01';
	var $name = 'jerrysubpurchase';

	function __construct () {
		// set the active module
		$this->active_module = $this->name;
		
		parent::__construct();
	}
	
	/*
	* Module update
	*
	* @param int $db_version The current DB version
	*
	* @return int The current software version, to update the database
	*/
	function update ($db_version) {
								
		// return current version
		return $this->version;
	}
}