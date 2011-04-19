<?php
/**
* Admincp Login Controller 
*
* Log in and out
*
* @author Electric Function, Inc.
* @package Electric Framework

*/

class Login extends CI_Controller {
	function __construct() {
		parent::__construct();
		
		$this->load->model('admincp/notices');
		$this->load->helper('admincp/get_notices');
	}
	
	function index() {
		$this->load->view(branded_view('cp/login.php'));
	}
	
	function go() {
		if ($this->user_model->login($this->input->post('username'),$this->input->post('password'))) {
			$this->notices->SetNotice("You have logged in successfully.");
			redirect('/admincp');
			return TRUE;
		}
		else {
			$this->notices->SetError('Your login credentials were incorrect.');
			redirect('/admincp/login');
			return FALSE;
		}
	}
	
	function logout () {
		$this->user_model->logout();
		
		redirect('admincp');
		
		return TRUE;
	}
}