<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/*
* Twitter Control Panel
*
* Displays all control panel forms, datasets, and other displays
*
* @author Electric Function, Inc.
* @copyright Electric Function, Inc.
* @package Hero Framework
*
*/

class Admincp extends Admincp_Controller {
	function __construct()
	{
		parent::__construct();
		
		$this->admin_navigation->parent_active('configuration');
	}
	
	function index () {
		$this->load->library('admin_form');
		$form = new Admin_form;
		
		$form->fieldset('Settings');
		$form->value_row('&nbsp;','<div style="float:left; width: 600px">Enter your Google Analytics site id (e.g., UA-1234567-1) to automatically
		begin sending site analytics information to your Google Analytics account.</div>');
								   
		$form->text('Site ID','analytics_id', $this->config->item('googleanalytics_id'));
	
		$data = array(
					'form_title' => 'Google Analytics',
					'form_action' => site_url('admincp/googleanalytics/post_configure'),
					'form' => $form->display(),
					'form_button' => 'Save Configuration'
				);
	
		$this->load->view('generic', $data);
	}
	
	function post_configure () {
		$this->settings_model->update_setting('googleanalytics_id', $this->input->post('analytics_id'));
		
		$this->notices->SetNotice('Google Analytics configuration saved successfully.');
			
		return redirect('admincp/googleanalytics');
	}
}