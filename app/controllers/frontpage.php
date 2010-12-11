<?php

/**
* Frontpage Controller 
*
* Displays the site homepage
*
* @version 1.0
* @author Electric Function, Inc.
* @package Electric Framework
*/

class Frontpage extends Front_Controller {
	function __construct () {
		parent::__construct();
	}
	
	function index () {
		$this->smarty->display(setting('frontpage_template'));
	}
}


