<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
* Frontpage Controller 
*
* Displays the site homepage by triggering the "frontpage" template.
*
* @copyright Electric Function, Inc.
* @author Electric Function, Inc.
* @package Hero Framework
*/

class Frontpage extends Front_Controller {
	function __construct () {
		parent::__construct();
	}
	
	function index () {
		$this->smarty->display(setting('frontpage_template'));
	}
}


