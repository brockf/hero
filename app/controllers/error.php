<?php

/**
* Error Controller 
*
* Displays a generic error to the user, passed via the URL.
* This controller was initially created so that the IonCube callback file has an internal
* URL to pass users to.
*
* @version 1.0
* @author Electric Function, Inc.
* @package Electric Framework
*/

class Error extends Controller {
	function __construct () {
		parent::__construct();
	}
	
	function index () {
		die(show_error(urldecode($this->input->get('msg'))));
	}
}
