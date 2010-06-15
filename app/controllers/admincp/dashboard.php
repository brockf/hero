<?php
/**
* Admincp Default Controller 
*
* Shows the admin homepage, logs in and out
*
* @version 1.0
* @author Electric Function, Inc.
* @package Electric Publisher

*/

class Dashboard extends Controller {
	function __construct() {
		parent::Controller();
	}
	
	function index() {
		echo 'test';
	}
}