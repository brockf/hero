<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/*
* User Activity Module
*
* Register a user activity record for the logged in user
*
* @author Electric Function, Inc.
* @copyright Electric Function, Inc.
* @package Hero Framework
*
*/

class User_activity extends Front_Controller {
	function __construct() {
		parent::__construct();	
	}
	
	function index () {
		if ($this->user_model->logged_in()) {
			$this->db->insert('user_activity', array('user_id' => $this->user_model->get('id'), 'user_activity_date' => date('Y-m-d H:i:s')));
		}
	}
}