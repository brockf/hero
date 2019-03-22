<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
* Error Controller 
*
* Displays a generic error to the user, passed via the URL.
* This controller was initially created so that the IonCube callback file has an internal
* URL to pass users to.
*
* @copyright Electric Function, Inc.
* @author Electric Function, Inc.
* @package Hero Framework
*/

class Error extends CI_Controller {
	function __construct () {
		parent::__construct();
	}
	
	function index () {
		die(show_error(urldecode($this->input->get('msg'))));
	}
}
